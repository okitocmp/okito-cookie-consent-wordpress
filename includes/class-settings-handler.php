<?php
/**
 * Okito Settings Handler
 * 
 * Manages plugin settings. The plugin is intentionally simple:
 * only a Website Key is needed — everything else is configured in the Okito dashboard.
 * 
 * @package OkitoCookieConsent
 */

if (!defined('ABSPATH')) {
    exit;
}

class Okito_Settings_Handler
{
    public function __construct()
    {
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings()
    {
        register_setting('okito_settings', 'okito_website_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));

        register_setting('okito_settings', 'okito_enabled', array(
            'type' => 'boolean',
            'sanitize_callback' => array($this, 'sanitize_checkbox'),
            'default' => false,
        ));
    }

    public function sanitize_checkbox($input)
    {
        return !empty($input) ? 1 : 0;
    }

    public function get_all_settings()
    {
        return array(
            'website_key' => get_option('okito_website_key', ''),
            'enabled' => (bool) get_option('okito_enabled', false),
        );
    }

    public function reset_settings()
    {
        delete_option('okito_website_key');
        delete_option('okito_enabled');
        return true;
    }
}
