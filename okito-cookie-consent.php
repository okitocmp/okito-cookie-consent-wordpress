<?php
/**
 * Plugin Name: Okito Cookie Consent
 * Plugin URI: https://app.okito.com
 * Description: Professional cookie consent management and GDPR/CCPA/LGPD compliance. Integrates with the Okito SaaS platform for enterprise-grade cookie banner management.
 * Version: 1.1.2
 * Author: Okito
 * Author URI: https://okito.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: okito-cookie-consent
 * Domain Path: /languages
 * Requires at least: 6.4
 * Tested up to: 7.1
 * Requires PHP: 7.4
 * 
 * @package OkitoCookieConsent
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('OKITO_VERSION', '1.1.2');
define('OKITO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OKITO_PLUGIN_PATH', plugin_dir_path(__FILE__));
// Overridable from wp-config.php (e.g. staging).
if (!defined('OKITO_API_BASE_URL')) {
    define('OKITO_API_BASE_URL', 'https://app.okito.com/api');
}
if (!defined('OKITO_CDN_BASE_URL')) {
    define('OKITO_CDN_BASE_URL', 'https://cdn.okito.com');
}
if (!defined('OKITO_APP_URL')) {
    define('OKITO_APP_URL', 'https://app.okito.com');
}
define('OKITO_MIN_WP_VERSION', '6.4');
define('OKITO_MIN_PHP_VERSION', '7.4');

require_once OKITO_PLUGIN_PATH . 'includes/okito-compat.php';

// Declare compliance with the WP Consent API
// (https://wordpress.org/plugins/wp-consent-api/).
add_filter('wp_consent_api_registered_' . plugin_basename(__FILE__), '__return_true');

/**
 * Main Okito Cookie Consent Plugin Class
 */
class OkitoCookieConsent
{
    /** @var OkitoCookieConsent|null */
    private static $instance = null;

    /** @var Okito_API_Client */
    private $api_client;

    /** @var Okito_Public */
    private $public;

    /**
     * Get plugin instance (singleton)
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init();
    }

    private function init()
    {
        add_action('init', array($this, 'initialize_plugin'));
        add_action('admin_init', array($this, 'register_privacy_suggested_content'));
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
            add_action('admin_init', array($this, 'handle_connect_return'));
        }

        add_action('admin_notices', array(__CLASS__, 'maybe_show_environment_notice'));

        // "Connect with Okito" start (admin-post.php?action=okito_connect).
        add_action('admin_post_okito_connect', array($this, 'start_connect'));

        // AJAX hooks
        add_action('wp_ajax_okito_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_okito_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_okito_register_website', array($this, 'ajax_register_website'));
        add_action('wp_ajax_okito_clear_cache', array($this, 'ajax_clear_cache'));

        // Activation / deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Suggested text for the Privacy Policy page (Tools → Privacy since WP 4.9.6).
     */
    public function register_privacy_suggested_content()
    {
        if (!function_exists('wp_add_privacy_policy_content')) {
            return;
        }

        $content = sprintf(
            /* translators: 1: opening link tag to app.okito.com, 2: closing link tag */
            __(
                'This plugin stores your Okito Website Key in the WordPress database. When the banner is enabled, it loads JavaScript from %1$sapp.okito.com%2$s to display and manage cookie consent. Visitor interactions with the banner may be processed by Okito as described on their website.',
                'okito-cookie-consent'
            ),
            '<a href="https://app.okito.com" target="_blank" rel="noopener noreferrer">',
            '</a>'
        );

        wp_add_privacy_policy_content(
            __('Okito Cookie Consent', 'okito-cookie-consent'),
            wp_kses_post($content)
        );
    }

    public function initialize_plugin()
    {
        $this->include_files();
        $this->api_client = new Okito_API_Client();
        new Okito_Compat();
        new Okito_Site_Health();

        if (!is_admin()) {
            $this->public = new Okito_Public();
        }
    }

    private function include_files()
    {
        require_once OKITO_PLUGIN_PATH . 'includes/class-api-client.php';
        require_once OKITO_PLUGIN_PATH . 'includes/class-settings-handler.php';
        require_once OKITO_PLUGIN_PATH . 'includes/class-banner-manager.php';
        require_once OKITO_PLUGIN_PATH . 'includes/class-public.php';
        require_once OKITO_PLUGIN_PATH . 'includes/class-compat.php';
        require_once OKITO_PLUGIN_PATH . 'includes/class-site-health.php';
    }

