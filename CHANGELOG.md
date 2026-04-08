# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.3.3] - 2026-04-08

### Fixed
- Skip flip image when it is the same file as the base image (was causing invisible flip on products where `image_on_hover` role pointed to the same photo as `image`)
- Gallery query now uses current store ID instead of only default store, with `GROUP BY` to prevent duplicate rows in multi-store setups
- Fix primary role bypassing placeholder detection
- Fix ModuleInfo constructor: add missing authSession parameter

### Added
- Module info group in admin config: module name, version (from composer.json), GitHub link

## [1.2.1] - 2026-04-07

### Fixed
- Fix flip image fallback not working for configurable products: gallery query missing `store_id` filter caused duplicate rows per store view, corrupting the `OFFSET` and returning the base image instead of the actual second gallery image
- Change default `fallback_role` from `small_image` to `second_image` for better out-of-the-box behavior

### Added
- Configurable product child fallback: when the parent configurable has no flip image, the module now tries child simple products as last resort

## [1.2.0] - 2025-12-20

### Added
- Desktop-only mode: new config option to restrict flip effect to screens wider than 768px

### Fixed
- Mobile click/tap issue when desktop-only mode is disabled

## [1.1.1] - 2025-12-15

### Fixed
- Keep module name untranslated in admin menu
- Fix admin menu: move config under ROLLPIX tab

## [1.1.0] - 2025-12-01

### Added
- Initial release
- Product image flip on hover for category pages, widgets, search results, related products, CMS blocks, and Page Builder
- Multiple animation types: fade, slide (left/right/up/down), zoom, flip (horizontal/vertical)
- Configurable primary and fallback image roles
- "Second Gallery Image" option for automatic flip image selection
- Custom `media_image` attribute auto-detection
- Dynamic content support: AJAX, sliders (Slick, Owl Carousel, Swiper), Amasty Infinite Scroll
- Admin configuration per store view
- Translation files: en_US, es_ES, es_AR (voseo)
