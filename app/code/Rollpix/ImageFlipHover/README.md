# Rollpix Image Flip Hover for Magento 2

A Magento 2 module that displays an alternate product image on mouse hover in category pages, product widgets, sliders, and grids. Supports custom image roles with fallback configuration.

## Features

- **Configurable Image Roles**: Select any image role (native or custom) for the hover image
- **Second Gallery Image Option**: Use the second image in the product gallery as flip image (no role assignment needed)
- **Fallback Support**: Configure a fallback image role when the primary role is not set
- **Multiple Animation Types**: Fade, Slide (all directions), Zoom, and 3D Flip animations
- **Configurable Animation Speed**: Customize the transition duration
- **Location Control**: Enable/disable for specific locations (category pages, widgets, search results, related products, CMS blocks, Page Builder)
- **Page Builder Support**: Full support for Page Builder Products widget and dynamic content
- **CMS Block Support**: Works with product widgets embedded in CMS blocks and pages
- **Third-Party Compatible**: Works with any module that uses Magento's standard `ImageFactory` for product images
- **Touch Device Support**: Tap to toggle flip on mobile devices
- **Lazy Loading**: Flip images are loaded on first hover to optimize performance
- **Dynamic Content Support**: Automatically initializes for AJAX-loaded content (infinite scroll, sliders, etc.)
- **Accessibility**: Respects `prefers-reduced-motion` preference

## Requirements

- Magento 2.4.x
- PHP 7.4 or higher

## Installation

### Via Composer (recommended)

```bash
composer require rollpix/module-image-flip-hover
bin/magento module:enable Rollpix_ImageFlipHover
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

### Manual Installation

1. Create the directory `app/code/Rollpix/ImageFlipHover`
2. Copy all module files to this directory
3. Run the following commands:

```bash
bin/magento module:enable Rollpix_ImageFlipHover
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

## Configuration

Navigate to **Stores > Configuration > General > Image Flip on Hover**

### General Settings

| Setting | Description | Default |
|---------|-------------|---------|
| Enable Image Flip on Hover | Enable or disable the module globally | Yes |
| Primary Image Role | The image role to display on hover (includes "Second Gallery Image" option) | Second Gallery Image |
| Fallback Image Role | Used when product doesn't have the primary role image | Small Image |
| Animation Type | Type of transition animation | Fade |
| Animation Speed (ms) | Duration of the animation in milliseconds | 300 |

### Enable Locations

| Setting | Description | Default |
|---------|-------------|---------|
| Category Pages | Enable for product listing pages | Yes |
| Product Widgets | Enable for slider/grid widgets | Yes |
| Search Results | Enable for search result pages | Yes |
| Related/Upsell/Cross-sell | Enable for related product blocks | Yes |
| CMS Blocks | Enable for product widgets in CMS blocks/pages | Yes |
| Page Builder | Enable for Page Builder Products widget | Yes |

## Image Role Options

### Native Magento Roles

- **Base Image** (`image`)
- **Small Image** (`small_image`)
- **Thumbnail** (`thumbnail`)
- **Swatch Image** (`swatch_image`)

### Special Options

