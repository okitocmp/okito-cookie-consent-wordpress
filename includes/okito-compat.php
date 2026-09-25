<?php
/**
 * Runtime compatibility helpers (WordPress 6.4+ and PHP 7.4–8.x).
 *
 * @package OkitoCookieConsent
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compare the running WordPress version against a minimum.
 *
 * @param string $minimum_version Minimum version, e.g. "6.3".
 * @return bool
 */
function okito_wp_version_at_least($minimum_version)
{
    global $wp_version;

    if (!is_string($wp_version) || '' === $wp_version) {
        return false;
    }

    return version_compare($wp_version, $minimum_version, '>=');
}

/**
 * Whether wp_register_script() accepts the args array (in_footer, strategy).
 *
 * @return bool
 */
function okito_wp_supports_script_strategy()
{
    return okito_wp_version_at_least('6.3');
}

/**
 * Normalize an HTTP response header to a single lowercase string.
 *
 * @param mixed $header Header value from wp_remote_retrieve_header().
 * @return string
 */
function okito_normalize_response_header($header)
{
    if (is_array($header)) {
        $header = implode(', ', $header);
    }

    if (!is_string($header)) {
        return '';
    }

    return strtolower($header);
}

/**
 * Case-insensitive substring check (PHP 7.4+ safe).
 *
 * @param string $haystack Haystack.
 * @param string $needle   Needle.
 * @return bool
 */
function okito_string_contains($haystack, $needle)
{
    if ('' === $needle) {
        return true;
    }

    return false !== strpos($haystack, $needle);
}

/**
 * Whether a CDN health-check response looks like JavaScript.
 *
 * @param int    $status_code   HTTP status.
 * @param string $content_type  Normalized Content-Type header.
 * @return bool
 */
function okito_cdn_response_looks_like_script($status_code, $content_type)
{
    if (200 !== (int) $status_code) {
        return false;
    }

    if ('' === $content_type) {
        // Some CDNs omit Content-Type; 200 on /js/{key} is treated as success.
        return true;
    }

    return okito_string_contains($content_type, 'javascript')
        || okito_string_contains($content_type, 'ecmascript')
        || okito_string_contains($content_type, 'text/js')
        || okito_string_contains($content_type, 'application/x-javascript');
}
