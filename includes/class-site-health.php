<?php
/**
 * Okito Site Health — consent checks under Tools → Site Health.
 *
 * @package OkitoCookieConsent
 */

if (!defined('ABSPATH')) {
    exit;
}

class Okito_Site_Health
{
    /** Other consent / cookie banner plugins (directory => name). */
    const OTHER_CMPS = array(
        'complianz-gdpr'                        => 'Complianz',
        'complianz-gdpr-premium'                => 'Complianz Premium',
        'cookie-law-info'                       => 'CookieYes',
        'cookiebot'                             => 'Cookiebot',
        'gdpr-cookie-compliance'                => 'GDPR Cookie Compliance',
        'cookie-notice'                         => 'Cookie Notice & Compliance',
        'real-cookie-banner'                    => 'Real Cookie Banner',
        'real-cookie-banner-pro'                => 'Real Cookie Banner Pro',
        'iubenda-cookie-law-solution'           => 'iubenda',
        'borlabs-cookie'                        => 'Borlabs Cookie',
        'uk-cookie-consent'                     => 'Termly',
        'gdpr-cookie-consent'                   => 'WP Cookie Consent',
        'beautiful-and-responsive-cookie-consent' => 'Beautiful Cookie Banner',
        'ninja-gdpr-compliance'                 => 'Ninja GDPR',
        'cookie-script-com'                     => 'Cookie-Script',
    );

    public function __construct()
    {
        add_filter('site_status_tests', array($this, 'register_tests'));
    }

    /**
     * @param array $tests Site Health tests.
     * @return array
     */
    public function register_tests($tests)
    {
        $tests['direct']['okito_setup'] = array(
            'label' => __('Okito cookie banner', 'okito-cookie-consent'),
            'test'  => array($this, 'test_setup'),
        );
        $tests['direct']['okito_other_cmp'] = array(
            'label' => __('Only one cookie consent plugin', 'okito-cookie-consent'),
            'test'  => array($this, 'test_other_cmp'),
        );
        $tests['direct']['okito_wp_consent_api'] = array(
            'label' => __('WP Consent API', 'okito-cookie-consent'),
            'test'  => array($this, 'test_wp_consent_api'),
        );
        if (defined('GOOGLESITEKIT_VERSION')) {
            $tests['direct']['okito_site_kit'] = array(
                'label' => __('Site Kit Consent Mode', 'okito-cookie-consent'),
                'test'  => array($this, 'test_site_kit'),
            );
        }
        if (self::is_active()) {
            $tests['direct']['okito_tag_order'] = array(
                'label' => __('Consent defaults load before Google tags', 'okito-cookie-consent'),
                'test'  => array($this, 'test_tag_order'),
            );
        }
        return $tests;
    }

    private static function is_active()
    {
        return (bool) get_option('okito_enabled', false) && '' !== (string) get_option('okito_website_key', '');
    }

    /**
     * @param string $test        Test id.
     * @param string $status      good|recommended|critical.
     * @param string $label       Result headline.
     * @param string $description Result details (plain text).
     * @param string $actions     Optional action HTML.
     * @return array
     */
    private function result($test, $status, $label, $description, $actions = '')
    {
        return array(
            'label'       => $label,
            'status'      => $status,
            'badge'       => array(
                'label' => __('Privacy', 'okito-cookie-consent'),
                'color' => 'good' === $status ? 'blue' : ('critical' === $status ? 'red' : 'orange'),
            ),
            'description' => '<p>' . esc_html($description) . '</p>',
            'actions'     => $actions,
            'test'        => $test,
        );
    }

    private function settings_action()
    {
        return sprintf(
            '<p><a href="%s">%s</a></p>',
            esc_url(admin_url('admin.php?page=okito-settings')),
            esc_html__('Open Okito settings', 'okito-cookie-consent')
        );
    }

    public function test_setup()
    {
        if (self::is_active()) {
            return $this->result(
                'okito_setup',
                'good',
                __('The Okito cookie banner is enabled', 'okito-cookie-consent'),
                __('Visitors see the Okito consent banner and Google Consent Mode signals are sent.', 'okito-cookie-consent')
            );
        }
        return $this->result(
            'okito_setup',
            'recommended',
            __('The Okito cookie banner is not enabled', 'okito-cookie-consent'),
            __('Connect this site to Okito to show the consent banner and send Google Consent Mode signals.', 'okito-cookie-consent'),
            $this->settings_action()
        );
    }

