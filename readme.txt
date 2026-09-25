=== Okito Cookie Consent ===
Contributors: okitoapp
Tags: cookie consent, cookie banner, gdpr, google consent mode, ccpa
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Cookie consent banner for GDPR, CCPA, KVKK and LGPD with Google Consent Mode v2, IAB TCF v2.3, WP Consent API and geo-targeting. Free plan.

== Description ==

**Okito Cookie Consent** adds a customizable cookie consent banner to your WordPress site and keeps Google Analytics, Google Ads and your other tags in line with each visitor's choice. It helps you meet the GDPR, UK GDPR, ePrivacy, CCPA/CPRA and other US state privacy laws, Brazil's LGPD and Türkiye's KVKK.

You design the banner in the Okito dashboard; the plugin connects your site with one click and loads a single asynchronous script. No coding and no theme changes are needed.

= Why Okito =

* **Google Consent Mode v2** — ad_storage, analytics_storage, ad_user_data and ad_personalization are set before your Google tags run and updated the moment a visitor decides. Works with Google Analytics 4, Google Ads, Google Tag Manager and Site Kit by Google.
* **IAB TCF v2.3** — registered IAB Europe CMP (ID 508) with the full `__tcfapi`, Google Additional Consent (AC string) and Google's `enableAdvertiserConsentMode`, for sites that run AdSense, Ad Manager or programmatic ads.
* **WP Consent API** — every choice is passed to the WP Consent API, so Site Kit and other compatible plugins follow it automatically.
* **Geo-targeting by privacy law** — show the banner only where consent is required (EEA, UK, Switzerland, Türkiye, Brazil and similar) and keep measurement on elsewhere.
* **US privacy** — opt-out notice with a "Do Not Sell or Share My Personal Information" link, the IAB US Privacy API (`__uspapi`) and Global Privacy Control (GPC) support.
* **Automatic script blocking** — tracking scripts wait until the visitor consents to their category.
* **Cookie scanner** — Okito scans your site and sorts cookies into categories for your cookie policy.
* **Consent records** — every decision is logged as proof of consent.
* **50+ languages** — the banner speaks your visitor's language automatically.

= Built for WordPress =

* **Connect with Okito** — sign in, pick your site and the banner is live. No key to copy.
* **Fast** — one small asynchronous script. Nothing is rendered by PHP, so your pages stay light.
* **Caching and optimization friendly** — works with WP Rocket, LiteSpeed Cache, Autoptimize, SiteGround Optimizer, W3 Total Cache and Cloudflare; the consent scripts are never delayed, combined or deferred.
* **Site Health checks** — Tools → Site Health warns you about a second cookie plugin, a missing WP Consent API, Site Kit's Consent Mode setting and Google tags that load before your consent defaults.
* **Debug mode** — add `?okito_debug=1` to any URL to see whether the consent defaults load before your Google tags.

= Free plan =

