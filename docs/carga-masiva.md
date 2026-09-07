# Carga masiva de productos

> Cómo funciona hoy la importación desde Google Sheets, por qué se queda corta y
> qué caminos hay para hacerla más simple y robusta.
>
> Sistema: Laravel 12 + Filament 3.3 · Página: `/admin/product-sync` ·
> Código: `app/Filament/Pages/ProductSync.php` · Actualizado: 2026-09-07

---

## En una línea

Hoy funciona, pero es frágil y confuso.

- **Gana rápida:** aceptar el archivo `.csv`/`.xlsx` subido directamente en vez de una URL pública.
- **Meta:** migrar a la **acción de importación nativa de Filament** (`ImportAction` +
  `Importer`): subida + mapeo de columnas + cola + reporte de errores. Reemplaza casi
  todo el código a medida.

---

## 1. Cómo funciona hoy

Dos procesos separados en la página `ProductSync`: **datos** e **imágenes**.

### Flujo de datos (Google Sheets → catálogo)

1. Preparas la planilla en Google Sheets: una fila de encabezados, una fila por
   combinación producto + modelo compatible.
2. Publicas la hoja como CSV: `Archivo → Compartir → Publicar en la web → Valores
   separados por comas (.csv)`. Copias la URL (termina en `/pub?output=csv` o
   `/export?format=csv`).
3. Pegas la URL en el botón «Sincronizar desde Excel/CSV» y confirmas.
4. El servidor descarga el CSV con `Http::get()`, lo parsea y por cada `SKU` crea o
   actualiza el `Product`; crea si faltan la `Category`, la `Brand` del repuesto y las
   `Brand`/`CarModel` de los autos compatibles (buscando por *slug* para no duplicar).
5. Vincula los modelos compatibles en la tabla pivote y arma una descripción extendida
   con origen, condición, garantía y cilindradas.

### Flujo de imágenes (aparte)

1. Arrastras las fotos a la página. El nombre del archivo debe ser el SKU:
   `FRENOS-001.jpg` para la principal; `FRENOS-001_1.jpg`, `FRENOS-001_2.jpg`… para la galería.
2. El servidor las convierte a WebP (máx. 1200 px, calidad 80) con PHP GD y las asigna
   al producto cuyo SKU coincide con el nombre del archivo.

### Formato de columnas esperado

```
Item, SKU, Producto, Descripción, Categoría, Marca del Repuesto,
Marca Compatible, Modelo Compatible, Cilindrada, Años Compatibles,
Precio Venta, Precio Oferta, Stock Actual, Stock Mínimo,
Origen, Condición, Garantía
```

Los encabezados se normalizan solos (minúsculas, sin tildes, espacios → `_`) y se
detectan por **coincidencia parcial**, así que sirven variantes: `PRECIO`, `Precio
Venta`, `Valor`, `PVP` → precio de venta; `Precio Oferta`, `Mayorista` → precio
mayorista; `Stock`, `Existencia`, `Cantidad` → stock; etc. La única columna
**obligatoria es `SKU`**; una fila sin SKU se ignora. Si no se detecta ninguna columna
de precio, el importador lo avisa y lista los encabezados que encontró.

### Vehículo compatible dentro del nombre

Si la planilla **no** tiene columnas `Marca Compatible` / `Modelo Compatible`, el
importador las extrae del texto del nombre con el patrón
`… COMPATIBLE CON <MARCA> Y <MODELO>`:

- `BUJIAS PUNTA IRIDIUM COMPATIBLE CON CHERY Y TIGGO 2` → marca `CHERY`, modelo `TIGGO 2`.
- Marcas de dos palabras funcionan: `GREAT WALL Y HAVAL H3`, `GAC GONOW Y WAY 1.3CC`
  (corta en el **primer** ` Y `).
- En este caso el nombre del producto se respeta **tal cual** viene en la planilla
  (ya incluye el vehículo); no se le agrega nada.
- Si hay columnas dedicadas, se usan esas y el vehículo se **agrega** al nombre
  (`Bujías Jgo — Chery Tiggo 2`).

### Datos que se repiten por SKU

Estas planillas suelen llenar los datos del producto (nombre, precio, categoría, stock…)
**sólo en la primera fila de cada SKU** y dejar en blanco las filas de compatibilidad
siguientes. El importador toma, para cada campo, el **primer valor no vacío** que aparezca
en cualquier fila de ese SKU. Si un producto queda en `$0` es porque *ninguna* fila de su
SKU tenía la columna de precio con valor.

### Un producto por fila (SKU repetible)

