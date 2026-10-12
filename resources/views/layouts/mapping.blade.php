<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subsync Dashboard</title>
<link rel="stylesheet" href="{{ asset('styles.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
<script>
  // Point default marker icons at local images
  if(window.L && L.Icon && L.Icon.Default){
    L.Icon.Default.mergeOptions({
      iconUrl: '{{ asset('vendor/leaflet/images/marker-icon.png') }}',
      iconRetinaUrl: '{{ asset('vendor/leaflet/images/marker-icon-2x.png') }}',
      shadowUrl: '{{ asset('vendor/leaflet/images/marker-shadow.png') }}',
    });
  }
</script>
</head>

<body>

<div class="container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
        <img src="{{ asset('emblem_1.svg') }}" alt="SubSync logo">
        </div>
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/login">Portal Sign-in</a></li>
            <li class="active"><a href="/mapping">Subdivision Map</a></li>
        </ul>
        <hr>
        <br>
        <br>
        <h2>Terra Nova </h2>
    </aside>
  <main class="main full-map">

    <header class="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
     @auth
    <div class="admin-btn">
        {{ Auth::user()->name }}
    </div>
@endauth
    </header>

    <!-- FULLSCREEN MAP -->
    <div id="map"></div>
    
</main>
</div>

<script>
function toggleSidebar() {
    document.querySelector(".sidebar").classList.toggle("active");
}
</script>

</body>
</html>



<script>
// 📍 Terra Nova Panaad (approximate coordinates)
// 🎯 REAL Terra Nova center
const terraNovaCenter = [10.62269, 122.96134];

// Strict Terra Nova subdivision bounds.
const bounds = L.latLngBounds(
    [10.6195, 122.9585],
    [10.6255, 122.9645]
);

// 🗺️ Create map
const map = L.map('map', {
    center: terraNovaCenter,
    zoom: 17,
    minZoom: 16,
    maxZoom: 19,
    maxBounds: bounds,
    maxBoundsViscosity: 1.0
});

// 🌍 Tile
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);


map.setMaxBounds(bounds);
map.fitBounds(bounds, { padding: [12, 12] });

// Prevent escaping the subdivision
map.on('drag', function () {
    map.panInsideBounds(bounds, { animate: false });
});


// 📌 MARKER SYSTEM
let markers = [];

// Drop marker on click
map.on('click', function (e) {

    const marker = L.marker(e.latlng, {
        draggable: true
    }).addTo(map);

    marker.bindPopup("📍 New Household").openPopup();

    markers.push(marker);
});
</script>