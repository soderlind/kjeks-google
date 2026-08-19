# Changelog

## 0.3.0 - 2026-08-19

- Self-updates from GitHub releases via the `wordpress-github-updater` library (bundled `plugin-update-checker`). Define the optional `KJEKS_GITHUB_TOKEN` constant for private repositories or higher GitHub API rate limits.
- Add GitHub Actions workflows to build and attach the release ZIP on published releases and on manual dispatch.
- Add a Composer manifest and load the Composer autoloader.
- Align the `KJEKS_GOOGLE_VERSION` constant with the plugin header version.

## 0.2.0 - 2026-08-19

- Extract Google tag resolution into a testable `GoogleTagConfig` module; Settings is now a thin admin adapter.

## 0.1.0 - 2026-08-19

- Initial release: Google Tag Manager and GA4 via Consent Mode v2, gated through the Kjeks consent layer, with network defaults and per-site overrides.
