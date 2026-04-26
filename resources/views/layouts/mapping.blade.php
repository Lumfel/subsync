<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subsync Dashboard</title>
<link rel="stylesheet" href="{{ asset('styles.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body>

<div class="container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
        <img src="dashbaord_2.svg" alt="Logo">
        </div>
        <ul>
         <li><a href="/">Dashboard</a></li>
            <li><a href="/finance">Finance</a></li>
            <li><a href="/manage_users">Manage Users</a></li>
            <li ><a href="/deliquents">Deliquents</a></li>
            <li><a href="/analytics">Analytics</a></li>
            <li ><a href="/reports">Reports</a></li>
            <li class="active"><a href="/mapping"> Maps</a></li>
       

        </ul>
        <hr>
        <br>
        <br>
        <h2>Terra Nova </h2>
    </aside>
  <main class="main full-map">

    <header class="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
        <input type="text" placeholder="Search...">
        <div>Admin</div>
    </header>

    <!-- FULLSCREEN MAP -->
    <div id="map"></div>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    
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

// 🗺️ Create map
const map = L.map('map', {
    center: terraNovaCenter,
    zoom: 17,
    minZoom: 16,
    maxZoom: 19
});

// 🌍 Tile
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);


// 🔒 STRICT SUBDIVISION BOUNDS (tight box)
const bounds = L.latLngBounds(
    [10.6195, 122.9585], // southwest (lower left)
    [10.6255, 122.9645]  // northeast (upper right)
);

map.setMaxBounds(bounds);

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