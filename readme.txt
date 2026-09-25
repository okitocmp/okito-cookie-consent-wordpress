=== Okito Cookie Consent ===
Contributors: okitoapp
Tags: cookies, gdpr, consent, privacy, compliance
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional cookie consent management with GDPR, CCPA, LGPD, and IAB TCF v2.3 compliance. Connects your site to the Okito platform.

== Description ==

Okito Cookie Consent is a lightweight WordPress plugin that connects your website to the **Okito** cookie consent platform. A single CDN script handles banner display, consent management, Google Consent Mode v2, IAB TCF v2.3, the WP Consent API, and automatic language detection.

= How it works =

1. Install the plugin
2. Get your **Website Key** from [app.okito.com](https://app.okito.com)
3. Paste the key into Settings and save — or simply click **Connect with Okito** in Okito → Settings, sign in and pick your site

The plugin injects a single `script` tag into your site's `head`. Banner rendering is handled by Okito's script, not by PHP on your server.

= Key features =

* Full compliance — GDPR, CCPA, LGPD, IAB TCF v2.3
* Single async CDN script in the document head
* Visual banner builder in the Okito dashboard
* Automatic language detection (many languages)
* Google Consent Mode v2 integration
* WP Consent API integration (Site Kit and other compatible plugins follow the visitor's choice)
* Works with caching and optimization plugins (WP Rocket, LiteSpeed Cache, Autoptimize, SiteGround Optimizer, W3 Total Cache, Cloudflare)
* Site Health checks for common consent setup problems
* Analytics and consent metrics in the Okito dashboard
* Cookie scanning and categorization via Okito
* Responsive banner layout
* Optional script blocking until consent (configured in Okito)

= Pricing =

Okito offers several plans including a free tier. See [pricing on app.okito.com](https://app.okito.com/pricing) for current options.

== Installation ==

= Automatic =

1. Go to Plugins → Add New
2. Search for "Okito Cookie Consent"
3. Click Install Now, then Activate

= Manual =

1. Upload the plugin folder to `/wp-content/plugins/`, or upload the ZIP under Plugins → Add New → Upload Plugin
2. Activate the plugin through the Plugins screen

= Setup =

1. Go to **Okito → Settings** in WordPress admin
2. Enter your Website Key from [app.okito.com/login](https://app.okito.com/login)
3. Enable the cookie consent banner and save

== Frequently Asked Questions ==

= Do I need an Okito account? =

Yes. Create an account at [app.okito.com](https://app.okito.com) to obtain a Website Key.

= Will this slow down my website? =

The plugin adds one asynchronous script tag. Heavy work runs in the browser and on Okito's infrastructure.

= Where do I customize the banner? =

In the Okito dashboard (for example the banner builder). Changes apply to your site according to your Okito configuration.

= Is it compatible with caching plugins? =

Yes. It is a standard script include and works with common caching setups.

= Does it support Google Consent Mode v2? =

Yes, when configured in your Okito account.

= Does the plugin add promotional links on my public site? =

The plugin itself only outputs the consent script and optional HTML comments identifying the integration. Any visible branding inside the banner is controlled in your Okito dashboard and should follow your compliance settings.

== Screenshots ==

1. WordPress admin dashboard with status and quick actions
2. Settings page with Website Key field
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

= 1.1.0 =

WordPress 7.1 support, one-click connection and WP Consent API integration for Site Kit.

= 1.0.2 =

Important fix for PHP 8 sites when enabling the cookie banner.

= 1.0.1 =

Maintenance and compatibility update.
