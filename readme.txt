=== Kjeks Google ===
Contributors: soderlind
Tags: cookies, consent, google, gtm, analytics
Requires at least: 6.8
Tested up to: 7.1
Requires PHP: 8.3
Requires Plugins: kjeks
Stable tag: 0.3.1
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
3. On multisite, set network defaults under **Network Admin → Settings → Kjeks Google**.
4. Configure per site under **Settings → Kjeks Google** (the only screen on a single site).

== Changelog ==

= 0.3.1 =
* Drop the internal Dependency class in favour of a one-line function_exists() guard; the Requires Plugins header already prevents activation without Kjeks.
* Add unit tests (Pest + Brain Monkey) covering Google tag config resolution and the settings option reads.

= 0.3.0 =
* Self-updates from GitHub releases via wordpress-github-updater (bundled plugin-update-checker). Define the optional KJEKS_GITHUB_TOKEN constant for private repositories.
* Add GitHub Actions workflows to build and attach the release ZIP.
* Add a Composer manifest and load the Composer autoloader.
* Align the KJEKS_GOOGLE_VERSION constant with the plugin header version.

= 0.2.0 =
* Extract Google tag resolution into a testable GoogleTagConfig module; Settings is now a thin admin adapter.

= 0.1.0 =
* Initial release: GTM + GA4 via Consent Mode v2, gated through Kjeks.