    // ──────────────────────────────────────────
    // Admin Menu
    // ──────────────────────────────────────────

    public function add_admin_menu()
    {
        add_menu_page(
            __('Okito Cookie Consent', 'okito-cookie-consent'),
            __('Okito', 'okito-cookie-consent'),
            'manage_options',
            'okito-cookie-consent',
            array($this, 'admin_dashboard_page'),
            $this->get_admin_menu_icon_data_uri(),
            30
        );

        add_submenu_page(
            'okito-cookie-consent',
            __('Dashboard', 'okito-cookie-consent'),
            __('Dashboard', 'okito-cookie-consent'),
            'manage_options',
            'okito-cookie-consent',
            array($this, 'admin_dashboard_page')
        );

        add_submenu_page(
            'okito-cookie-consent',
            __('Settings', 'okito-cookie-consent'),
            __('Settings', 'okito-cookie-consent'),
            'manage_options',
            'okito-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Okito logo as a data URI for the admin menu.
     *
     * @return string
     */
    private function get_admin_menu_icon_data_uri()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 99.39 99.39"><path fill="#114b98" d="M44.61.47h0c22.79-1.49,36.04,7.65,44.33,18.18h0l-16.83,12.76h0c-16.49-17.9-34.79-8.96-39.13-5.69-3.93,2.9-7.16,6.82-9.23,11.31-3.49,7.79-3.52,17.03-.23,24.9,3.41,7.71,10.22,13.86,18.28,16,4.35,1.15,8.93,1.26,13.35.52,8.97-1.47,16.76-8.05,20.38-16.47,1.91-4.27,2.53-9.04,2.39-13.7l19.06-14.21c3.47,11.65,3.02,24.51-1.66,35.75-4.46,10.67-12.87,19.55-23.21,24.39-6.32,3.14-13.35,4.72-20.35,4.99-7.69.23-15.54-.88-22.68-3.96-8.98-3.91-16.72-10.66-21.91-19.09C1.75,67.53-.36,57.04.15,46.91c.27-5.77,1.4-11.54,3.59-16.89C8.61,18.31,18.21,8.78,29.74,3.96c4.74-1.93,9.78-3.09,14.87-3.49Z"/><path fill="#81bc44" d="M90.48,20.62h0c2.28,3.32,4.19,6.82,5.69,10.72,0,0-21.11,15.84-31.54,23.95-4.43,3.48-8.94,6.85-13.44,10.24-.2.22-.52.13-.67-.09-4.73-4.85-18.01-18.5-18.01-18.5h0l7.51-8.31,10.96,11.76h0c6.52-4.96,32.99-24.79,39.49-29.76h0Z"/></svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function admin_init()
    {
        register_setting(
            'okito_settings',
            'okito_website_key',
            array(
                'type'              => 'string',
                'sanitize_callback' => array($this, 'sanitize_website_key'),
                'default'           => '',
            )
        );
        register_setting(
            'okito_settings',
            'okito_enabled',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => array($this, 'sanitize_enabled'),
                'default'           => false,
            )
        );
    }

    /**
     * @param mixed $value Raw option value.
     * @return string
     */
    public function sanitize_website_key($value)
    {
        if (null === $value || false === $value) {
            return '';
        }

        return sanitize_text_field((string) $value);
    }

    /**
     * @param mixed $value Raw option value.
     * @return bool
     */
    public function sanitize_enabled($value)
    {
        return (bool) $value;
    }

    public function admin_enqueue_scripts($hook)
    {
        if (strpos($hook, 'okito') === false) {
            return;
        }

        wp_enqueue_style(
            'okito-admin-style',
            OKITO_PLUGIN_URL . 'admin/assets/css/admin.css',
            array(),
            OKITO_VERSION
        );

        wp_enqueue_script(
            'okito-admin-script',
            OKITO_PLUGIN_URL . 'admin/assets/js/admin.js',
            array('jquery'),
            OKITO_VERSION,
            true
        );

        wp_localize_script('okito-admin-script', 'okito_ajax', array(
            'url'             => admin_url('admin-ajax.php'),
            'nonce'           => wp_create_nonce('okito_nonce'),
            'cdn_base'        => OKITO_CDN_BASE_URL,
            'test_connection' => __('Test Connection', 'okito-cookie-consent'),
            'testing'         => __('Testing...', 'okito-cookie-consent'),
            'please_enter_key'=> __('Please enter a Website Key first.', 'okito-cookie-consent'),
            'testing_connection' => __('Testing connection...', 'okito-cookie-consent'),
            'network_error'   => __('Network error. Please try again.', 'okito-cookie-consent'),
            'clear_cache'     => __('Clear Cache', 'okito-cookie-consent'),
            'clearing'        => __('Clearing...', 'okito-cookie-consent'),
        ));
    }

    // ──────────────────────────────────────────
    // Page Renderers
    // ──────────────────────────────────────────

    public function admin_dashboard_page()
    {
        include OKITO_PLUGIN_PATH . 'admin/dashboard.php';
    }

    public function settings_page()
    {
        include OKITO_PLUGIN_PATH . 'admin/settings.php';
    }

    // ──────────────────────────────────────────
    // Connect with Okito
    // ──────────────────────────────────────────

    /**
     * URL of the "Connect with Okito" button (nonce-protected).
     */
    public static function connect_url()
    {
        return wp_nonce_url(admin_url('admin-post.php?action=okito_connect'), 'okito_connect');
    }

    /**
     * Send the admin to app.okito.com, which returns the Website Key to
     * handle_connect_return() together with a one-time state.
     */
    public function start_connect()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Insufficient permissions', 'okito-cookie-consent'));
        }
        check_admin_referer('okito_connect');

