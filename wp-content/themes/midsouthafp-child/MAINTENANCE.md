# MidSouth AFP Child Theme — Maintenance Guide
Version: 1.0.6 | Theme: midsouthafp-child (parent: Divi)
Repo: https://github.com/redsurfer1/midsouthafp

## One-time setup URLs (run once after each deployment)
| URL | Purpose |
|-----|---------|
| /wp-admin/?run_alt_fix=1 | Bulk-set alt text on images missing it |
| /wp-admin/?msafp_yoast_social=1 | Configure Yoast OG, Twitter, page meta |
| /wp-admin/?generate_og_image=1 | Generate 1200×630 branded OG image |
| /wp-admin/?purge_divi_cache=1 | Clear Divi static CSS + all caches |

## Ongoing admin URLs
| URL | Purpose |
|-----|---------|
| /wp-admin/?msafp_health=1 | Full health check dashboard |
| /wp-json/midsouthafp/v1/health | Lightweight JSON health (no auth) |
| /?audit_ids=1 (site front, admin logged in) | Duplicate ID + H1 checker |
| /wp-admin/?generate_rollback_url=1 | Get emergency rollback URL (bookmark before changes) |

## Monthly checks
- [ ] Run /wp-admin/?msafp_health=1 and confirm all rows pass
- [ ] Check PageSpeed Insights — maintain performance ≥ 85
- [ ] Review Yoast SEO issues panel for new warnings
- [ ] Verify next quarterly event is entered in The Events Calendar
- [ ] Check Google Search Console for crawl errors or coverage drops
- [ ] Review LinkedIn and Facebook share previews via debuggers

## Quarterly checks
- [ ] Update event schema dates for next quarter in TEC (automatic
      via dynamic schema, but verify event is published in TEC)
- [ ] Confirm CTP credit information is current on Resources page
- [ ] Review member audience section for accuracy
- [ ] Test all forms (Fluent Forms) submit correctly

## Before every WordPress / Divi / plugin update
1. Take a full backup (UpdraftPlus or host snapshot)
2. Run /wp-admin/?generate_rollback_url=1 — bookmark the URL
3. Test on staging if available; otherwise update one plugin at a time
4. After updates, run /wp-admin/?purge_divi_cache=1
5. Check homepage and events page visually
6. Run /wp-admin/?msafp_health=1

## Child theme file reference
| File | Purpose |
|------|---------|
| functions.php | All hooks, filters, schema, helpers |
| style.css | Hero, sticky nav, screen-reader CSS |
| front-page.php | Homepage template (Divi override) |
| inc/homepage-hero.php | Hero section markup + TEC next-event |
| inc/id-audit.php | Duplicate ID + H1 DOM audit tool |
| inc/rollback.php | Emergency theme rollback helper |
| inc/generate-og-image.php | Programmatic OG image generator |
| MAINTENANCE.md | This file |

## Adding a new event
1. Events → Add New in WordPress admin
2. Set title, date/time, venue (Seasons 52 or new location)
3. Add description for the event (used in Event schema and hero)
4. Publish — the hero "Next Event" card and JSON-LD update automatically

## Updating membership information
1. Pages → membership-invoice → Edit
2. Update content and re-publish
3. No code changes needed — hero CTA resolves URL dynamically

## Divi page builder notes
- All page content is managed in Divi Builder on each page
- The hero section is injected ABOVE Divi content via et_before_main_content
- Do NOT add an H1 heading module to the homepage in Divi Builder
  (the hero already provides the H1 — adding another creates duplicate H1s)
- If Divi Builder fails to load after an update, remove et-core-common
  from the $defer_handles array in functions.php (lines ~60-80)

## Emergency contacts / resources
- Divi support: https://www.elegantthemes.com/documentation/
- TEC support: https://theeventscalendar.com/support/
- Google Search Console: https://search.google.com/search-console/
- Schema validator: https://validator.schema.org
- PageSpeed Insights: https://pagespeed.web.dev
- OG debugger (Facebook): https://developers.facebook.com/tools/debug/
- Twitter card validator: https://cards-dev.twitter.com/validator

## Known limitations / future improvements
- Event schema uses OfflineEventAttendanceMode for all events.
  If online/hybrid events are added, extend midsouthafp_child_build_event_schema()
  to check a TEC virtual event field.
- The OG image (og-midsouthafp-1200x630.png) was programmatically generated.
  Consider replacing with a professionally designed version.
- get_page_by_path() is deprecated in newer WordPress. If PHP notices
  appear in future WP versions, replace with a WP_Query lookup.
- The Yoast social config helper (?msafp_yoast_social=1) should be
  re-run if the front page ID changes (e.g., after a site migration).
