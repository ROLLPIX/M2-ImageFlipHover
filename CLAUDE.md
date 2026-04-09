# CLAUDE.md - Project Guide for AI Assistants

## Project Overview

This is a Magento 2 module called **Rollpix_ImageFlipHover** that provides product image flip on hover functionality for category pages, widgets, sliders, product grids, CMS blocks, and Page Builder content.

**Repository:** https://github.com/ROLLPIX/M2-ImageFlipHover

## Directory Structure (Composer-ready)

```
M2-ImageFlipHover/
├── Block/Adminhtml/System/Config/
│   └── ModuleVersion.php         # Version display in admin config
├── etc/                          # Configuration files
│   ├── module.xml                # Module declaration
│   ├── di.xml                    # Dependency injection (plugins)
│   ├── config.xml                # Default config values
│   ├── acl.xml                   # Admin ACL
│   └── adminhtml/system.xml      # Admin config fields (info + general + locations)
├── Helper/Config.php             # Config helper
├── Model/
│   ├── ImageFlipService.php      # Core flip image service
│   └── Config/Source/            # Dropdown source models
│       ├── ImageRoles.php
│       └── AnimationType.php
├── Plugin/                       # Magento plugins
│   ├── Product/
│   │   ├── ImagePlugin.php       # ImageFactory plugin
│   │   └── CollectionPlugin.php  # Collection plugin
│   └── Block/Product/
│       └── ImagePlugin.php       # Image block HTML plugin
├── ViewModel/ImageFlip.php       # Frontend ViewModel
├── view/frontend/                # Frontend assets
│   ├── layout/default.xml
│   ├── templates/
│   ├── web/css/image-flip.css
│   └── web/js/image-flip.js
├── i18n/                         # Translation files
│   ├── en_US.csv
│   ├── es_ES.csv
│   └── es_AR.csv
├── docs/                         # Project documentation
│   ├── SELF-REVIEW.md
│   ├── TASKS.md
│   └── ACCEPTANCE.md
├── composer.json                 # Composer package definition
├── registration.php              # Module registration
├── README.md                     # Documentation (EN)
├── manual.md                     # User manual (ES)
├── CHANGELOG.md                  # Version history
└── CLAUDE.md                     # This file
```

## Installation

### Via Composer
```bash
composer require rollpix/module-image-flip-hover
bin/magento module:enable Rollpix_ImageFlipHover
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

### Manual
Copy contents to `app/code/Rollpix/ImageFlipHover/`

## Key Technical Details

### Plugin Architecture

The module uses three plugins to intercept image rendering:

1. **CollectionPlugin** (`beforeLoad`) - Adds flip image attributes to product collection queries
2. **ImagePlugin on ImageFactory** (`afterCreate`) - Attaches flip image data to Image block
3. **ImagePlugin on Image Block** (`afterToHtml`) - Injects flip image HTML into output

### Image Resolution Logic (ImageFlipService)

```
1. Try primary role (from config)
   └── If role = 'second_image' → Query gallery for 2nd image by position (filtered by store_id=0)
   └── Else → Query EAV varchar table for attribute value
2. If no image found, try fallback role (same logic)
3. For configurable products: if parent has no flip image, try child simple products
   └── Query catalog_product_super_link for child IDs
   └── Try primary role on children, then fallback role
