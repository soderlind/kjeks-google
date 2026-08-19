=== Kjeks Google ===
Contributors: soderlind
Tags: cookies, consent, google, gtm, analytics
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 8.3
Requires Plugins: kjeks
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Google Tag Manager and GA4 for Kjeks, using Consent Mode v2 with default-denied signals and a consent-gated container.

== Description ==

Kjeks Google adds Google Tag Manager and Google Analytics 4 to the Kjeks cookie-consent plugin.

* Consent Mode v2 signals default to denied before any Google code loads.
* The Google container is withheld until the configured category is granted through Kjeks, so Google cannot bypass the consent layer.
* Consent signals update automatically when the visitor changes their choice.
* Network default with per-site overrides.

Requires the Kjeks plugin.

== Installation ==

1. Install and network-activate Kjeks.
2. Place this plugin in `wp-content/plugins/kjeks-google` and network-activate it.
3. Set network defaults under **Network Admin → Settings → Kjeks Google**.
4. Override per site under **Settings → Kjeks Google**.

== Changelog ==

= 0.1.0 =
* Initial release: GTM + GA4 via Consent Mode v2, gated through Kjeks.
