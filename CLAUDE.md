# CLAUDE.md - Project Guide for AI Assistants

## Project Overview

This is a Magento 2 module called **Rollpix_ImageFlipHover** that provides product image flip on hover functionality for category pages, widgets, sliders, product grids, CMS blocks, and Page Builder content.

## Directory Structure

```
app/code/Rollpix/ImageFlipHover/
├── etc/                          # Configuration files
│   ├── module.xml                # Module declaration
│   ├── di.xml                    # Dependency injection (plugins)
│   ├── config.xml                # Default config values
│   ├── acl.xml                   # Admin ACL
│   └── adminhtml/system.xml      # Admin config fields
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
├── composer.json
├── registration.php
└── README.md
```

## Key Technical Details

### Plugin Architecture

The module uses three plugins to intercept image rendering:

1. **CollectionPlugin** (`beforeLoad`) - Adds flip image attributes to product collection queries
2. **ImagePlugin on ImageFactory** (`afterCreate`) - Attaches flip image data to Image block
3. **ImagePlugin on Image Block** (`afterToHtml`) - Injects flip image HTML into output

### Image Resolution Logic (ImageFlipService)

```
1. Try primary role (from config)
   └── If role = 'second_image' → Query gallery for 2nd image by position
   └── Else → Query EAV varchar table for attribute value
2. If no image found, try fallback role (same logic)
3. Return resized image URL via ImageHelper
```

### Database Tables Used

- `eav_attribute` - Get attribute ID by code
- `catalog_product_entity_varchar` - Get image value for custom roles
- `catalog_product_entity_media_gallery` - Gallery images
- `catalog_product_entity_media_gallery_value` - Gallery position/disabled
- `catalog_product_entity_media_gallery_value_to_entity` - Gallery-product link

### Config Paths

All config stored under `rollpix_imageflip/`:
- `general/enabled`
- `general/primary_role`
- `general/fallback_role`
- `general/animation_type`
- `general/animation_speed`
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

## Language

The module UI is in Spanish. Labels and comments in system.xml are in Spanish.
Translation files available: en_US, es_ES, es_AR (with voseo).

## Version

Current version: 1.1.0
