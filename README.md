# Kjeks Google

Google Tag Manager and Google Analytics 4 for
[Kjeks](https://github.com/soderlind/kjeks), wired through **Consent Mode v2**.
Requires the Kjeks plugin.

> Part of the **kjeks family** — an adapter over the Kjeks public API. See the
> [kjeks ecosystem overview](https://github.com/soderlind/kjeks/blob/main/docs/architecture.md#9-ecosystem-the-kjeks-family).

## How it works

Two layers keep Google inside the Kjeks consent layer (see Kjeks grilling Q7):

1. **Consent Mode v2 defaults are denied** — before any Google code runs, the
   add-on sets `ad_storage`, `ad_user_data`, `ad_personalization`, and
   `analytics_storage` to `denied`.
2. **The container is gated** — the GTM/gtag container is registered with Kjeks
   as an inert script and only activates when the configured category is
   granted, so Google can never load before consent.

When the visitor changes their choice, the add-on pushes a Consent Mode
`update`, mapping:

| Kjeks category | Consent Mode signals |
| --- | --- |
| `analytics` granted | `analytics_storage: granted` |
| `marketing` granted | `ad_storage`, `ad_user_data`, `ad_personalization: granted` |

## Configuration

- **Network Admin → Settings → Kjeks Google** — network defaults.
- **Settings → Kjeks Google** — per-site overrides (blank inherits the default).

Fields: GTM container ID (`GTM-XXXXXX`), GA4 measurement ID (`G-XXXXXXX`), and
the category that must be granted before the container loads (default
`analytics`).

## Filters

- `kjeks_google_config` — filter the resolved `{ gtm_id, ga4_id, gating_category }`.

## Notes and limitations

- The GTM `<noscript>` iframe fallback is intentionally omitted; it would bypass
  the JavaScript consent gate.
- Gating uses a single category. If a container mixes analytics and advertising
  tags, choose the stricter category or split containers.
- This add-on assists with consent wiring; it does not guarantee legal
  compliance.

## License

[GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html)
