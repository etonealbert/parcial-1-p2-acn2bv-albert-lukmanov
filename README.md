# 🌍 Catálogo dinámico - Parcial 2

Aplicación web en **PHP + HTML + CSS + JavaScript** que gestiona un catálogo de destinos turísticos. Cumple los requisitos mínimos del Parcial 2: carga desde JSON externo, filtros/búsqueda, tema claro/oscuro, tarjetas dinámicas y un endpoint API con validación y persistencia básica.

---

## 📂 Estructura
- `index.php`: Página principal con tarjetas, filtros por búsqueda/categoría, selector de tema y formulario para agregar ítems (AJAX).
- `api.php`: API JSON que lista ítems con filtros `q` y `categoria`, y recibe altas por `POST` con validación backend.
- `data/items.json`: Fuente de datos externa con 10 ítems iniciales.
- `style.css`: Estilos con variables para tema claro/oscuro y diseño responsivo de tarjetas/formularios.
- `script.js`: JavaScript que gestiona filtros, peticiones AJAX y el refresco dinámico de tarjetas/alertas.

---

## 🚀 Ejecución local
1. Ubica el proyecto en tu servidor PHP (embebido o Apache/XAMPP).
2. Desde la raíz, ejecuta:
   ```bash
   php -S localhost:8000
   ```
3. Abre `http://localhost:8000/index.php` en el navegador.

---

## 🎛️ Funcionalidades clave
- **Tarjetas dinámicas**: Renderizadas desde `data/items.json` con imagen, nombre, categoría y descripción.
- **Búsqueda y filtro**: Por nombre (`q`) y categoría (`categoria`) vía formulario GET o recarga dinámica con `fetch` a `api.php`.
- **Tema claro/oscuro**: Selección por URL (`?tema=claro|oscuro`) o botones en la UI; afecta fondo, texto y bordes.
- **API JSON**: `GET /api.php` devuelve ítems filtrados; `POST /api.php` valida y persiste nuevas entradas en `items.json`.
- **Validación**: HTML5 `required`/`minlength` en el frontend; sanitización y reglas básicas en el backend antes de guardar.
- **Actualización sin recarga**: Tras agregar un ítem válido, el listado se refresca automáticamente.

---

## ✅ Cobertura de requisitos
- **Tecnologías**: PHP, HTML, CSS y JavaScript en todos los módulos principales.
- **Datos**: Fuente externa `data/items.json` con más de 8 ítems.
- **Archivos mínimos**: Incluye `index.php`, `api.php` y `style.css`.
- **UI**: Tarjetas dinámicas que muestran todos los ítems cuando no hay filtros.
- **Filtros**: Búsqueda por nombre y filtro por categoría (GET y AJAX).
- **Tema**: Soporte `?tema=claro` y `?tema=oscuro` que modifica fondo, texto y bordes.
- **Extras**: Formulario `POST` para altas, validación frontend/backend, persistencia en JSON y recarga dinámica del listado.

---

## 🧪 Pruebas rápidas
- Linter PHP:
  ```bash
  php -l index.php
  php -l api.php
  ```

