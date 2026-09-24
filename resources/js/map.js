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