Start free and upgrade when you need more websites or advanced features. See [Okito pricing](https://app.okito.com/pricing).

== Installation ==

= Install =

1. In WordPress, go to **Plugins → Add New**, search for **Okito Cookie Consent**, then click **Install Now** and **Activate**.
2. Or upload the ZIP under **Plugins → Add New → Upload Plugin**.

= Connect your site =

1. Go to **Okito → Settings**.
2. Click **Connect with Okito**, sign in (or create a free account) and choose your website.
3. You are sent back to WordPress with the cookie banner enabled.

Prefer to do it by hand? Copy the Website Key from the Okito dashboard, paste it into **Okito → Settings**, enable the banner and save.

= Recommended =

* Install the **WP Consent API** plugin so other plugins follow the visitor's choice.
* Using Site Kit by Google? Turn on **Consent Mode** under Site Kit → Settings → Admin Settings.
* Check **Tools → Site Health** for Okito's setup checks.

== Frequently Asked Questions ==

= How do I add a cookie consent banner to WordPress? =

Install Okito Cookie Consent, click **Connect with Okito** in Okito → Settings and choose your website. The banner appears on your site right away; change its design, texts and languages in the Okito dashboard.

= Does Okito support Google Consent Mode v2? =

Yes. Consent Mode v2 defaults (ad_storage, analytics_storage, ad_user_data, ad_personalization) are set before your Google tags load and updated as soon as the visitor makes a choice, in both basic and advanced setups.

= Does it work with Google Tag Manager and Site Kit? =

Yes. Keep your Google Tag Manager container or Site Kit as it is: Okito's defaults load first in the page head. Site Kit also reads the visitor's choice through the WP Consent API.

= Is Okito an IAB TCF CMP? =

Yes. Okito is registered with IAB Europe as CMP ID 508 and supports TCF v2.3, including the Google Additional Consent string.

= Can I show the banner only in the EU? =

Yes. With geo-targeting the banner opens only where a consent law requires it, and measurement stays on for visitors from other regions.

= Will the plugin slow down my website? =

No. It loads one asynchronous script; the banner is rendered in the visitor's browser, not by PHP on your server.

= Is it compatible with caching plugins? =

Yes. Okito tells WP Rocket, LiteSpeed Cache, Autoptimize, SiteGround Optimizer, W3 Total Cache and Cloudflare Rocket Loader to leave its consent scripts alone, so they are never delayed, combined or deferred.

= Do I need an Okito account? Is there a free plan? =

Yes, the banner is managed in an Okito account. You can create one for free while connecting the plugin.

= Does the plugin add links or branding to my site? =

The plugin only adds the consent script. What appears inside the banner is set in your Okito dashboard.

== Screenshots ==

1. WordPress admin dashboard with status and quick actions
2. Settings page with Connect with Okito and the Website Key field
3. Cookie banner and related tools in the Okito web application

== External services ==

This plugin relies on Okito, a third-party cookie consent and compliance platform operated by Okito (https://okito.com). It is required for the plugin to function: the banner, consent storage, IAB TCF v2.3 signals, Google Consent Mode v2 and the cookie scanner are all delivered by Okito's service. A free plan is available.

The plugin contacts the Okito service in three ways:

1. **CDN script load (visitor's browser, public pages only when the banner is enabled)**

   * **Endpoint:** `https://cdn.okito.com/js/{WEBSITE_KEY}`
   * **When:** On every public page view in the visitor's browser, after the plugin is enabled and a Website Key is configured.
   * **What is sent:** The visitor's browser performs a standard HTTPS request for the script file. This means Okito receives the Website Key (as part of the URL), the visitor's IP address, User-Agent and the standard `Referer` header sent by the browser. While the banner is displayed, it stores the visitor's consent choices in the browser (cookies / local storage) and reports anonymized consent metrics to Okito so they can be shown in your Okito dashboard.
   * **Why:** This is what renders the cookie consent banner, captures the visitor's choice, blocks/unblocks tagged third-party scripts and feeds the Okito analytics for that website.

2. **Admin "Test Connection" request (server-side, WordPress admin only)**

   * **Endpoint:** `https://cdn.okito.com/js/{WEBSITE_KEY}`
   * **When:** Only when an administrator clicks the "Test Connection" button on the Okito → Settings page.
   * **What is sent:** A standard HTTPS GET request from your server containing only the Website Key entered in the form. No visitor data, posts or user information is transmitted.
   * **Why:** To verify that the Website Key is valid against the Okito CDN.

3. **"Connect with Okito" (administrator's browser, WordPress admin only)**

   * **Endpoint:** `https://app.okito.com/integrations/wordpress/connect`
   * **When:** Only when an administrator clicks "Connect with Okito".
   * **What is sent:** The site's home URL, the URL of the plugin settings page and a random one-time code. After the administrator signs in to Okito and chooses a website, Okito sends the browser back to the settings page with the Website Key and the same code, which the plugin checks before saving the key.
   * **Why:** To connect the site to an Okito account without copying the Website Key by hand.

The plugin does not send any other data to Okito or any other third party. No personal data of WordPress visitors is transmitted by PHP code in this plugin.

Use of the Okito service is subject to:

* Okito Terms of Service: https://okito.com/terms-of-service/
* Okito Privacy Policy: https://okito.com/privacy-policy/

Site administrators are responsible for ensuring their own privacy policy describes the use of Okito where required by law. Suggested wording is registered with WordPress and visible under **Settings → Privacy** after this plugin is active.

== Privacy ==

This plugin:

* Saves your **Okito Website Key** and an **enabled/disabled** flag in the WordPress options table.
* When enabled, loads the script described in **External services** on public pages so visitors see the cookie banner and consent features.

Suggested wording for your Privacy Policy is available in WordPress under **Settings → Privacy** after this plugin is active.

== Changelog ==

= 1.1.2 =

* IAB TCF stub in the page head: the TCF API (__tcfapi) is available before Google tags run, even when they load before the Okito banner script

= 1.1.1 =

* WP Rocket "Delay JavaScript": the WP Consent API and Site Kit's Consent Mode script are no longer delayed until the first interaction, so Site Kit receives the visitor's choice right away

= 1.1.0 =

* Tested with WordPress 7.1
* One-click "Connect with Okito": sign in, pick the site and the banner is enabled, no key copying
* WP Consent API integration: visitor choices are passed to the WP Consent API (functional, preferences, statistics, marketing), so Site Kit and other compatible plugins follow them
* Google Consent Mode v2 defaults printed early in the page: region-aware, Global Privacy Control support and Okito's Google developer ID
* Compatibility with caching and optimization plugins (WP Rocket, LiteSpeed Cache, Autoptimize, SiteGround Optimizer, W3 Total Cache, Cloudflare Rocket Loader): Okito's scripts are never delayed, deferred or combined
* Site Health checks: banner setup, other active consent plugins, WP Consent API, Site Kit Consent Mode and whether Google tags load before the consent defaults
* Escaped all admin output, prepared database queries and other WordPress Plugin Check fixes

= 1.0.2 =

* Fixed a PHP 8+ fatal error when the cookie banner was enabled (script tag filter)
* Improved banner enable flag handling and WordPress 6.3+ async script loading
* Added compatibility helpers for WordPress 6.4–7.0 and PHP 7.4–8.x
* Script loading uses native `async` strategy on WordPress 6.3+ and a safe fallback on older supported versions

= 1.0.1 =

* Updated IAB TCF references to v2.3 in plugin UI and documentation
* Switched script delivery and connection checks to cdn.okito.com
* Improved script connection verification logic for CDN responses
* Added custom Okito admin menu icon

= 1.0.0 =

* Initial release
* CDN script injection in `wp_head`
* Settings for Website Key and enable/disable
* Connection test from WordPress admin
* Suggested privacy policy text for the site Privacy Policy page

== Upgrade Notice ==

= 1.1.2 =

Recommended for IAB TCF sites: Google tags find the TCF API from the first moment.

= 1.1.1 =

Recommended for sites using WP Rocket together with Site Kit and the WP Consent API.

= 1.1.0 =

WordPress 7.1 support, one-click connection and WP Consent API integration for Site Kit.

= 1.0.2 =

Important fix for PHP 8 sites when enabling the cookie banner.

= 1.0.1 =

Maintenance and compatibility update.
