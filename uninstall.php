<?php
/**
 * Okito Cookie Consent — Uninstall
 * 
 * Cleans up all plugin options when uninstalled via WordPress admin.
 * 
 * @package OkitoCookieConsent
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all Okito options
$okito_options = array(
    'okito_website_key',
    'okito_enabled',
);

foreach ($okito_options as $okito_option) {
    delete_option($okito_option);
}

// Remove transients
global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- bulk transient cleanup has no API equivalent.
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $wpdb->esc_like('_transient_okito_') . '%',
        $wpdb->esc_like('_transient_timeout_okito_') . '%'
    )
);
