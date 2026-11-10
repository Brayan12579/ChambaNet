let leafletPromise;
const leaflet = () => {
    leafletPromise ||= Promise.all([
        new Promise((resolve, reject) => {
            const style = document.createElement('link');
            style.rel = 'stylesheet';
            style.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            style.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=';
            style.crossOrigin = 'anonymous';
            const timer = setTimeout(() => reject(new Error('Map stylesheet unavailable')), 12000);
            style.onload = () => { clearTimeout(timer); resolve(); };
            style.onerror = () => { clearTimeout(timer); reject(new Error('Map stylesheet unavailable')); };
            document.head.append(style);
        }),
        new Promise((resolve, reject) => {
            if (window.L) return resolve(window.L);
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
            script.crossOrigin = 'anonymous';
            const timer = setTimeout(() => reject(new Error('Map library unavailable')), 12000);
            script.onload = () => { clearTimeout(timer); resolve(window.L); };
            script.onerror = () => { clearTimeout(timer); reject(new Error('Map library unavailable')); };
            document.head.append(script);
        }),
    ]).then(([, L]) => L);
    return leafletPromise;
};
const maps = new WeakMap();
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

async function postJson(url, data) {
    const response = await fetch(url, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: JSON.stringify(data),
    });
    const json = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(json.message || 'No se pudo completar la búsqueda. Inténtalo más tarde.');
    return json;
}

function validCoordinates(latitude, longitude) {
    return Number.isFinite(latitude) && Number.isFinite(longitude)
        && latitude >= -19 && latitude <= 1 && longitude >= -84 && longitude <= -68;
}

function makeMap(L, element) {
    const map = L.map(element, { scrollWheelZoom: false }).setView([-9.2, -75], 5);
    const tiles = L.tileLayer(element.dataset.tileUrl, {
        maxZoom: 19, attribution: element.dataset.tileAttribution,
    }).addTo(map);
    tiles.on('tileerror', () => {
        const status = element.closest('.job-map-block')?.querySelector('[data-map-status]');
        if (status) status.textContent = 'No se pudo cargar parte del fondo del mapa. Las ofertas y sus ubicaciones siguen disponibles en el listado.';
    });
    if ('ResizeObserver' in window) new ResizeObserver(() => map.invalidateSize()).observe(element);
    return map;
}