4. Return resized image URL via ImageHelper
```

### Database Tables Used

- `eav_attribute` - Get attribute ID by code
- `catalog_product_entity_varchar` - Get image value for custom roles
- `catalog_product_entity_media_gallery` - Gallery images
- `catalog_product_entity_media_gallery_value` - Gallery position/disabled (filtered by store_id=0)
- `catalog_product_entity_media_gallery_value_to_entity` - Gallery-product link
- `catalog_product_super_link` - Configurable product child IDs

### Config Paths

All config stored under `rollpix_imageflip/`:
- `general/enabled`
- `general/primary_role`
- `general/fallback_role`
- `general/animation_type`
- `general/animation_speed`
- `general/desktop_only`
- `locations/category_page`
- `locations/widget_products`
- `locations/search_results`
- `locations/related_products`
- `locations/cms_blocks`
- `locations/page_builder`

### JavaScript Events Listened

The JS listens for dynamic content events:
- `contentUpdated` (Magento standard)
- `pagebuilder:renderAfter` (Page Builder)
- `ajaxComplete` (jQuery AJAX)
- Slick, Owl Carousel, Swiper events
- `amscroll_after_load` (Amasty)
- `catalog_product_list_loaded`

### JavaScript Public API

```javascript
window.rollpixImageFlip.init();       // Re-initialize
window.rollpixImageFlip.preload();    // Preload all flip images
window.rollpixImageFlip.lazyPreload(); // Lazy preload with IntersectionObserver
```

## Common Tasks

### After Making Changes

```bash
rm -rf generated/code/* generated/metadata/*
bin/magento setup:di:compile
bin/magento cache:flush
```

### Testing the Module

1. Enable module in admin config
2. Set primary role to "Second Gallery Image" for easiest testing
3. Ensure products have 2+ images in gallery
4. Visit category page and hover over product images
5. Test in Page Builder by adding Products content type
6. Test in CMS block with product widget

### Adding New Animation Type

1. Add option to `Model/Config/Source/AnimationType.php`
2. Add CSS styles to `view/frontend/web/css/image-flip.css`
3. Update translation files in `i18n/`
4. Clear cache

### Adding New Image Role Option

Custom `media_image` attributes are auto-detected. For special options like "second_image":
1. Add option in `Model/Config/Source/ImageRoles.php`
2. Handle in `Model/ImageFlipService.php` `getImageByRoleOrSecond()` method
3. Update translation files

### Adding New Location Option

1. Add constant and method in `Helper/Config.php`
2. Add field in `etc/adminhtml/system.xml`
3. Add default value in `etc/config.xml`
4. Update `getConfigArray()` in Config helper
5. Update translation files in `i18n/`

## Dependencies

- Magento_Catalog
- Magento_CatalogWidget
- Magento_Eav

## Vendor

- Vendor: Rollpix
- Module: ImageFlipHover
- Full name: Rollpix_ImageFlipHover
- Composer package: rollpix/module-image-flip-hover

## Language

The module UI is in Spanish. Labels and comments in system.xml are in Spanish.
Translation files available: en_US, es_ES, es_AR (with voseo).

## Version

Current version: 2.0.0 (branch `hover-slider`)

## Local Development & Testing Environment

### Docker (Magento 2 + Hyvä)

A Docker-based Magento instance exists at `/home/nicolas/magento-hyva-test` (WSL Ubuntu) for testing.
Managed via `docker compose` with Mark Shust's docker-magento setup.

**Containers:** magento-hyva-test-phpfpm-1, magento-hyva-test-app-1, etc.

**Access:**
- **Storefront:** `https://magento.test` (self-signed SSL, add to hosts: `127.0.0.1 magento.test`)
- **Admin:** `https://magento.test/admin` — user: `john.smith` / pass: `password123`
- **PhpMyAdmin:** `http://localhost:8080` — user: `magento` / pass: `magento`
- **DB:** host=db, database=magento, user=magento, pass=magento

**Themes installed:**
- `Magento/luma` (theme_id=3) — Luma standard
- `Hyva/default` (theme_id=4) — Hyvä base
- `Rollpix/hyva-default` (theme_id=5) — Rollpix child of Hyvä

**Modules installed:**
- `Rollpix_ImageFlipHover` — main module (enabled)
- `Rollpix_ImageFlipHoverHyvaCompat` — Hyvä compat sub-module (disable for Luma testing, enable for Hyvä)

**Test categories with products (226 products total):**
- `https://magento.test/nutricion-saludable` — parent category, many products
- `https://magento.test/nutricion-saludable/omega-3` — has product OMEGA 3 x60 (entity_id=29) with **4 gallery images** (best for slider testing)
- `https://magento.test/nutricion-saludable/control-de-peso` — 5 products, all with 2 images
- `https://magento.test/nutricion-saludable/digest-detox` — 3 products with 2 images

### Sync & Deploy Workflow

The module source is at `c:\Dropbox\ROLLPIX\repos\M2-flip-image-hover\M2-ImageFlipHover`.
Docker mounts `./src/app/code` so files must be copied to WSL:

```bash
# 1. Sync module to Docker
wsl -d Ubuntu -e bash -c "rm -rf /home/nicolas/magento-hyva-test/src/app/code/Rollpix/ImageFlipHover && cp -r '/mnt/c/Dropbox/ROLLPIX/repos/M2-flip-image-hover/M2-ImageFlipHover' /home/nicolas/magento-hyva-test/src/app/code/Rollpix/ImageFlipHover"

# 2. For HyvaCompat (only when testing Hyvä):
wsl -d Ubuntu -e bash -c "rm -rf /home/nicolas/magento-hyva-test/src/app/code/Rollpix/ImageFlipHoverHyvaCompat && cp -r '/mnt/c/Dropbox/ROLLPIX/repos/M2-flip-image-hover/M2-ImageFlipHover/HyvaCompat' /home/nicolas/magento-hyva-test/src/app/code/Rollpix/ImageFlipHoverHyvaCompat"

# 3. Compile & deploy (needed after PHP changes)
docker exec magento-hyva-test-phpfpm-1 bash -c "rm -rf generated/code/Rollpix var/cache/* var/page_cache/* && bin/magento setup:di:compile && bin/magento setup:static-content:deploy -f && bin/magento cache:flush"

# 4. For CSS/JS-only changes (faster, no compile needed):
docker exec magento-hyva-test-phpfpm-1 bash -c "rm -rf pub/static/frontend/Magento/luma/es_AR/Rollpix_ImageFlipHover/ var/view_preprocessed/* var/cache/* var/page_cache/* && bin/magento setup:static-content:deploy -f && bin/magento cache:flush"
```

### Switch Themes for Testing

```bash
# Switch to Luma (theme_id=3):
docker exec magento-hyva-test-phpfpm-1 bash -c "mysql -h db -u magento -pmagento magento -e \"UPDATE core_config_data SET value=3 WHERE path='design/theme/theme_id'\""
docker exec magento-hyva-test-phpfpm-1 bin/magento cache:flush

# Switch to Hyvä (theme_id=5):
docker exec magento-hyva-test-phpfpm-1 bash -c "mysql -h db -u magento -pmagento magento -e \"UPDATE core_config_data SET value=5 WHERE path='design/theme/theme_id'\""
docker exec magento-hyva-test-phpfpm-1 bin/magento cache:flush
```

### HyvaCompat Module Toggle

```bash
# Disable for Luma testing:
docker exec magento-hyva-test-phpfpm-1 bin/magento module:disable Rollpix_ImageFlipHoverHyvaCompat

# Enable for Hyvä testing:
docker exec magento-hyva-test-phpfpm-1 bin/magento module:enable Rollpix_ImageFlipHoverHyvaCompat
# Then: setup:upgrade + setup:di:compile + cache:flush
```

### Set Module Mode via DB

```bash
# Set mode to slider:
docker exec magento-hyva-test-phpfpm-1 bash -c "mysql -h db -u magento -pmagento magento -e \"INSERT INTO core_config_data (scope, scope_id, path, value) VALUES ('default', 0, 'rollpix_imageflip/general/mode', 'slider') ON DUPLICATE KEY UPDATE value='slider'\""

# Set mode to flip:
docker exec magento-hyva-test-phpfpm-1 bash -c "mysql -h db -u magento -pmagento magento -e \"UPDATE core_config_data SET value='flip' WHERE path='rollpix_imageflip/general/mode'\""

docker exec magento-hyva-test-phpfpm-1 bin/magento cache:flush
```

### Current State of Development (hover-slider branch)

**Working:**
- Admin config UI (mode selector, all slider fields, desktop/mobile independent config)
- PHP data pipeline: batch gallery preload, slider data injection in HTML
- Plugin branching: flip mode untouched, slider mode injects gallery URLs + config
- JS initialization, preloading, arrows, indicators (pills/dots/bars/counter)
- Flip-only behavior for 2-image products in slider mode
- HyvaCompat sub-module (JS vanilla, layout swap)

**WIP / Known issues:**
- Slide transition (track approach): viewport `<span>` needed `display: block` for `overflow: hidden` to clip the track — fix applied, needs testing
- Hyvä PLP: PHP plugins inject data-attributes into Image block HTML (works because Hyvä still calls `$blockImage->toHtml()` via `setTemplate('Magento_Catalog::product/list/image.phtml')`)
- HyvaCompat layout removes Luma JS block — must be disabled when testing with Luma theme

**Not yet implemented:**
- Indicator color theme (dark/light) configuration
- Full visual testing of all transition types
- Mobile testing
- Performance benchmarking with large catalogs