    public function test_other_cmp()
    {
        $active = array();
        foreach ((array) get_option('active_plugins', array()) as $plugin) {
            $dir = strtok((string) $plugin, '/');
            if (isset(self::OTHER_CMPS[$dir])) {
                $active[] = self::OTHER_CMPS[$dir];
            }
        }
        if (empty($active)) {
            return $this->result(
                'okito_other_cmp',
                'good',
                __('No other cookie consent plugin is active', 'okito-cookie-consent'),
                __('Only Okito manages consent on this site.', 'okito-cookie-consent')
            );
        }
        return $this->result(
            'okito_other_cmp',
            'critical',
            __('Another cookie consent plugin is active', 'okito-cookie-consent'),
            sprintf(
                /* translators: %s: plugin names */
                __('%s is active next to Okito. Two consent tools show two banners and send conflicting consent signals to Google. Deactivate the other plugin.', 'okito-cookie-consent'),
                implode(', ', $active)
            ),
            sprintf('<p><a href="%s">%s</a></p>', esc_url(admin_url('plugins.php')), esc_html__('Manage plugins', 'okito-cookie-consent'))
        );
    }

    public function test_wp_consent_api()
    {
        if (function_exists('wp_has_consent')) {
            return $this->result(
                'okito_wp_consent_api',
                'good',
                __('The WP Consent API is active', 'okito-cookie-consent'),
                __('Okito passes each visitor\'s choice to the WP Consent API, so Site Kit and other compatible plugins follow it.', 'okito-cookie-consent')
            );
        }
        return $this->result(
            'okito_wp_consent_api',
            'recommended',
            __('Install the WP Consent API', 'okito-cookie-consent'),
            __('With the WP Consent API plugin, Site Kit, WooCommerce and other plugins follow the choice visitors make in the Okito banner.', 'okito-cookie-consent'),
            sprintf(
                '<p><a href="%s">%s</a></p>',
                esc_url(admin_url('plugin-install.php?s=wp-consent-api&tab=search&type=term')),
                esc_html__('Install the WP Consent API', 'okito-cookie-consent')
            )
        );
    }

    public function test_site_kit()
    {
        $settings = get_option('googlesitekit_consent_mode', array());
        if (is_array($settings) && !empty($settings['enabled'])) {
            return $this->result(
                'okito_site_kit',
                'good',
                __('Consent Mode is enabled in Site Kit', 'okito-cookie-consent'),
                __('Site Kit\'s Google tag follows the choices visitors make in the Okito banner.', 'okito-cookie-consent')
            );
        }
        return $this->result(
            'okito_site_kit',
            'recommended',
            __('Enable Consent Mode in Site Kit', 'okito-cookie-consent'),
            __('Turn on Consent Mode under Site Kit → Settings → Admin Settings so Site Kit\'s Google tag reads the visitor\'s choice through the WP Consent API.', 'okito-cookie-consent'),
            sprintf(
                '<p><a href="%s">%s</a></p>',
                esc_url(admin_url('admin.php?page=googlesitekit-settings#/admin-settings')),
                esc_html__('Open Site Kit settings', 'okito-cookie-consent')
            )
        );
    }

    public function test_tag_order()
    {
        $response = wp_remote_get(home_url('/'), array('timeout' => 10, 'sslverify' => false));
        if (is_wp_error($response) || 200 !== (int) wp_remote_retrieve_response_code($response)) {
            return $this->result(
                'okito_tag_order',
                'good',
                __('Tag order could not be checked', 'okito-cookie-consent'),
                __('The home page could not be loaded from the server. Check the order in your browser: Okito\'s consent defaults must come before Google tags.', 'okito-cookie-consent')
            );
        }
        $html = (string) wp_remote_retrieve_body($response);
        $okito = strpos($html, 'developer_id.dZGJiMm');
        if (false === $okito) {
            return $this->result(
                'okito_tag_order',
                'critical',
                __('Okito\'s consent defaults are missing from the home page', 'okito-cookie-consent'),
                __('The banner is enabled but its Google Consent Mode defaults are not in the page. Clear your page cache; if it persists, your theme may not call wp_head().', 'okito-cookie-consent'),
                $this->settings_action()
            );
        }
        $google = false;
        foreach (array('googletagmanager.com/gtm.js', 'googletagmanager.com/gtag/js', "gtag('config'", 'gtag("config"') as $needle) {
            $pos = strpos($html, $needle);
            if (false !== $pos && (false === $google || $pos < $google)) {
                $google = $pos;
            }
        }
        if (false !== $google && $google < $okito) {
            return $this->result(
                'okito_tag_order',
                'critical',
                __('A Google tag loads before Okito\'s consent defaults', 'okito-cookie-consent'),
                __('Google Tag Manager or gtag.js appears in the page before Okito, so it can send data before consent is known. Move the Google tag below Okito, or load it through Site Kit or Google Tag Manager with the Okito template.', 'okito-cookie-consent')
            );
        }
        return $this->result(
            'okito_tag_order',
            'good',
            __('Consent defaults load before Google tags', 'okito-cookie-consent'),
            __('Okito sets the Google Consent Mode defaults before any Google tag on the home page.', 'okito-cookie-consent')
        );
    }
}
