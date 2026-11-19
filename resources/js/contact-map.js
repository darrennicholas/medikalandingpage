import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const mapElement = document.querySelector('[data-contact-map]');
if (mapElement) {
    const latitude = Number(mapElement.dataset.latitude);
    const longitude = Number(mapElement.dataset.longitude);
    if (Number.isFinite(latitude) && Number.isFinite(longitude) && Math.abs(latitude) <= 90 && Math.abs(longitude) <= 180) {
        const map = L.map(mapElement, { scrollWheelZoom: false }).setView([latitude, longitude], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);
        const label = document.createElement('span');
        label.textContent = mapElement.dataset.mapLabel || '';
        L.circleMarker([latitude, longitude], { radius: 9, color: '#ffffff', weight: 3, fillColor: '#245bd2', fillOpacity: 1 }).addTo(map).bindPopup(label);
    }
}