**Cada fila del CSV es un producto propio**, aunque el SKU se repita. Dos filas con el
mismo SKU pero distinto vehículo compatible → dos productos separados en el catálogo:
`Bujías Jgo — Chery Tiggo 2` y `Bujías Jgo — Chery IQ`. El vehículo se agrega al nombre
para diferenciar las fichas.

- **Clave de identidad (`import_key`):** `sku + marca compatible + modelo compatible`
  (normalizado). Es lo que usa el sincronizador para saber si una fila ya existe y
  actualizarla en vez de duplicarla.
- Si dos filas comparten esa clave (mismo SKU, mismo vehículo, sólo cambia el rango de
  años o la cilindrada), se tratan como **un** producto y la última fila gana.
- Los productos creados a mano en el panel tienen `import_key` vacío y **el importador
  nunca los toca**.
- **Primera sincronización tras este cambio:** un producto heredado (creado con el
  esquema viejo, 1 por SKU) es *adoptado* por la primera fila de ese SKU en vez de
  quedar huérfano. Conviene revisar el catálogo después de la primera corrida.

> **Stock:** con este modelo, un SKU con stock 6 repartido en 5 filas se ve como 5
> productos de stock 6 (30 unidades aparentes) y se venden por separado. Cada
> re-sincronización vuelve a poner el valor de la planilla. Para control de inventario
> real hay que implementar *stock compartido por SKU* (pendiente, ver §7 F4).

> **Detalle importante:** el enlace tiene que ser el **CSV publicado**. Un enlace de
> *carpeta* de Google Drive o el enlace normal de la hoja devuelven HTML, no datos →
> resultado «0 productos». El sistema ahora avisa explícitamente cuando detecta esto.

---

## 2. Qué se queda corto

- **Corre dentro del request web.** Un catálogo grande puede superar tiempo/memoria de
  PHP en cPanel y cortar a la mitad (esto causaba el error 500). Se mitigó subiendo
  límites, pero no es la solución de fondo.
- **Obliga a «publicar en la web».** Paso confuso; deja la planilla accesible
  públicamente para cualquiera con la URL.
- **Sin previsualización.** No puedes ver «voy a crear 12, actualizar 340, 5 con error»
  antes de aplicar.
- **Errores silenciosos.** Las filas mal formadas se saltan sin decir cuáles ni por qué.
- **No borra ni desactiva.** Un producto que sacas de la planilla queda activo para siempre.
- **Una sola pestaña.** Productos repartidos en varias hojas → consolidar a mano.
- **Stock por fila, no por SKU.** Ver el recuadro de la §1: el inventario se infla cuando
  un SKU aparece en varias filas.
- **Precios como texto.** Se limpian con regex (`$40,000` → `40000`); formatos raros se
  pueden interpretar mal.
- **Imágenes = paso manual separado**, depende de nombrar cada archivo con el SKU exacto.

---

## 3. Lo más fácil ahora mismo

### A · URL de exportación directa — cero desarrollo

En vez de «Publicar en la web», comparte la planilla como *«Cualquier persona con el
enlace: Lector»* y usa la URL de exportación:

```
https://docs.google.com/spreadsheets/d/<ID_DE_LA_HOJA>/export?format=csv&gid=<ID_PESTAÑA>
```

El `ID_DE_LA_HOJA` está en la URL normal de la planilla; el `gid` es el número tras
`#gid=` al abrir cada pestaña. Misma mecánica de hoy, sin el paso de «publicar».
Funciona con el código actual.

### B · Subir el archivo directamente — desarrollo mínimo

Cambiar el campo «URL» por un **selector de archivo**:

1. En Google Sheets: `Archivo → Descargar → Valores separados por comas (.csv)` — o `.xlsx`.
2. En el panel: arrastras ese archivo y listo. Sin URL, sin publicar, sin exponer la planilla.

Cambio chico sobre el código actual (Filament ya trae el componente de subida; sólo hay
que leer el archivo local en vez de descargarlo por HTTP). **Esta es la «forma más
fácil»** para el día a día.

> Si vas a tocar el código para la opción B, conviene hacerlo directamente con la
> **Opción 1** (abajo): mismo esfuerzo de integración, y te da mapeo de columnas, cola y
> reporte de errores «gratis».

---

## 4. Alternativas para mejorar

De menos a más ambicioso. No son excluyentes (p. ej. 1 + 4 cubren carga manual y automática).

### Opción 1 · Importador nativo de Filament — **Recomendada**

`esfuerzo: medio · ~2–4 días · requiere cola`