- **Second Gallery Image (Position #2)**: Automatically uses the second image in the product's media gallery ordered by position. This is the easiest option as it doesn't require assigning a specific role to images.

### Custom Image Roles

Any custom `media_image` type attribute you create will automatically appear in the dropdown. For example, if you create an attribute with code `rpx_product_image_on_hover`, it will be available for selection.

## Creating a Custom Image Role

To create a custom image role (e.g., "Image on Hover"):

1. Go to **Stores > Attributes > Product**
2. Click **Add New Attribute**
3. Set the following:
   - Default Label: `Image on Hover`
   - Catalog Input Type: **Media Image**
   - Attribute Code: `image_on_hover` (or any code you prefer)
4. In **Storefront Properties**:
   - Used in Product Listing: This option is NOT available for Media Image attributes (this is normal)
5. Save the attribute
6. Add it to your attribute set(s) under **Stores > Attributes > Attribute Set**
7. The new role will automatically appear in the module's configuration dropdown

### Assigning Images to Custom Roles

1. Edit a product in the admin
2. Go to **Images and Videos**
3. Upload or select an image
4. Click on the image to open the detail panel
5. In the **Role** dropdown, select your custom role (e.g., "Image on Hover")
6. Save the product

## Animation Types

| Animation | Description |
|-----------|-------------|
| Fade | Simple opacity crossfade |
| Slide Left | Image slides from right to left |
| Slide Right | Image slides from left to right |
| Slide Up | Image slides from bottom to top |
| Slide Down | Image slides from top to bottom |
| Zoom | Primary zooms in while flip zooms out |
| Flip Horizontal | 3D card flip on Y-axis |
| Flip Vertical | 3D card flip on X-axis |

## Compatibility with Third-Party Modules

This module works with any third-party slider/grid module that uses Magento's standard `Magento\Catalog\Block\Product\ImageFactory` to render product images. This includes:

- Native Magento product widgets
- Most third-party product sliders (Slick, Owl Carousel, Swiper)
- Product grids/carousels
- Custom product listing modules

The module uses plugins that hook into the core image rendering pipeline, so it doesn't require specific support from third-party modules.

### For AJAX-Loaded Content

The JavaScript automatically detects dynamically loaded content through:
- MutationObserver for DOM changes
- `contentUpdated` event (Magento standard)
- Common slider library events (Slick, Owl, Swiper)
- AJAX completion events

## Technical Documentation

### Architecture Overview

The module uses Magento's plugin system to intercept the product image rendering pipeline:

```
Product Collection Load
        │
        ▼
┌─────────────────────────────┐
│   CollectionPlugin          │  Adds flip image attributes to collection query
│   (beforeLoad)              │
└─────────────────────────────┘
        │
        ▼
┌─────────────────────────────┐
│   ImageFactory Plugin       │  Attaches flip image data to Image block
│   (afterCreate)             │
└─────────────────────────────┘
        │
        ▼
┌─────────────────────────────┐
│   Image Block Plugin        │  Injects flip image HTML into output
│   (afterToHtml)             │
└─────────────────────────────┘
        │
        ▼
┌─────────────────────────────┐
│   Frontend JavaScript       │  Handles hover events, lazy loading
│   (image-flip.js)           │
└─────────────────────────────┘
```

### Files Structure

```
Rollpix/ImageFlipHover/
├── etc/
│   ├── module.xml              # Module declaration
│   ├── di.xml                  # Dependency injection & plugin config
│   ├── config.xml              # Default configuration values
│   ├── acl.xml                 # Admin ACL resources
│   └── adminhtml/
│       └── system.xml          # Admin configuration fields
├── Helper/
│   └── Config.php              # Configuration helper
├── Model/
│   ├── ImageFlipService.php    # Core service for flip image resolution
│   └── Config/Source/
│       ├── ImageRoles.php      # Source model for image roles dropdown
│       └── AnimationType.php   # Source model for animation types
├── Plugin/
│   ├── Product/
│   │   ├── ImagePlugin.php     # Plugin for ImageFactory
│   │   └── CollectionPlugin.php # Plugin for product collections
│   └── Block/Product/
│       └── ImagePlugin.php     # Plugin for Image block HTML
├── ViewModel/
│   └── ImageFlip.php           # ViewModel for frontend templates
├── view/frontend/
│   ├── layout/
│   │   └── default.xml         # Layout updates
│   ├── templates/
│   │   ├── product/
│   │   │   └── image_with_flip.phtml
│   │   └── js/
│   │       └── init.phtml      # JS initialization template
│   ├── web/
│   │   ├── css/
│   │   │   └── image-flip.css  # Animation styles
│   │   └── js/
│   │       └── image-flip.js   # Hover handling & lazy loading
│   └── requirejs-config.js
├── composer.json
├── registration.php
└── README.md
```

### Key Classes

#### `ImageFlipService`

The core service that resolves which image to use for the flip effect:

```php
// Get flip image URL for a product
$flipImageUrl = $imageFlipService->getFlipImageUrl($product, $imageId);

// Check if product has a flip image
$hasFlipImage = $imageFlipService->hasFlipImage($product);

// Get complete flip image data
$data = $imageFlipService->getFlipImageData($product, $imageId);
// Returns: ['hasFlipImage' => bool, 'flipImageUrl' => string, 'animationType' => string, 'animationSpeed' => int]
```

**Image Resolution Order:**
1. Try primary role from configuration
2. If not found, try fallback role
3. For `second_image` role, queries the media gallery directly

#### `Config Helper`

Provides access to all module configuration:

```php
$config->isEnabled();                    // bool
$config->getPrimaryRole();               // string
$config->getFallbackRole();              // string
$config->getAnimationType();             // string
$config->getAnimationSpeed();            // int
$config->isEnabledForCategoryPage();     // bool
$config->isEnabledForWidgetProducts();   // bool
$config->isEnabledForSearchResults();    // bool
$config->isEnabledForRelatedProducts();  // bool
$config->isEnabledForCmsBlocks();        // bool
$config->isEnabledForPageBuilder();      // bool
$config->getConfigArray();               // array with all config
```

### Database Queries

For custom image roles, the module queries the EAV tables directly:

```sql
-- Get attribute ID
SELECT attribute_id FROM eav_attribute
WHERE attribute_code = 'your_role' AND entity_type_id = 4;

-- Get image value
SELECT value FROM catalog_product_entity_varchar
WHERE attribute_id = ? AND entity_id = ?;
```

For "Second Gallery Image":

```sql
SELECT mg.value FROM catalog_product_entity_media_gallery mg
JOIN catalog_product_entity_media_gallery_value_to_entity mgvte
    ON mg.value_id = mgvte.value_id
LEFT JOIN catalog_product_entity_media_gallery_value mgv
    ON mg.value_id = mgv.value_id AND mgvte.entity_id = mgv.entity_id
WHERE mgvte.entity_id = ?
    AND mg.media_type = 'image'
    AND COALESCE(mgv.disabled, 0) = 0
ORDER BY COALESCE(mgv.position, 999) ASC
LIMIT 1 OFFSET 1;
```

### CSS Classes

The module adds the following CSS classes:

| Class | Description |
|-------|-------------|
| `.product-image-flip-container` | Wrapper container for the image |
| `.product-image-flip-primary` | Primary (default) image |
| `.product-image-flip-hover` | Hover (flip) image |
| `.flip-animation-{type}` | Animation-specific class |
| `.flip-loaded` | Added when flip image is loaded |

### JavaScript API

```javascript
// Manually initialize flip functionality
window.rollpixImageFlip.init();

// Re-initialize after AJAX content load
$(document).on('contentUpdated', function() {
    window.rollpixImageFlip.init();
});

// Preload all flip images
window.rollpixImageFlip.preload();

// Enable lazy preloading with IntersectionObserver
window.rollpixImageFlip.lazyPreload();
```

### Events

The JavaScript listens for these events to handle dynamic content:

- `contentUpdated` (Magento standard)
- `pagebuilder:renderAfter` (Page Builder)
- `ajaxComplete` (jQuery AJAX)
- `init`, `reInit`, `afterChange` (Slick slider)
- `initialized.owl.carousel`, `refreshed.owl.carousel` (Owl Carousel)
- `swiperInit`, `swiperSlideChangeEnd` (Swiper)
- `amscroll_after_load` (Amasty extensions)
- `catalog_product_list_loaded` (Various extensions)

## Troubleshooting

### Flip image not showing

1. Verify the product has an image assigned to the configured role
2. For "Second Gallery Image", ensure the product has at least 2 images in the gallery
3. Check if the module is enabled for the current location
4. Clear Magento cache: `bin/magento cache:flush`
5. Regenerate static content: `bin/magento setup:static-content:deploy`
6. Verify JavaScript is loading (check browser console for errors)

### Custom role not appearing in dropdown

1. Ensure the attribute has **Catalog Input Type** set to **Media Image**
2. Clear config cache: `bin/magento cache:clean config`
3. Check that the attribute exists in `eav_attribute` table

### Animation not working

1. Check if `prefers-reduced-motion` is enabled in your OS
2. Verify CSS is loading correctly
3. Check for CSS conflicts with your theme (inspect element)

### Third-party module not working

1. Ensure the module uses `Magento\Catalog\Block\Product\ImageFactory` for rendering
2. Check if the module triggers `contentUpdated` event after AJAX loading
3. You may need to manually call `imageFlip.init()` after content loads

### Error after module update

Clear generated files and recompile:

```bash
rm -rf generated/code/* generated/metadata/*
bin/magento setup:di:compile
bin/magento cache:flush
```

## Uninstallation

### Via Composer

```bash
bin/magento module:disable Rollpix_ImageFlipHover
composer remove rollpix/module-image-flip-hover
bin/magento setup:upgrade
bin/magento cache:flush
```

### Manual

```bash
bin/magento module:disable Rollpix_ImageFlipHover
rm -rf app/code/Rollpix/ImageFlipHover
bin/magento setup:upgrade
bin/magento cache:flush
```

## Changelog

### Version 1.1.0
- Added Page Builder Products widget support
- Added CMS blocks location option
- Added touch device support (tap to toggle)
- Improved dynamic content detection
- Added debounced initialization to prevent multiple rapid calls
- Added IntersectionObserver for lazy preloading option
- Added support for Amasty, Mirasvit and other popular extensions
- Added global `window.rollpixImageFlip` API
- Fixed double event binding on dynamic content

### Version 1.0.0
- Initial release
- Support for native and custom image roles
- "Second Gallery Image" option
- 8 animation types
- Location-based enable/disable
- Third-party module compatibility

## License

MIT License

## Author

Rollpix - https://rollpix.com

## Support

For issues and feature requests, please contact support@rollpix.com