async function showPreview(picker, latitude, longitude) {
    if (!validCoordinates(latitude, longitude)) return;
    const preview = picker.querySelector('[data-location-preview]');
    const element = picker.querySelector('[data-location-preview-map]');
    if (!preview || !element) return;
    preview.hidden = false;
    picker.querySelector('[data-location-coordinates]').textContent = `Coordenadas del lugar seleccionado: ${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
    const L = await leaflet();
    if (preview.hidden) return;
    let state = maps.get(element);
    if (!state) {
        const map = makeMap(L, element);
        state = { map, marker: L.circleMarker([latitude, longitude], { radius: 9, color: '#123d32', fillColor: '#4e966c', fillOpacity: 1 }).addTo(map) };
        maps.set(element, state);
    }
    state.marker.setLatLng([latitude, longitude]);
    state.map.invalidateSize();
    state.map.setView([latitude, longitude], 14);
}

document.querySelectorAll('[data-location-picker]').forEach((picker) => {
    const input = picker.querySelector('[data-location-query]');
    const selection = picker.querySelector('[data-location-selection]');
    const button = picker.querySelector('[data-location-search]');
    const results = picker.querySelector('[data-location-results]');
    const status = picker.querySelector('[data-location-status]');
    const form = picker.closest('form');
    const mode = form?.querySelector('[data-work-mode]');
    const preview = picker.querySelector('[data-location-preview]');
    const nearby = form?.querySelector('[data-nearby-token]');
    const district = picker.dataset.context === 'offer' ? form?.querySelector('[name="district_id"]') : null;

    const clearSelection = () => {
        selection.value = '';
        results.replaceChildren();
        results.hidden = true;
        if (preview) preview.hidden = true;
        if (nearby) nearby.value = '';
        if (district) district.value = '';
        status.textContent = picker.dataset.context === 'offer' ? 'Ubicación manual: el texto se guardará sin asignar coordenadas.' : 'Pulsa «Buscar ubicación» para consultar coincidencias reales.';
    };
    // Typing only clears the old selection; it never sends a geocoding request.
    input.addEventListener('input', clearSelection);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.isComposing) {
            event.preventDefault();
            if (!button.disabled) button.click();
        }
    });
    button.addEventListener('click', async () => {
        const query = input.value.trim();
        if (query.length < 2 || query.length > 160) {
            status.textContent = 'Escribe entre 2 y 160 caracteres: por ejemplo «Cusco» o «Wanchaq, Cusco».';
            return;
        }
        button.disabled = true;
        results.hidden = true;
        results.replaceChildren();
        status.textContent = 'Consultando ubicaciones de Perú…';
        try {
            const data = await postJson(picker.dataset.searchUrl, { q: query });
            if (input.value.trim() !== query) return;
            status.textContent = data.results.length
                ? `${data.results.length} coincidencias de ${data.source}${data.cached ? ' (caché)' : ' (consulta en línea)'}. Selecciona una ubicación.`
                : 'No encontramos coincidencias en Perú. Prueba incluyendo ciudad y departamento, o conserva la ubicación escrita manualmente.';
            data.results.forEach((place) => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'location-result';
                option.textContent = place.label;
                option.addEventListener('click', () => {
                    input.value = place.label;
                    selection.value = place.selection;
                    if (district) {
                        const option = Array.from(district.options).find((item) => item.textContent.trim().toLocaleLowerCase('es') === place.name.toLocaleLowerCase('es'));
                        district.value = option?.value || '';
                    }
                    if (nearby) nearby.value = '';
                    results.hidden = true;
                    status.textContent = `Ubicación seleccionada: ${place.label}`;
                    showPreview(picker, Number(place.latitude), Number(place.longitude)).catch(() => {
                        status.textContent = 'Ubicación seleccionada. No se pudo cargar la vista previa del mapa.';
                    });
                    input.focus();
                });
                results.append(option);
            });
            results.hidden = data.results.length === 0;
        } catch (error) {
            status.textContent = error instanceof TypeError ? 'No hay conexión con el servicio. Puedes continuar con una ubicación escrita manualmente.' : error.message;
        } finally {
            button.disabled = mode?.value === 'remote';
        }
    });
    const updateMode = () => {
        if (mode?.value === 'remote') {
            clearSelection();
            button.disabled = true;
            status.textContent = 'Trabajo remoto: no se asignará un marcador ni se requiere dirección exacta.';
        } else {
            button.disabled = false;
        }
    };
    mode?.addEventListener('change', updateMode);
    updateMode();
    const element = picker.querySelector('[data-location-preview-map]');
    if (element && !preview.hidden && element.dataset.latitude && element.dataset.longitude) {
        showPreview(picker, Number(element.dataset.latitude), Number(element.dataset.longitude)).catch(() => {
            status.textContent = 'La ubicación está guardada. La vista previa del mapa no está disponible.';
        });
    }
});

document.querySelectorAll('[data-nearby-search]').forEach((container) => {
    const button = container.querySelector('[data-nearby-button]');
    const status = container.querySelector('[data-nearby-status]');
    button.addEventListener('click', () => {
        if (!window.isSecureContext || !navigator.geolocation) {
            status.textContent = 'La ubicación requiere HTTPS o localhost. Puedes buscar manualmente por ciudad o distrito.';
            return;
        }
        button.disabled = true;
        status.textContent = 'Esperando tu permiso de ubicación…';
        navigator.geolocation.getCurrentPosition(async ({ coords }) => {
            try {
                // Never transmit or persist the precise browser position.
                const data = await postJson(container.dataset.nearbyUrl, {
                    latitude: Number(coords.latitude.toFixed(2)), longitude: Number(coords.longitude.toFixed(2)),
                });
                const form = container.closest('form');
                container.querySelector('[data-nearby-token]').value = data.nearby;
                form.querySelector('[name="sort"]').value = 'distance';
                form.querySelector('[data-location-query]').value = '';
                form.querySelector('[data-location-selection]').value = '';
                form.requestSubmit();
            } catch (error) {
                status.textContent = error instanceof TypeError ? 'No pudimos conectar. Puedes buscar una ubicación manualmente.' : error.message;
            } finally {
                button.disabled = false;
            }
        }, (error) => {
            button.disabled = false;
            status.textContent = error.code === 1
                ? 'No autorizaste la ubicación. Puedes buscar manualmente por ciudad o distrito.'
                : 'No pudimos obtener tu ubicación. Puedes buscar manualmente por ciudad o distrito.';
        }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 });
    });
});

document.querySelectorAll('[data-job-map]').forEach(async (element) => {
    try {
        const L = await leaflet();
        const map = makeMap(L, element);
        const markers = JSON.parse(element.dataset.markers);
        const points = [];
        const icon = L.divIcon({ className: 'chambanet-map-pin', html: '<span aria-hidden="true"></span>', iconSize: [26, 26], iconAnchor: [13, 13] });
        markers.forEach((item) => {
            const latitude = Number(item.latitude);
            const longitude = Number(item.longitude);
            if (!validCoordinates(latitude, longitude)) return;
            const popup = document.createElement('div');
            popup.className = 'job-map-popup';
            const title = document.createElement('strong');
            title.textContent = item.title;
            const company = document.createElement('p');
            company.textContent = item.company;
            const locationText = document.createElement('p');
            locationText.textContent = item.location;
            const link = document.createElement('a');
            const url = new URL(item.url, window.location.origin);
            if (url.origin !== window.location.origin) return;
            link.href = url.href;
            link.className = 'text-link';
            link.textContent = 'Ver oferta →';
            popup.append(title, company, locationText, link);
            L.marker([latitude, longitude], { icon, title: item.title, alt: item.title }).addTo(map).bindPopup(popup);
            points.push([latitude, longitude]);
        });
        if (points.length) map.fitBounds(points, { padding: [35, 35], maxZoom: 14 });
    } catch {
        const status = element.closest('.job-map-block')?.querySelector('[data-map-status]');
        if (status) status.textContent = 'El mapa no está disponible. Puedes seguir consultando las ofertas en el listado.';
    }
});
