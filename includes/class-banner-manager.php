<?php
/**
 * Okito Banner Manager
 * 
 * Minimal helper — banner management is handled in the Okito web dashboard.
 * This class provides helper methods for the WordPress admin side.
 * 
 * @package OkitoCookieConsent
 */

if (!defined('ABSPATH')) {
    exit;
}

class Okito_Banner_Manager
{
    /**
     * Check if the plugin is properly configured
     */
    public function is_configured()
    {
        $website_key = get_option('okito_website_key', '');
        return !empty($website_key);
    }

    /**
     * Get the CDN script URL
     */
    public function get_script_url()
    {
        $api_client = new Okito_API_Client();
        return $api_client->get_script_url();
    }

    /**
     * Get the Okito dashboard URL for banner management
     */
    public function get_dashboard_url()
    {
        return 'https://app.okito.com/banner-builder';
    }

    /**
     * Get the Okito setup URL
     */
    public function get_setup_url()
    {
        return 'https://app.okito.com/setup';
    }
}
