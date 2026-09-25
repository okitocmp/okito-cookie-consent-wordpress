# Okito Cookie Consent for WordPress

Source of the [Okito Cookie Consent](https://wordpress.org/plugins/okito-cookie-consent/) WordPress plugin. Okito is a consent management platform (IAB TCF v2.3 CMP ID 508, Google Consent Mode v2).

- WordPress.org: https://wordpress.org/plugins/okito-cookie-consent/
- Okito: https://okito.com
- License: GPLv2 or later

## WP Consent API integration

The plugin integrates with the [WP Consent API](https://wordpress.org/plugins/wp-consent-api/), so Site Kit by Google, WooCommerce and every other plugin that reads the API follow the choice the visitor makes in the Okito banner.

### PHP (this repository)

| What | Where |
| --- | --- |
| Declares the plugin compliant with the WP Consent API (`wp_consent_api_registered_{plugin}`) | [`okito-cookie-consent.php`](okito-cookie-consent.php) |
| Default consent type `optin` (`wp_get_consent_type`) | [`includes/class-public.php`](includes/class-public.php) |
| Google Consent Mode v2 defaults printed first in `<head>` (before Site Kit / GTM / gtag) | [`includes/class-public.php`](includes/class-public.php) |
| Site Health checks: WP Consent API installed, Site Kit Consent Mode enabled, Google tags loading after the consent defaults, no second consent plugin | [`includes/class-site-health.php`](includes/class-site-health.php) |
| Caching / optimization plugins never delay, defer or combine the consent scripts | [`includes/class-compat.php`](includes/class-compat.php) |

### JavaScript (Okito banner script)

The banner script the plugin loads from `https://cdn.okito.com/js/{Website Key}` sets the per-visitor consent type and calls `wp_set_consent()` for every WP Consent API category on each choice (Accept all, Reject all, saved preferences) and on every page load for a stored choice (simplified excerpt):

```js
// Opt-in unless no opt-in decision gates this visitor (US opt-out model,
// or a region no opt-in law covers).
window.wp_consent_type = (US_MODE || GRANT_WHEN_NOT_REQUIRED) ? 'optout' : 'optin';
document.dispatchEvent(new CustomEvent('wp_consent_type_defined'));

function okitoSyncWpConsent(preferences) {
  if (typeof window.wp_set_consent !== 'function') {
    // wp-consent-api.js may load after the async banner script: apply on load.
    return deferUntilLoad(preferences);
  }
  var wpCategories = {
    'functional': true,
    'preferences': preferences.personalization ?? preferences.functional,
    'statistics': preferences.analytics,
    'statistics-anonymous': preferences.analytics,
    'marketing': preferences.advertisement
  };
  Object.keys(wpCategories).forEach(function (category) {
    window.wp_set_consent(category, wpCategories[category] ? 'allow' : 'deny');
  });
}
```

The same function also sends the Google Consent Mode `update` command, and it does so before any blocked Google tag is released.

### Try it

1. Install **Okito Cookie Consent**, **WP Consent API** and **Site Kit by Google** (enable Consent Mode in Site Kit → Settings → Admin Settings).
2. In **Okito → Settings**, click **Connect with Okito** (or paste your Website Key) and enable the banner.
3. Open the site in a private window and run `wp_has_consent('marketing')` in the console: `false` before a choice, `true` after **Accept all**, `false` after **Reject all**. The `wp_consent_*` cookies show every category.

## Structure

```
okito-cookie-consent.php     Plugin bootstrap, admin menu, settings, Connect with Okito
includes/class-public.php    Consent Mode defaults, banner script, WP Consent API type
includes/class-compat.php    Caching / optimization plugin exclusions
includes/class-site-health.php  Site Health checks
includes/okito-compat.php    WordPress / PHP compatibility helpers
admin/                       Dashboard and settings pages
readme.txt                   WordPress.org readme (External services, changelog)
```
