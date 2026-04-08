# Acceptance Criteria — Rollpix_ImageFlipHover v1.2.1

Last updated: 2026-04-07

---

## Funcional

### Flip image basico
- [x] Al hacer hover sobre un producto en el listado de categoria, se muestra una imagen alternativa
- [x] Al quitar el mouse, vuelve a la imagen original
- [x] La animacion configurada se aplica correctamente (fade, slide, zoom, flip)
- [x] La velocidad de animacion es configurable

### Roles de imagen
- [x] Se puede seleccionar cualquier rol de tipo `media_image` como rol principal
- [x] Se puede seleccionar "Segunda Imagen de Galeria" para usar la posicion #2 de la galeria
- [x] Si el producto no tiene imagen con el rol principal, se usa el rol de respaldo
- [x] Los roles custom creados por el usuario aparecen automaticamente en el dropdown

### Productos configurables (v1.2.1)
- [x] El fallback a "Segunda Imagen de Galeria" funciona correctamente en productos configurables
- [x] Si el padre configurable no tiene flip image, se busca en los hijos simples
- [x] La query de galeria no produce duplicados por store view

### Ubicaciones
- [x] Funciona en paginas de categoria
- [x] Funciona en widgets de productos
- [x] Funciona en resultados de busqueda
- [x] Funciona en productos relacionados/upsell/cross-sell
- [x] Funciona en bloques CMS con widgets de productos
- [x] Funciona en Page Builder Products widget
- [x] Se puede habilitar/deshabilitar cada ubicacion independientemente

### Solo Desktop
- [x] Cuando esta habilitado, el flip solo funciona en pantallas > 768px
- [x] En mobile no se aplica el efecto ni se cargan las imagenes flip

### Contenido dinamico
- [x] Se re-inicializa automaticamente para contenido cargado por AJAX
- [x] Funciona con sliders (Slick, Owl Carousel, Swiper)
- [x] Funciona con infinite scroll (Amasty)

---

## Global — Tecnico
- [x] setup:upgrade sin errores
- [x] setup:di:compile sin errores
- [ ] PHPStan level 5 sin errores (no configurado)
- [x] Funciona con cache habilitado (FPC, block, config)

## Global — Documentacion
- [x] README.md completo (EN)
- [x] manual.md completo (ES)
- [x] CHANGELOG.md actualizado
- [x] i18n completo (en_US + es_AR + es_ES)
- [x] CLAUDE.md actualizado

## Global — Compatibilidad
- [x] PHP 7.4 - 8.3
- [x] Magento 2.4.x
- [ ] Solo Luma (Hyva no testeado)