        $state = wp_generate_password(40, false, false);
        set_transient('okito_connect_state_' . get_current_user_id(), $state, 30 * MINUTE_IN_SECONDS);

        $url = add_query_arg(
            array(
                'site_url'   => rawurlencode(home_url('/')),
                'return_url' => rawurlencode(admin_url('admin.php?page=okito-settings')),
                'state'      => $state,
            ),
            OKITO_APP_URL . '/integrations/wordpress/connect'
        );

        add_filter('allowed_redirect_hosts', array($this, 'allow_app_host'));
        wp_safe_redirect($url);
        exit;
    }

    /**
     * @param string[] $hosts Allowed redirect hosts.
     * @return string[]
     */
    public function allow_app_host($hosts)
    {
        $host = wp_parse_url(OKITO_APP_URL, PHP_URL_HOST);
        if ($host) {
            $hosts[] = $host;
        }
        return $hosts;
    }

    /**
     * Back from app.okito.com: verify the one-time state, then save and
     * enable the returned Website Key.
     */
    public function handle_connect_return()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- the one-time state below is the CSRF check for this external round trip.
        if (!isset($_GET['page'], $_GET['okito_key'], $_GET['state'])) {
            return;
        }
        if ('okito-settings' !== sanitize_key(wp_unslash($_GET['page']))) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }

        $state = sanitize_text_field(wp_unslash($_GET['state']));
        $key = sanitize_text_field(wp_unslash($_GET['okito_key']));
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        $transient = 'okito_connect_state_' . get_current_user_id();
        $expected = get_transient($transient);
        delete_transient($transient);

        $ok = is_string($expected) && '' !== $expected && hash_equals($expected, $state)
            && 1 === preg_match('/^[A-Za-z0-9_-]{3,128}$/', $key);

        if ($ok) {
            update_option('okito_website_key', $key);
            update_option('okito_enabled', true);
        }

        wp_safe_redirect(admin_url('admin.php?page=okito-settings&okito_connected=' . ($ok ? '1' : '0')));
        exit;
    }

    // ──────────────────────────────────────────
    // AJAX Handlers
    // ──────────────────────────────────────────

    public function ajax_save_settings()
    {
        check_ajax_referer('okito_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'okito-cookie-consent'));
        }

        $website_key = isset($_POST['website_key']) ? sanitize_text_field(wp_unslash($_POST['website_key'])) : '';
        $enabled = !empty($_POST['enabled']);

        update_option('okito_website_key', $website_key);
        update_option('okito_enabled', $enabled);

        wp_send_json_success(__('Settings saved successfully', 'okito-cookie-consent'));
    }

    public function ajax_test_connection()
    {
        check_ajax_referer('okito_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'okito-cookie-consent'));
        }

        $website_key = isset($_POST['website_key'])
            ? sanitize_text_field(wp_unslash($_POST['website_key']))
            : sanitize_text_field((string) get_option('okito_website_key', ''));
        if (empty($website_key)) {
            wp_send_json_error(__('Please enter a Website Key first.', 'okito-cookie-consent'));
        }

        // Test by fetching the CDN script — if it returns JS, the key is valid
        $test_url = OKITO_CDN_BASE_URL . '/js/' . rawurlencode($website_key) . '?healthcheck=1';
        $response = wp_remote_get($test_url, array('timeout' => 10));

        if (is_wp_error($response)) {
            wp_send_json_error(__('Could not connect to Okito servers: ', 'okito-cookie-consent') . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $content_type = okito_normalize_response_header(
            wp_remote_retrieve_header($response, 'content-type')
        );

        if (okito_cdn_response_looks_like_script($code, $content_type)) {
            wp_send_json_success(__('Connection successful! Your Website Key is valid.', 'okito-cookie-consent'));
        } elseif ($code === 404) {
            wp_send_json_error(__('Website Key not found. Please check your key in the Okito dashboard.', 'okito-cookie-consent'));
        } else {
            wp_send_json_error(sprintf(
                /* translators: %d: HTTP status code */
                __('Unexpected response (HTTP %d). Please try again later.', 'okito-cookie-consent'),
                $code
            ));
        }
    }

    public function ajax_register_website()
    {
        check_ajax_referer('okito_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'okito-cookie-consent'));
        }

        // This directs the user to the Okito dashboard to register their website
        wp_send_json_success(array(
            'redirect' => 'https://app.okito.com/setup',
            'message' => __('Please register your website in the Okito dashboard and copy the Website Key.', 'okito-cookie-consent'),
        ));
    }

    public function ajax_clear_cache()
    {
        check_ajax_referer('okito_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'okito-cookie-consent'));
        }

        // Clear transients
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- bulk transient cleanup has no API equivalent.
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                $wpdb->esc_like('_transient_okito_') . '%',
                $wpdb->esc_like('_transient_timeout_okito_') . '%'
            )
        );

        wp_send_json_success(__('Cache cleared successfully!', 'okito-cookie-consent'));
    }

    // ──────────────────────────────────────────
    // Activation / Deactivation
    // ──────────────────────────────────────────

    public function activate()
    {
        add_option('okito_enabled', false);
        add_option('okito_website_key', '');
    }

    public function deactivate()
    {
        // Nothing to clean on deactivation (settings preserved)
    }

    /**
     * Warn administrators when PHP or WordPress is below the supported range.
     */
    public static function maybe_show_environment_notice()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $issues = array();

        if (version_compare(PHP_VERSION, OKITO_MIN_PHP_VERSION, '<')) {
            $issues[] = sprintf(
                /* translators: 1: required PHP version, 2: current PHP version */
                __('Okito Cookie Consent requires PHP %1$s or newer (this server runs PHP %2$s).', 'okito-cookie-consent'),
                OKITO_MIN_PHP_VERSION,
                PHP_VERSION
            );
        }

        if (!okito_wp_version_at_least(OKITO_MIN_WP_VERSION)) {
            global $wp_version;
            $issues[] = sprintf(
                /* translators: 1: required WordPress version, 2: current WordPress version */
                __('Okito Cookie Consent requires WordPress %1$s or newer (this site runs WordPress %2$s).', 'okito-cookie-consent'),
                OKITO_MIN_WP_VERSION,
                is_string($wp_version) ? $wp_version : __('unknown', 'okito-cookie-consent')
            );
        }

        if (empty($issues)) {
            return;
        }

        echo '<div class="notice notice-error"><p><strong>'
            . esc_html__('Okito Cookie Consent', 'okito-cookie-consent')
            . '</strong> — '
            . esc_html(implode(' ', $issues))
            . '</p></div>';
    }
}

// Boot!
OkitoCookieConsent::get_instance();
