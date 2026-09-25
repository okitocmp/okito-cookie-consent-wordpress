<?php
/**
 * Okito Compatibility — keeps caching / optimization plugins away from Okito.
 *
 * The Consent Mode defaults must run before any Google tag and the banner
 * script must load early. Delaying, deferring, combining or minifying them
 * (WP Rocket "Delay JavaScript", LiteSpeed, Autoptimize, SiteGround
 * Optimizer, W3 Total Cache, Cloudflare Rocket Loader, …) breaks consent.
 * Each tool is told to leave them alone through its own exclusion filter;
 * the script tags also carry the attributes these tools honor.
 *
 * @package OkitoCookieConsent
 */

if (!defined('ABSPATH')) {
    exit;
}

class Okito_Compat
{
    /** Substrings that identify Okito's scripts (URL or inline content). */
    const PATTERNS = array('cdn.okito.com', 'okito-cookie-consent', 'okitoGpc', 'developer_id.dZGJiMm');

    /**
     * Consent plumbing of other plugins that must not wait for a user
     * interaction either: the WP Consent API (Okito passes choices through it)
     * and Site Kit's Consent Mode script (reads them).
     */
    const CONSENT_API_PATTERNS = array('wp-consent-api', 'googlesitekit-consent-mode');

    /** Attributes honored by Cloudflare, LiteSpeed, WP Rocket and others. */
    const SCRIPT_ATTRIBUTES = 'data-cfasync="false" data-no-optimize="1" data-no-defer="1" data-no-minify="1" nowprocket';

    public function __construct()
    {
        // WP Rocket.
        add_filter('rocket_delay_js_exclusions', array($this, 'add_delay_patterns'));
        add_filter('rocket_exclude_js', array($this, 'add_patterns'));
        add_filter('rocket_exclude_defer_js', array($this, 'add_patterns'));
        add_filter('rocket_excluded_inline_js_content', array($this, 'add_patterns'));
        add_filter('rocket_minify_excluded_external_js', array($this, 'add_hosts'));

        // LiteSpeed Cache.
        add_filter('litespeed_optimize_js_excludes', array($this, 'add_patterns'));
        add_filter('litespeed_optm_js_defer_exc', array($this, 'add_patterns'));
        add_filter('litespeed_optm_gm_js_exc', array($this, 'add_patterns'));

        // SiteGround Optimizer (handles and inline content).
        add_filter('sgo_js_minify_exclude', array($this, 'add_handles'));
        add_filter('sgo_javascript_combine_exclude', array($this, 'add_handles'));
        add_filter('sgo_js_async_exclude', array($this, 'add_handles'));
        add_filter('sgo_javascript_combine_excluded_inline_content', array($this, 'add_patterns'));
        add_filter('sgo_javascript_combine_excluded_external_paths', array($this, 'add_hosts'));

        // Autoptimize (comma-separated string).
        add_filter('autoptimize_filter_js_exclude', array($this, 'add_patterns_csv'));

        // W3 Total Cache minify.
        add_filter('w3tc_minify_js_do_tag_minification', array($this, 'w3tc_skip'), 10, 3);

        // Attributes on the enqueued banner script.
        add_filter('script_loader_tag', array($this, 'tag_attributes'), 10, 2);
    }

    /**
     * @param mixed $list Existing exclusions.
     * @return array
     */
    public function add_patterns($list)
    {
        return array_values(array_unique(array_merge((array) $list, self::PATTERNS)));
    }

    /**
     * Delay-JS exclusions: Okito plus the consent APIs it talks to.
     *
     * @param mixed $list Existing exclusions.
     * @return array
     */
    public function add_delay_patterns($list)
    {
        return array_values(array_unique(array_merge($this->add_patterns($list), self::CONSENT_API_PATTERNS)));
    }

    /**
     * @param mixed $list Existing excluded hosts.
     * @return array
     */
    public function add_hosts($list)
    {
        $host = wp_parse_url(OKITO_CDN_BASE_URL, PHP_URL_HOST);
        return array_values(array_unique(array_merge((array) $list, $host ? array($host) : array())));
    }

    /**
     * @param mixed $list Existing excluded script handles.
     * @return array
     */
    public function add_handles($list)
    {
        return array_values(array_unique(array_merge((array) $list, array('okito-cookie-consent-cdn'))));
    }

    /**
     * @param string $list Comma-separated exclusions.
     * @return string
     */
    public function add_patterns_csv($list)
    {
        $items = array_filter(array_map('trim', explode(',', (string) $list)));
        return implode(', ', array_unique(array_merge($items, self::PATTERNS)));
    }

    /**
     * @param bool   $do         Whether W3TC minifies this tag.
     * @param string $script_tag Script tag.
     * @return bool
     */
    public function w3tc_skip($do, $script_tag)
    {
        return self::is_okito((string) $script_tag) ? false : $do;
    }

    /**
     * @param string $tag    Script tag HTML.
     * @param string $handle Script handle.
     * @return string
     */
    public function tag_attributes($tag, $handle)
    {
        if ('okito-cookie-consent-cdn' !== $handle || false !== strpos($tag, 'nowprocket')) {
            return $tag;
        }
        return preg_replace('/<script\b/', '<script ' . self::SCRIPT_ATTRIBUTES, $tag, 1);
    }

    /**
     * @param string $haystack Script tag or content.
     * @return bool
     */
    public static function is_okito($haystack)
    {
        foreach (self::PATTERNS as $pattern) {
            if (false !== strpos($haystack, $pattern)) {
                return true;
            }
        }
        return false;
    }
}
