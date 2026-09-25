<?php
/**
 * Okito API Client
 * 
 * Lightweight client — the plugin primarily uses CDN script injection.
 * This client is used only for admin-side features (connection test, cache management).
 * 
 * @package OkitoCookieConsent
 */

if (!defined('ABSPATH')) {
    exit;
}

class Okito_API_Client
{
    private $cdn_base_url;

    public function __construct()
    {
        $this->cdn_base_url = defined('OKITO_CDN_BASE_URL') ? OKITO_CDN_BASE_URL : 'https://cdn.okito.com';
    }

    /**
     * Get the CDN script URL for a website key
     */
    public function get_script_url($website_key = null)
    {
        if (empty($website_key)) {
            $website_key = get_option('okito_website_key');
        }

        if (empty($website_key)) {
            return new WP_Error('no_website_key', __('No Website Key configured', 'okito-cookie-consent'));
        }

        return trailingslashit($this->cdn_base_url) . 'js/' . $website_key;
    }

    /**
     * Test if a website key is valid by pinging the CDN endpoint
     */
    public function test_website_key($website_key)
    {
        $url = $this->get_script_url($website_key);

        if (is_wp_error($url)) {
            return $url;
        }

        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'Okito WordPress Plugin/' . OKITO_VERSION,
            ),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        return $code === 200;
    }

    /**
     * Clear all plugin transients
     */
    public function clear_cache()
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- bulk transient cleanup has no API equivalent.
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                $wpdb->esc_like('_transient_okito_') . '%',
                $wpdb->esc_like('_transient_timeout_okito_') . '%'
            )
        );
    }
}
