# 🌍 Mini Travel Guide

**Guía de viajes interactiva con búsqueda inteligente, filtros dinámicos y sugerencias con IA**

---

## 👤 Autoría

- **Autor:** Albert Lukmanov
- **Email:** [albert.lukmanov@davinci.edu.ar](mailto:albert.lukmanov@davinci.edu.ar)
- **GitHub:** [https://github.com/etonealbert](https://github.com/etonealbert)
- **Curso:** ACN2BV - Aplicaciones Web Interactivas
- **Trabajo:** Parcial 1 - Parte 2

---

## 📖 Descripción

**Mini Travel Guide** es una aplicación web interactiva en PHP que permite explorar destinos turísticos de todo el mundo. El sistema incluye:

- ✅ **Catálogo de 10 destinos** pre-cargados con información detallada
- 🔍 **Búsqueda por texto** en nombre, país y descripción
- 🌎 **Filtros** por continente y tipo de viaje (playa, montaña, urbano, aventura, cultural)
- 🎨 **Tema claro/oscuro** dinámico con transiciones suaves
- 🤖 **Asistente inteligente** con procesamiento de lenguaje natural y fallback local
- ✨ **Formulario de sugerencias** con validación frontend y backend
- 📱 **Diseño responsivo** adaptado a todos los dispositivos

---

## 🚀 Cómo ejecutar

### Opción 1: Servidor PHP embebido (recomendado)

```bash
# Desde la raíz del proyecto
php -S localhost:8000
```

Luego abre tu navegador en: **http://localhost:8000/index.php**

### Opción 2: XAMPP/WAMP/MAMP

1. Coloca el proyecto en la carpeta `htdocs` (o equivalente)
2. Inicia Apache
3. Accede a: **http://localhost/parcial-1-p2-acn2bv-albert-lukmanov/index.php**

---

## 🎛️ Parámetros y funcionalidades

### Parámetros GET

| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| `q` | string | Búsqueda por texto en nombre, país o descripción | `?q=rio` |
| `continente` | string | Filtrar por continente específico | `?continente=Sudamérica` |
| `tipo` | string | Filtrar por tipo de viaje | `?tipo=aventura` |
| `tema` | string | Cambiar tema visual (`claro` o `oscuro`) | `?tema=oscuro` |
| `pregunta_ia` | string | Consulta al asistente inteligente | `?pregunta_ia=¿Dónde ir para aventura?` |

**Valores válidos:**
- **Continentes:** `Sudamérica`, `Europa`, `Asia`, `África`, `América del Norte`, `Oceanía`
- **Tipos:** `playa`, `montaña`, `urbano`, `aventura`, `cultural`
- **Tema:** `claro`, `oscuro`

### Parámetros POST (Formulario de Sugerencia)

| Campo | Tipo | Validación | Descripción |
|-------|------|------------|-------------|
| `titulo` | string | required, no vacío | Nombre del destino |
| `pais` | string | required, no vacío | País del destino |
| `continente` | string | required, select | Continente (opciones predefinidas) |
| `tipo` | string | required, select | Tipo de viaje (opciones predefinidas) |
| `descripcion` | string | required, no vacío | Descripción del destino |
| `imagen` | url | required, no vacío | URL de la imagen |

---

## 🧠 Extensión LLM / Asistente Inteligente

El sistema incluye un **asistente de viajes inteligente** que responde preguntas en lenguaje natural usando **OpenAI GPT-4o-mini**.

### Funcionamiento

**Con API de OpenAI configurada (Activo):**
- El sistema usa OpenAI GPT-4o-mini para analizar la pregunta
- La IA recibe el contexto de todos los destinos disponibles
- Recomienda destinos específicos basándose en la consulta
- Muestra sugerencias con el badge "🤖 Sugerencias con IA"
- Si OpenAI falla o no responde, automáticamente usa el fallback

**Fallback automático (si OpenAI no está disponible):**
- El sistema analiza la pregunta buscando palabras clave
- Detecta continentes: "Sudamérica", "Europa", "Asia", "África"
- Detecta tipos: "aventura", "playa", "montaña", "urbano", "cultural"
- Filtra los destinos locales según los criterios detectados
- Muestra sugerencias con un badge indicando "💡 Sugerencias locales"

### Configuración de OpenAI

El proyecto incluye un archivo `.env` (no versionado en Git) con las credenciales:

```env
OPENAI_API_KEY=tu_api_key_aqui
OPENAI_MODEL=gpt-4o-mini
```

**⚠️ Importante:** El archivo `.env` está en `.gitignore` para proteger las credenciales sensibles.

### Ejemplos de preguntas

```
¿Dónde debería ir para turismo de aventura en Sudamérica?
Quiero ir a playas en Asia
Recomiéndame destinos urbanos en Europa
¿Qué lugares culturales hay en África?
```

---

## 🎨 Temas visuales

El sistema soporta dos temas que afectan:
- Color de fondo de página
- Color de texto
- Color de bordes
- Color de tarjetas
- Sombras y efectos

**Cambiar tema:**
- Usando los botones en el header (☀️ Claro / 🌙 Oscuro)
- Por URL: `?tema=claro` o `?tema=oscuro`

---

## 📋 Ejemplos de uso

### Búsqueda básica
```
http://localhost:8000/index.php?q=rio
```

### Filtro por continente
```
http://localhost:8000/index.php?continente=Sudamérica
```

### Búsqueda combinada con tema oscuro
```
http://localhost:8000/index.php?q=playa&continente=Asia&tipo=playa&tema=oscuro
```

### Consulta al asistente IA
```
http://localhost:8000/index.php?pregunta_ia=¿Dónde%20ir%20para%20aventura%20en%20Sudamérica?
```

---

## 🗂️ Estructura del proyecto

```
parcial-1-p2-acn2bv-albert-lukmanov/
│
├── index.php              # Archivo principal con toda la lógica
├── README.md              # Este archivo
│
└── assets/
    ├── css/               # (Opcional) CSS externo
    └── img/               # (Opcional) Imágenes locales
```

---

## 🛡️ Validaciones implementadas

### Frontend (HTML5)
- Atributo `required` en todos los campos del formulario de sugerencia
- Tipo `url` para el campo de imagen
- Validación nativa del navegador

### Backend (PHP)
- Función `empty()` para detectar campos vacíos
- Mensajes de error en rojo debajo de cada campo con problemas
- Escapado con `htmlspecialchars()` para prevenir XSS
- Preservación de datos del formulario en caso de error

---

## 🎯 Funcionalidades destacadas

✨ **Sin parámetros GET:** Muestra todos los destinos  
🔍 **Con búsqueda/filtros:** Muestra solo coincidencias  
🎨 **Tema dinámico:** Cambio instantáneo de apariencia  
📝 **Formulario POST:** Muestra preview de la sugerencia (no persiste)  
🤖 **Asistente IA:** Análisis de lenguaje natural con fallback local  
📱 **Responsivo:** Grid adaptable (1-4 columnas según pantalla)  
♿ **Accesible:** Atributos `alt` en imágenes, `label` en inputs  
🎭 **Iconos temáticos:** Emojis contextuales por tipo de destino  

---

## 🗺️ Destinos incluidos

El sistema pre-carga **10 destinos turísticos**:

1. **Rio de Janeiro** 🇧🇷 - Sudamérica - Playa
2. **Cusco** 🇵🇪 - Sudamérica - Aventura
3. **Patagonia** 🇦🇷🇨🇱 - Sudamérica - Montaña
4. **Cartagena** 🇨🇴 - Sudamérica - Urbano
5. **Kioto** 🇯🇵 - Asia - Cultural
6. **Bali** 🇮🇩 - Asia - Playa
7. **Zermatt** 🇨🇭 - Europa - Montaña
8. **Barcelona** 🇪🇸 - Europa - Urbano
9. **Marrakech** 🇲🇦 - África - Cultural
10. **Serengeti** 🇹🇿 - África - Aventura

---

## 🧪 Pruebas sugeridas

1. **Sin filtros:** `index.php` → Debe mostrar 10 destinos
2. **Búsqueda:** `index.php?q=rio` → Debe mostrar Rio de Janeiro
3. **Filtro continente:** `index.php?continente=Europa` → 2 destinos
4. **Filtro tipo:** `index.php?tipo=aventura` → 2 destinos
5. **Tema oscuro:** `index.php?tema=oscuro` → Fondo oscuro, texto claro
6. **Búsqueda combinada:** `index.php?q=playa&tipo=playa` → Destinos de playa
7. **Formulario vacío:** Enviar sin datos → Errores en rojo
8. **Formulario completo:** Llenar todos los campos → Preview exitoso
9. **Asistente IA:** "¿Dónde ir para aventura en Sudamérica?" → 2 resultados

---

## 📦 Tecnologías utilizadas

- **PHP 7.4+** - Lógica del servidor
- **HTML5** - Estructura semántica
- **CSS3** - Estilos con variables y transiciones
- **JavaScript** - Aplicación de clases de tema

---

## 📜 Licencia

Este proyecto es de **uso académico** para el Parcial 1 P2 de Aplicaciones Web Interactivas.

---

## 🎓 Créditos de imágenes

Las imágenes utilizadas provienen de [Unsplash](https://unsplash.com), plataforma de fotografías libres de derechos de autor.

---

## 🔗 Repositorio GitHub

**URL:** [https://github.com/etonealbert/parcial-1-p2-acn2bv-albert-lukmanov](https://github.com/etonealbert/parcial-1-p2-acn2bv-albert-lukmanov)

**Usuario colaborador:** sergiomedinaio

---

## 💬 Notas del desarrollo

- **Commits:** Se utilizó Conventional Commits (`feat:`, `fix:`, `docs:`, etc.)
- **Código:** Todos los comentarios en español según consigna
- **CSS:** Implementado con variables CSS para temas dinámicos
- **UX:** Transiciones suaves y feedback visual en todas las interacciones
- **Validación:** Doble validación frontend/backend para seguridad

---

**Desarrollado con ❤️ por Albert Lukmanov - Octubre 2025**
