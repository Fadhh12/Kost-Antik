/*
 * Peta Leaflet + OpenStreetMap (tanpa API key).
 * <div data-map data-lat=".." data-lng=".." data-label=".."></div>
 * Mode pilih lokasi (admin): tambahkan data-pick, data-lat-input="#id", data-lng-input="#id".
 */
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const DEFAULT_CENTER = [-6.2615, 107.0379]; // Bekasi-Cikarang

const pin = L.divIcon({
    className: '',
    html: `<span style="display:block;width:28px;height:28px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:#0F4D48;border:3px solid #E2C27A;box-shadow:0 6px 14px rgb(8 43 40 / .35)"></span>`,
    iconSize: [28, 28],
    iconAnchor: [14, 28],
});

function initMap(el) {
    const lat = parseFloat(el.dataset.lat);
    const lng = parseFloat(el.dataset.lng);
    const hasPoint = !Number.isNaN(lat) && !Number.isNaN(lng);
    const pick = el.hasAttribute('data-pick');

    const map = L.map(el, {
        scrollWheelZoom: false,
        attributionControl: true,
    }).setView(hasPoint ? [lat, lng] : DEFAULT_CENTER, hasPoint ? 16 : 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    let marker = hasPoint ? L.marker([lat, lng], { icon: pin, draggable: pick }).addTo(map) : null;
    if (marker && el.dataset.label) {
        marker.bindPopup(el.dataset.label);
    }

    if (!pick) {
        return;
    }

    const latInput = document.querySelector(el.dataset.latInput);
    const lngInput = document.querySelector(el.dataset.lngInput);
    const write = (point) => {
        if (latInput) latInput.value = point.lat.toFixed(7);
        if (lngInput) lngInput.value = point.lng.toFixed(7);
    };

    map.on('click', (event) => {
        if (!marker) {
            marker = L.marker(event.latlng, { icon: pin, draggable: true }).addTo(map);
            marker.on('dragend', () => write(marker.getLatLng()));
        } else {
            marker.setLatLng(event.latlng);
        }
        write(event.latlng);
    });

    if (marker) {
        marker.on('dragend', () => write(marker.getLatLng()));
    }
}

document.querySelectorAll('[data-map]').forEach(initMap);

/*
 * Peta multi-titik (katalog publik): <div data-map-points data-points="[...]"></div>
 * Tiap titik: { lat, lng, name, url, thumb?, rating?, reviews? }.
 */
function initMultiMap(el) {
    let points = [];
    try {
        points = JSON.parse(el.dataset.points || '[]');
    } catch {
        points = [];
    }

    const map = L.map(el, { scrollWheelZoom: false, attributionControl: true }).setView(DEFAULT_CENTER, 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    const markers = points
        .filter((point) => typeof point.lat === 'number' && typeof point.lng === 'number')
        .map((point) => {
            const marker = L.marker([point.lat, point.lng], { icon: pin }).addTo(map);
            marker.bindPopup(buildPopup(point));

            return marker;
        });

    if (markers.length) {
        map.fitBounds(L.featureGroup(markers).getBounds().pad(0.2), { maxZoom: 15 });
    }
}

function buildPopup(point) {
    const box = document.createElement('div');
    box.className = 'w-44';

    if (point.thumb) {
        const img = document.createElement('img');
        img.src = point.thumb;
        img.alt = '';
        img.className = 'mb-1.5 h-20 w-full rounded-md object-cover';
        box.appendChild(img);
    }

    const title = document.createElement('a');
    title.href = point.url;
    title.textContent = point.name;
    title.className = 'block text-sm font-semibold text-tegel-800 hover:underline';
    box.appendChild(title);

    if (point.rating) {
        const rating = document.createElement('p');
        rating.className = 'mt-0.5 text-xs text-ink-500';
        rating.textContent = `★ ${point.rating} (${point.reviews ?? 0} ulasan)`;
        box.appendChild(rating);
    }

    return box;
}

document.querySelectorAll('[data-map-points]').forEach(initMultiMap);
