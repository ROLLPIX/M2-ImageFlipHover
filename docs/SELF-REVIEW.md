# Self-Review — Rollpix_ImageFlipHover v1.2.1

**Builder:** [PENDIENTE — completar por el builder]
**Date:** [PENDIENTE]
**Branch:** main

---

## 1. Verificacion de build
- [ ] `bin/magento setup:upgrade` — sin errores
- [ ] `bin/magento setup:di:compile` — sin errores
- [ ] `bin/magento setup:static-content:deploy` — sin errores (tiene frontend)
- [ ] `bin/magento module:status | grep Rollpix_ImageFlipHover` — aparece enabled
- [ ] `vendor/bin/phpstan analyse` — no hay phpstan.neon configurado

## 2. Verificacion funcional

### Productos simples
- [ ] Flip image con rol custom (Image On Hover) — **Testeado:**
- [ ] Flip image con segunda imagen de galeria — **Testeado:**
- [ ] Animacion fade — **Testeado:**

### Productos configurables
- [ ] Flip image con rol custom asignado en padre — **Testeado:**
- [ ] Fallback a segunda imagen del padre (fix store_id v1.2.1) — **Testeado:**
- [ ] Fallback a hijos simples (nuevo v1.2.1) — **Testeado:**

### Ubicaciones
- [ ] Paginas de categoria — **Testeado:**
- [ ] Widgets de productos — **Testeado:**
- [ ] Resultados de busqueda — **Testeado:**
- [ ] Productos relacionados — **Testeado:**
- [ ] Bloques CMS — **Testeado:**
- [ ] Page Builder — **Testeado:**

### Configuracion admin
- [ ] Rol principal configurable — **Testeado:**
- [ ] Rol de respaldo configurable — **Testeado:**
- [ ] Solo Desktop — **Testeado:**

## 3. Verificacion de cache
- [ ] Testeado con todos los caches habilitados
- [ ] FPC: las paginas renderizan correctamente despues del warm-up de cache
- [ ] Config cache: los cambios en el admin toman efecto despues de cache:flush

## 4. Verificacion de documentacion
_Estos items fueron verificados por Claude durante el review automatizado:_
- [x] README.md esta actualizado
- [x] manual.md creado
- [x] CHANGELOG.md tiene entrada para esta version
- [x] Los archivos i18n tienen todos los strings
- [x] CLAUDE.md esta actualizado con los ultimos cambios

## 5. Revision rapida de calidad de codigo
_Estos items fueron verificados por Claude con grep/analisis estatico:_
- [x] No quedaron var_dump/print_r/die en el codigo
- [x] No hay TODO/FIXME sin resolucion
- [x] No se usa ObjectManager
- [x] Los templates usan funciones de escape para toda la salida dinamica

## 6. Lo que NO testee (para el reviewer)
- [COMPLETAR por el builder: listar lo que no se pudo testear]

## 7. Sign-off del builder

Confirmo que verifique personalmente todos los items marcados arriba.
El modulo esta listo para peer review.

**Firmado:** [PENDIENTE — el builder firma despues de testear]