Filament 3 trae `ImportAction` + clases `Importer`: subís un CSV, el panel muestra una
pantalla para **mapear cada columna** a un campo del producto, valida fila por fila,
procesa **en segundo plano por lotes** con barra de progreso, y entrega un **CSV con las
filas que fallaron** y el motivo. Trae sus tablas (`imports`, `failed_import_rows`).

La lógica actual (agrupar por SKU, crear Brand/Category/CarModel, pivote, descripción
extendida) se traslada casi tal cual a `resolveRecord()` / `afterSave()` del `Importer`.

- **A favor:** reemplaza parser + cola + errores a medida por código de Filament ·
  mapeo de columnas (la planilla puede cambiar de orden) · reporte descargable ·
  progreso real.
- **En contra:** exige worker de cola (cron cPanel: `queue:work --stop-when-empty` cada
  minuto) · sigue siendo subida manual · no maneja imágenes.

### Opción 2 · Subida de archivo con Laravel Excel — alternativa a la 1

`esfuerzo: medio · ~2–4 días · paquete: maatwebsite/excel`

Similar a la 1 pero con `maatwebsite/excel`. Lee `.xlsx` nativo (sin exportar a CSV),
lectura por *chunks*, colas, validación, colección de errores. Más control de parseo,
menos UI lista para usar.

- **A favor:** `.xlsx` directo con varias hojas · control total del mapeo · muy probado.
- **En contra:** la UI de progreso/errores hay que armarla · dependencia pesada
  (PhpSpreadsheet) · mismo requisito de cola.

### Opción 3 · Google Sheets API con cuenta de servicio

`esfuerzo: medio-alto · ~3–5 días · paquete: google/apiclient`

Cuenta de servicio en Google Cloud; se comparte la planilla con su email. El panel lee
la hoja directo por API: el admin sólo aprieta **«Sincronizar»**, sin publicar, con la
planilla **privada**. Permite leer pestañas y rangos específicos, y se puede
**programar** (cron cada X horas) para actualización automática.

- **A favor:** mejor experiencia (un botón, o automático) · la planilla nunca se expone ·
  sigue siendo la fuente de verdad.
- **En contra:** setup en Google Cloud (credenciales JSON, cuota) · sin mapeo ni reporte
  de errores salvo que se programe · hay que proteger el archivo de credenciales en el servidor.

### Opción 4 · Comando programado + cola (complemento)

`esfuerzo: bajo-medio · ~1–2 días · sobre 1, 2 o 3`

Extraer la importación a un Job en cola y a un comando Artisan
(`php artisan productos:importar`). El botón encola el job; un cron lo puede correr solo
desde un archivo dejado por SFTP o desde la Sheets API. Resuelve de raíz el
timeout/memoria y habilita la automatización.

- **A favor:** elimina el riesgo de 500 por tiempo/memoria · reutilizable (panel, cron,
  CLI) · base para automatización futura.
- **En contra:** requiere worker de cola estable (no hay Supervisor en cPanel; se hace
  con cron) · es infraestructura, no una solución completa por sí sola.

### Opción 5 · Endpoint de API / integración externa

`esfuerzo: medio · ~2–3 días · para datos fuera de Sheets`

Endpoint autenticado (`POST /api/admin/productos/bulk`) para que un sistema externo —
ERP, o Make / Zapier / n8n — empuje los productos. Útil si el catálogo deja de vivir en
una planilla.

- **A favor:** desacopla el origen de los datos del panel · sincronización en tiempo real.
- **En contra:** exagerado si la planilla seguirá siendo el origen · superficie de
  seguridad nueva (token, rate limit, validación).

---

## 5. Comparación

| Enfoque | Cómo se carga | Esfuerzo dev | Async / sin 500 | Reporte errores | Planilla privada | Auto-programable |
|---|---|---|---|---|---|---|
| Actual (URL CSV) | Publicar en la web + pegar URL | — | No | No | No | No |
| URL export directa | Compartir por enlace + URL `/export` | Cero | No | No | Parcial | No |
| Filament ImportAction | Subir archivo `.csv` | Medio | Sí | Sí (CSV) | No | No |
| Laravel Excel | Subir `.xlsx`/`.csv` | Medio | Sí | Hay que armarlo | No | No |
| Google Sheets API | Botón «Sincronizar» | Medio-alto | Sí (con cola) | Si se programa | Sí | Sí |
| Comando + cola | Botón / cron / terminal | Bajo-medio | Sí | Según base | Según base | Sí |
| Endpoint API | Push desde sistema externo | Medio | Sí | Según cliente | N/A | Sí |

