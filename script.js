const filtrosForm = document.getElementById('form-filtros');
const limpiarBtn = document.getElementById('btn-limpiar');
const cardsContainer = document.querySelector('[data-cards]');
const summary = document.querySelector('[data-summary]');
const agregarForm = document.getElementById('form-agregar');
const alerta = document.getElementById('alerta');
const temaInput = filtrosForm?.querySelector('input[name="tema"]');
const temaInicial = temaInput?.value || 'claro';

function crearCard(item) {
    const article = document.createElement('article');
    article.className = 'card';

    const img = document.createElement('img');
    img.src = item.imagen;
    img.alt = item.nombre;

    const body = document.createElement('div');
    body.className = 'card-body';

    const title = document.createElement('h3');
    title.className = 'card-title';
    title.textContent = item.nombre;

    const badge = document.createElement('span');
    badge.className = 'badge';
    badge.textContent = `🏷️ ${item.categoria}`;

    const description = document.createElement('p');
    description.textContent = item.descripcion;

    body.append(title, badge, description);
    article.append(img, body);

    return article.outerHTML;
}

function renderItems(lista) {
    if (!cardsContainer) return;

    cardsContainer.innerHTML = lista.length
        ? lista.map(crearCard).join('')
        : '<p class="small-text">Sin resultados para la búsqueda seleccionada.</p>';
}

function setSummary(total, termino) {
    if (!summary) return;
    summary.textContent = `${total} ítems encontrados${termino ? ' para "' + termino + '"' : ''}`;
}

function paramsFromForm(form) {
    const data = new FormData(form);
    return new URLSearchParams(data.entries());
}

async function cargarItems() {
    if (!filtrosForm) return;

    const params = paramsFromForm(filtrosForm);
    try {
        const response = await fetch('api.php?' + params.toString());
        const data = await response.json();
        renderItems(data.items || []);
        setSummary(data.total || 0, filtrosForm.q.value.trim());
    } catch (err) {
        if (cardsContainer) {
            cardsContainer.innerHTML = '<p class="small-text">No se pudo recuperar la información del API.</p>';
        }
    }
}

filtrosForm?.addEventListener('submit', function (event) {
    event.preventDefault();
    cargarItems();
    const params = paramsFromForm(filtrosForm).toString();
    const nuevaUrl = window.location.pathname + '?' + params;
    window.history.replaceState({}, '', nuevaUrl);
});

limpiarBtn?.addEventListener('click', function () {
    filtrosForm?.reset();
    if (temaInput) {
        temaInput.value = temaInicial;
    }
    cargarItems();
    window.history.replaceState({}, '', `${window.location.pathname}?tema=${encodeURIComponent(temaInicial)}`);
});

agregarForm?.addEventListener('submit', async function (event) {
    event.preventDefault();
    if (!alerta) return;

    alerta.innerHTML = '';

    const payload = Object.fromEntries(new FormData(agregarForm).entries());
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (!data.ok) {
            const errores = data.errors ? Object.values(data.errors).join('. ') : 'No se pudo guardar';
            alerta.innerHTML = `<div class="alert alert-error">${errores}</div>`;
            return;
        }

        alerta.innerHTML = '<div class="alert alert-success">Ítem guardado y agregado al listado.</div>';
        agregarForm.reset();
        cargarItems();
    } catch (err) {
        alerta.innerHTML = '<div class="alert alert-error">Error al comunicar con el servidor.</div>';
    }
});

document.addEventListener('DOMContentLoaded', cargarItems);
