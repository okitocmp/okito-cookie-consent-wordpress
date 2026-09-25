<?php
/**
 * Okito Public (Frontend) — Injects CDN banner script
 * 
 * The heavy lifting (banner rendering, consent management, TCF, Google Consent Mode)
 * is handled by the Okito CDN script. This class simply injects it.
 * 
 * @package OkitoCookieConsent
 */

if (!defined('ABSPATH')) {
    exit;
}

class Okito_Public
{
    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        // WP Consent API: default to opt-in. The Okito CDN script sets the
        // per-visitor consent type (opt-out for the US model and regions
        // without an opt-in law) and calls wp_set_consent() on every choice,
        // so Site Kit and other API consumers follow it.
        add_filter('wp_get_consent_type', array($this, 'consent_type'));

        $enabled = get_option('okito_enabled', false);
        $website_key = get_option('okito_website_key', '');

        if (!$enabled || empty($website_key)) {
            return;
        }

        // Google Consent Mode v2 defaults as early as possible in <head>
        // (priority 1), identical to the Okito CDN banner: opt-in regions
        // start denied until the visitor decides, other regions granted (Data
        // Transmission Controls / Global Consent Defaults), GPC denied.
        add_action('wp_head', array($this, 'print_consent_mode_defaults'), 1);

        // Enqueue CDN script on frontend.
        add_action('wp_enqueue_scripts', array($this, 'enqueue_cdn_script'));

        // Body class for optional CSS targeting
        add_filter('body_class', array($this, 'add_body_class'));
    }

    /**
     * Print the synchronous Google Consent Mode v2 bootstrap. Must run before
     * any GTM/gtag snippet in the page; the CDN banner script later pushes
     * consent updates when the visitor decides (and on reload).
     */
    public function print_consent_mode_defaults()
    {
        if (is_admin()) {
            return;
        }
        ?>
<!-- Okito Consent Mode -->
<script data-cfasync="false" data-no-optimize="1" data-no-defer="1" data-no-minify="1" nowprocket>
  // IAB TCF stub: __tcfapi exists before Google tags; the Okito script takes over.
  (function(){var w=window;if(typeof w.__tcfapi==="function")return;var q=[];
  w.__tcfapi=function(){var a=arguments;if(!a.length)return q;if(a[0]==="ping"&&typeof a[2]==="function"){a[2]({gdprApplies:undefined,cmpLoaded:false,cmpStatus:"stub",apiVersion:"2.2"},true);}else{q.push(a);}};w.__tcfapi.a=q;
  (function f(){if(w.frames.__tcfapiLocator)return;if(document.body){var i=document.createElement("iframe");i.name="__tcfapiLocator";i.style.display="none";document.body.appendChild(i);}else{setTimeout(f,5);}})();
  w.__okitoTcfApiBridgeBound=true;w.addEventListener("message",function(e){var s=typeof e.data==="string",p;try{p=s?JSON.parse(e.data):e.data;}catch(x){return;}var c=p&&p.__tcfapiCall;if(!c)return;w.__tcfapi(c.command,c.version,function(r,ok){var m={__tcfapiReturn:{returnValue:r,success:ok,callId:c.callId}};if(e.source)e.source.postMessage(s?JSON.stringify(m):m,"*");},c.parameter);},false);})();
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('consent', 'default', {
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
    analytics_storage: 'denied',
    functionality_storage: 'granted',
    personalization_storage: 'denied',
    security_storage: 'granted',
    region: ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','IS','LI','NO','GB','CH','TR','BR','CA','ZA','AU','SA','AR','AD','FO','US'],
    wait_for_update: 500
  });
  var okitoGpc = navigator.globalPrivacyControl === true;
  var okitoState = okitoGpc ? 'denied' : 'granted';
  gtag('consent', 'default', {
    ad_storage: okitoState,
    ad_user_data: okitoState,
    ad_personalization: okitoState,
    analytics_storage: okitoState,
    functionality_storage: 'granted',
    personalization_storage: okitoState,
    security_storage: 'granted',
    wait_for_update: okitoGpc ? 500 : 0
  });
  gtag('set', 'ads_data_redaction', true);
  gtag('set', 'developer_id.dZGJiMm', true);
</script>
<!-- End Okito Consent Mode -->
        <?php
    }

    /**
     * Enqueue the Okito CDN banner script.
     *
     * This single script handles everything:
     * - Banner rendering & positioning
     * - Cookie consent management (4-level storage)
     * - Google Consent Mode v2
     * - IAB TCF v2.3
     * - Script blocking until consent
     * - Multi-language auto-detection
     */
    public function enqueue_cdn_script()
    {
        if (is_admin()) {
            return;
        }

        $website_key = get_option('okito_website_key', '');
        if (empty($website_key)) {
            return;
        }

        $cdn_base = defined('OKITO_CDN_BASE_URL') ? OKITO_CDN_BASE_URL : 'https://cdn.okito.com';
        $script_url = $cdn_base . '/js/' . rawurlencode($website_key);

        wp_enqueue_script(
            'okito-cookie-consent-cdn',
            esc_url_raw($script_url),
            array(),
            OKITO_VERSION,
            array(
                'strategy'  => 'async',
                'in_footer' => false,
            )
        );
    }

    /**
     * WP Consent API consent type before the Okito script runs.
     */
    public function consent_type($type)
    {
        return get_option('okito_enabled', false) ? 'optin' : $type;
    }

    /**
     * Add body class when Okito is active
     */
    public function add_body_class($classes)
    {
        $classes[] = 'okito-consent-active';
        return $classes;
    }
}
