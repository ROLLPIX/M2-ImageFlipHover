# Task Log — Rollpix_ImageFlipHover

Development history organized by session. Each entry documents what was done,
decisions made, and what's pending.

---

## 2026-04-07 — Nicolas Marquevich + Claude

### Done
- Diagnosed and fixed flip image not working for configurable products
- Fixed `store_id` bug in `getSecondGalleryImageByProductId`: missing filter caused duplicate rows per store view, corrupting the SQL `OFFSET` and returning the base image instead of the second gallery image
- Added configurable product child fallback: when parent has no flip image, module now queries `catalog_product_super_link` to find child IDs and tries their galleries
- Changed default `fallback_role` from `small_image` to `second_image` for better out-of-the-box behavior
- Created CHANGELOG.md, docs/TASKS.md, docs/ACCEPTANCE.md, docs/SELF-REVIEW.md, manual.md
- Version bump to 1.2.1

### Decisions
- Used `store_id = 0` (admin default) for gallery query filtering instead of current store ID to keep it simple and avoid injecting StoreManagerInterface
- Child fallback iterates children one by one for `second_image` role (not batched) — acceptable since it only fires when parent has no flip image
- Changed default fallback to `second_image` because `small_image` almost always returns the same image as the base (useless flip)

### Issues found
- The `LEFT JOIN` on `catalog_product_entity_media_gallery_value` without `store_id` filter produced N rows per image (one per store view), making `LIMIT 1 OFFSET 1` return the same first image from a different store instead of the actual second image
- The `owlCarousel is not a function` error reported by user is from the site's own inline JS, not from this module

### Pending
- system.xml missing `info` group (module name, version frontend_model, repo link, manual link)
- module.xml still has `setup_version` attribute (should be removed)
- PHPStan configuration not present in the repo
- Test in all locations (widgets, search, related, CMS, Page Builder)

---

## 2025-12-20 — Nicolas Marquevich

### Done
- Added desktop-only mode (config option + CSS + JS)
- Fixed mobile click/tap issue
- Version bump to 1.2.0

---

## 2025-12-15 — Nicolas Marquevich

### Done
- Fixed admin menu: moved config to ROLLPIX tab
- Kept module name untranslated in admin menu
- Version bump to 1.1.1

---

## 2025-12-01 — Nicolas Marquevich

### Done
- Initial release v1.1.0
- Core flip image functionality with plugin architecture
- 8 animation types, location controls, i18n
- Reorganized repository structure for Composer installation