*«Async / sin 500» = la importación no corre dentro del request del navegador.*

---

## 6. Imágenes

Ninguna opción de datos resuelve las fotos. De más simple a más automática:

- **Mantener el subidor actual** (arrastrar archivos nombrados por SKU → WebP). Es el paso manual.
- **Columna `imagen_url` en la planilla** con un enlace público a cada foto (bucket,
  hosting propio, Cloudinary…). El importador la descarga, convierte a WebP y asigna en
  la misma pasada. Los enlaces de *Google Drive* no sirven como imagen directa.
- **Galería:** varias URLs separadas por `|` en una columna `galeria`.
- **Mover el procesamiento a la cola** (redimensionar + WebP es caro en CPU); hoy corre
  síncrono y es otra fuente potencial de timeout.

> **Nota técnica:** el motor de imágenes es PHP GD nativo (se quitó Intervention Image
> por compatibilidad con cPanel). Si el hosting permitiera `imagick`, la conversión
> sería más rápida y de mejor calidad.

---

## 7. Recomendación y plan

**Recomendación:** Filament `ImportAction` (Opción 1) + comando/cola (Opción 4). Si más
adelante se quiere «que se actualice solo», sumar Sheets API (Opción 3). Para fotos:
columna `imagen_url` procesada en la cola.

| Fase | Qué | Detalle |
|---|---|---|
| **F1** | Estabilizar (hecho) | Fix del 500: captura de `\Throwable`, normalización de filas, límites de tiempo/memoria, mensajes claros ante enlaces inválidos. Verificar que exista el worker de cola. |
| **F2** | Subida directa de archivo | Opción 1: `Importer` de Filament con la lógica actual portada, mapeo de columnas, cola y CSV de errores. Se deja de depender de «publicar en la web». |
| **F3** | Imágenes por URL + cola | Columna `imagen_url` / `galeria`; descarga y conversión a WebP dentro del mismo import, en segundo plano. |
| **F4** | Stock compartido, bajas y automatización | Stock por SKU (no por fila): al vender, descontar de todas las filas del mismo SKU, o llevar el stock en una tabla aparte por SKU. Decidir qué pasa con productos que salen de la planilla (desactivar vs. sin stock). Opcional: Sheets API + cron para sincronización automática. |

---

## Anexo · Cambios aplicados (2026-09-07)

### Fix del error 500

- `catch (\Throwable)` en vez de `catch (\Exception)` — evita que `ValueError`/`Error`
  se propaguen como HTTP 500 en `/livewire/update`.
- Normalización de cada fila al ancho exacto del encabezado antes de `array_combine`
  (Google Sheets exporta columnas vacías finales → antes lanzaba `ValueError`).
- `preg_split('/\r\n|\r|\n/')` para todos los saltos de línea.
- `set_time_limit(0)` + `memory_limit` 512M + `Http::timeout(120)`.
- Fallback si `iconv//TRANSLIT` no está disponible.
- `default()` del campo `csv_url` blindado con `Schema::hasTable` + `try/catch`.
- Detección de respuesta HTML (carpeta de Drive / link normal de la hoja) y de
  encabezado sin columna `SKU`, con mensaje explicativo.
- Aviso (no «Éxito») cuando se procesan 0 productos.

### Un producto por fila (SKU repetible)

- Migración `2026_09_07_120000_products_allow_duplicate_sku`: quita el `UNIQUE` de
  `products.sku`, agrega `products.import_key` (indexado) e índice normal en `sku`.
- `ProductSync::runSync()` reescrito: agrupa por `import_key` (`sku|marca|modelo`), no por
  SKU; crea/actualiza un producto por fila; slug único por fila; agrega el vehículo al
  nombre; adopta productos heredados en la primera corrida; reporta
  «Creados / Actualizados / Total».
- `ProductSync::processImages()`: la foto de un SKU se asigna a **todos** los productos
  con ese SKU.
- `ProductResource`: se quita la validación `unique` del campo SKU.

### Detección tolerante de columnas + precio

- Los encabezados se detectan por coincidencia parcial (`PRECIO`, `Valor`, `PVP`, …),
  no por nombre exacto. Antes sólo funcionaba `Precio Venta` / `Precio Oferta`.
- Datos base por SKU: primer valor no vacío entre todas las filas del SKU (nombre,
  precio, categoría, stock, marca, condición, garantía, origen).
- Si falta la columna de precio, o si quedan productos en `$0`, el importador avisa.
- Si no hay precio mayorista propio, se usa el precio de venta.
- Pendiente: stock compartido por SKU (§7 F4).
