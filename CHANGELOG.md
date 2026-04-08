# Changelog

Todos los cambios notables de este módulo se documentan en este archivo.

## [1.3.2] - 2026-04-08

### Changed
- Sección "Información del Módulo" movida al final de la página de configuración, con nombre completo, versión y link a GitHub.

## [1.3.1] - 2026-04-08

### Fixed
- Fix rol primario con valor residual: si el atributo `image_on_hover` tiene un valor viejo que apunta a un archivo inexistente, `getImageUrlByRole()` ahora usa `buildImageUrl()` con detección de placeholder, permitiendo que el fallback `second_image` entre en juego.

## [1.3.0] - 2026-04-08

### Fixed
- Fix query de galería para setups multi-tienda: filtrar por `entity_id` y `store_id` en los JOINs a `catalog_product_entity_media_gallery_value` para evitar filas duplicadas que rompían el `LIMIT/OFFSET` de la segunda imagen.
- Fix para tiendas que cargan imágenes solo a nivel store scope (no default): doble LEFT JOIN (store-specific + default) con `COALESCE` para posición y disabled.
- Fix detección de placeholder: si `imageHelper` devuelve una URL de placeholder, retornar null en vez de usarla como flip image.
- Fix duplicado con imagen base: no mostrar flip image si es la misma URL que la imagen principal del producto.
- Fix query varchar en `getImageFromGalleryByRole()`: agregar filtro `store_id` y preferir valor store-specific sobre default.

### Added
- Bloque de información del módulo en admin con versión (Stores > Configuration > ROLLPIX > Image Flip Hover).

## [1.2.1] - 2025-12-15

### Fixed
- Fix flip image fallback for configurable products.

## [1.2.0] - 2025-12-01

### Added
- Desktop-only option and fix mobile click issue.

## [1.1.1] - 2025-11-15

### Fixed
- Keep module name untranslated in admin menu.

## [1.1.0] - 2025-11-01

### Added
- Initial multi-store support.
- Multiple animation types.
- Location-specific enable/disable.

## [1.0.0] - 2025-10-01

### Added
- Initial release.
- Product image flip on hover for category pages.
- Support for custom image roles and second gallery image.
- Page Builder, CMS blocks, and widget support.
