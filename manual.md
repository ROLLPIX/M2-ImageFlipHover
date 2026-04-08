# Manual de Usuario — Rollpix Image Flip Hover

## Que hace este modulo?

Muestra una imagen alternativa del producto al pasar el mouse sobre la imagen principal en listados de categoria, widgets, sliders, bloques CMS y Page Builder. Permite configurar que imagen mostrar (un rol especifico o la segunda imagen de la galeria) y elegir entre distintos tipos de animacion.

## Instalacion

### Requisitos previos
- Magento 2.4.x
- PHP 7.4 o superior

### Pasos de instalacion

1. Conectarse por SSH al servidor
2. Ir al directorio raiz de Magento
3. Ejecutar:

```bash
composer require rollpix/module-image-flip-hover
bin/magento module:enable Rollpix_ImageFlipHover
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

4. Verificar que el modulo esta activo:

```bash
bin/magento module:status | grep Rollpix_ImageFlipHover
```

## Configuracion

Ir a **Tiendas > Configuracion > ROLLPIX > Image Flip Hover**.

### Configuracion General

| Campo | Tipo | Descripcion | Valor por defecto |
|-------|------|-------------|-------------------|
| Habilitar Cambio de Imagen | Si/No | Activa o desactiva toda la funcionalidad del modulo | Si |
| Rol de Imagen Principal | Dropdown | Rol de imagen a mostrar en hover. Puede ser cualquier rol nativo, custom, o "Segunda Imagen de Galeria" | Segunda Imagen de Galeria |
| Rol de Imagen de Respaldo | Dropdown | Se usa si el producto no tiene imagen con el rol principal | Segunda Imagen de Galeria |
| Tipo de Animacion | Dropdown | Tipo de transicion visual al hacer hover | Desvanecimiento (Fade) |
| Velocidad de Animacion (ms) | Numero | Duracion de la animacion en milisegundos | 300 |
| Solo Desktop | Si/No | Si esta habilitado, el efecto solo funciona en pantallas mayores a 768px | Si |

### Ubicaciones Habilitadas

| Campo | Descripcion | Valor por defecto |
|-------|-------------|-------------------|
| Paginas de Categoria | Listados de productos en categorias | Si |
| Widgets de Productos | Sliders y grids de widgets de productos | Si |
| Resultados de Busqueda | Pagina de resultados de busqueda | Si |
| Productos Relacionados/Upsell/Cross-sell | Bloques de productos relacionados | Si |
| Bloques CMS | Widgets de productos dentro de bloques/paginas CMS | Si |
| Page Builder | Widget de Productos de Page Builder | Si |

## Uso

### Caso 1: Usar la segunda imagen de la galeria (mas simple)

Esta es la forma mas facil de configurar el modulo. No requiere asignar roles especiales a las imagenes.

1. Ir a **Tiendas > Configuracion > ROLLPIX > Image Flip Hover**
2. En **Rol de Imagen Principal** seleccionar **"Segunda Imagen de Galeria (Posicion #2)"**
3. Guardar y limpiar cache
4. Asegurarse de que los productos tengan al menos 2 imagenes en su galeria

Al hacer hover sobre un producto en la categoria, se mostrara la segunda imagen de la galeria.

### Caso 2: Usar un rol de imagen custom (mas control)

Permite elegir exactamente que imagen mostrar en hover para cada producto.

**Crear el atributo:**

1. Ir a **Tiendas > Atributos > Producto**
2. Click en **Agregar Nuevo Atributo**
3. Configurar:
   - Etiqueta por defecto: `Image On Hover`
   - Tipo de entrada de catalogo: **Media Image**
   - Codigo: `image_on_hover`
4. Guardar el atributo
5. Agregarlo al attribute set en **Tiendas > Atributos > Conjunto de Atributos**

**Configurar el modulo:**

1. Ir a **Tiendas > Configuracion > ROLLPIX > Image Flip Hover**
2. En **Rol de Imagen Principal** seleccionar **"Image On Hover"** (el nuevo atributo)
3. En **Rol de Imagen de Respaldo** seleccionar **"Segunda Imagen de Galeria"** (para productos sin el rol asignado)
4. Guardar y limpiar cache

**Asignar la imagen en cada producto:**

1. Editar el producto en el admin
2. Ir a **Imagenes y Videos**
3. Click en la imagen que se quiere mostrar en hover
4. En el panel de detalle, seleccionar el rol **"Image On Hover"**
5. Guardar el producto

### Caso 3: Productos configurables

El modulo soporta productos configurables:

- **Si el padre tiene imagenes:** usa la galeria del padre para el flip
- **Si el padre no tiene flip image:** busca automaticamente en los productos simples hijos
- **Con rol custom:** asignar el rol en las imagenes del producto padre configurable

**Importante:** si usas "Segunda Imagen de Galeria", asegurate de que el producto configurable padre tenga al menos 2 imagenes propias en su galeria.

## Tipos de animacion

| Animacion | Descripcion |
|-----------|-------------|
| Desvanecimiento (Fade) | Crossfade suave entre imagenes |
| Deslizar Izquierda | La imagen se desliza de derecha a izquierda |
| Deslizar Derecha | La imagen se desliza de izquierda a derecha |
| Deslizar Arriba | La imagen se desliza de abajo hacia arriba |
| Deslizar Abajo | La imagen se desliza de arriba hacia abajo |
| Zoom | La imagen principal hace zoom in mientras aparece la flip |
| Voltear Horizontal | Efecto 3D de giro en el eje Y |
| Voltear Vertical | Efecto 3D de giro en el eje X |

## Preguntas frecuentes (FAQ)

### No se ve la imagen de hover en un producto

1. Verificar que el producto tenga una imagen asignada al rol configurado
2. Si se usa "Segunda Imagen de Galeria", verificar que el producto tenga al menos 2 imagenes
3. Verificar que la ubicacion este habilitada (Paginas de Categoria, Widgets, etc.)
4. Limpiar cache: `bin/magento cache:flush`

### No aparece el rol custom en el dropdown de configuracion

1. Verificar que el atributo tenga **Tipo de entrada de catalogo** = **Media Image**
2. Limpiar cache de configuracion: `bin/magento cache:clean config`

### El flip no funciona en mobile

Si **Solo Desktop** esta habilitado (por defecto: Si), el efecto solo funciona en pantallas mayores a 768px. Para habilitarlo en mobile, cambiar a "No".

### El flip funciona en simples pero no en configurables

Verificar que el producto configurable padre tenga imagenes propias en su galeria. Si solo los hijos simples tienen imagenes, el modulo intentara buscar en los hijos como ultimo recurso.

### El modulo es compatible con Hyva Theme?

No testeado con Hyva. El modulo esta disenado para temas basados en Luma/Blank que usan RequireJS y jQuery.

## Solucion de problemas

### Problema: Despues de actualizar el modulo, el flip deja de funcionar
**Solucion:**
```bash
rm -rf generated/code/* generated/metadata/*
bin/magento setup:di:compile
bin/magento cache:flush
```

### Problema: Se muestra la imagen incorrecta en el hover
**Solucion:** Verificar la configuracion del rol principal y de respaldo. Si se usa "Segunda Imagen de Galeria", la imagen que se muestra es la segunda en orden de posicion de la galeria del producto. Se puede cambiar el orden arrastrando las imagenes en el admin.

## Desinstalacion

```bash
bin/magento module:disable Rollpix_ImageFlipHover
composer remove rollpix/module-image-flip-hover
bin/magento setup:upgrade
bin/magento cache:flush
```

## Contacto y soporte

Para soporte, contactar al equipo de Rollpix o crear un issue en el [repositorio de GitHub](https://github.com/ROLLPIX/M2-ImageFlipHover/issues).
