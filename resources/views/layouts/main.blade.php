<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subsync Dashboard</title>

<link rel="stylesheet" href="{{ asset('styles.css') }}">



</head>

<body>

<div class="container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <img src="dashbaord_2.svg" alt="Logo">
        </div>

        <ul>
            <li class="active">Dashboard</li>
            <li><a href="/finance">Finance</a></li>
            <li><a href="/manage_users">Manage Users</a></li>
            <li><a href="/deliquents">Deliquents</a></li>
            <li><a href="/analytics">Analytics</a></li>
            <li><a href="/reports">Reports</a></li>
            <li><a href="/mapping">Maps</a></li>
        </ul>

        <hr><br><br>
        <h2>Terra Nova</h2>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <header class="topbar">
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            <input type="text" placeholder="Search...">

            <!-- CLICKABLE ADMIN -->
          @guest
    <button class="admin-btn" onclick="openLogin()">
        Login
    </button>
@endguest

@auth
    <div class="admin-btn">
        {{ Auth::user()->name }}
    </div>
@endauth
        </header>

        <!-- CARDS -->
        <section class="cards-main">
            <div class="card">
                <a href="/members">
                    <h3>Members</h3>
                    <p>{{ $members->count() }}</p>
                </a>
            </div>

            <div class="card">
                <a href="/residents">
                    <h3>Houses</h3>
                    <p>{{ $households->count() }}</p>
                </a>
            </div>
        </section>

        <section class="cards">
            <div class="card">
                <h3>Payments</h3>
            </div>

            <div class="card">
                <h3>Bills due</h3>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">

            <div class="panel large">
                <img src="ChatGPT Image Mar 15, 2026, 07_39_03 AM.png">
                <div class="overlay">
                    <h3>Homes</h3>
                </div>
            </div>

            <div class="panel" id="main_panel">
                <h3>Status</h3>
                <div class="status ok">All Systems Normal</div>

                <br><br>

                <ul>
                    <li class="emergency">Emergency</li><br>
                    <li>Announcements</li>
                    <li>Officers</li>
                    <li>New Complaints</li>
                </ul>
            </div>

        </section>

    </main>
</div>

<!-- LOGIN MODAL -->
<div id="loginModal" class="login-modal" onclick="closeLogin(event)">

    <div class="login_box_admin" onclick="event.stopPropagation()">

        <span class="login-close" onclick="closeLogin()">✖</span>

        <div class="login_box_admin_logo">
            <img src="dashbaord_2.svg">
        </div>

        <div class="form-header">
            <h2>Admin Login</h2>
            <p>Access control panel</p>
        </div>

        <input type="text" placeholder="Username">
        <input type="password" placeholder="Password">

        <button class="main-btn">Login</button>

    </div>

</div>

<script>
function toggleSidebar() {
    document.querySelector(".sidebar").classList.toggle("active");
}

function openLogin() {
    document.getElementById("loginModal").classList.add("show");
}

function closeLogin(e) {
    if (e && e.target.classList.contains("login_box_admin")) return;
    document.getElementById("loginModal").classList.remove("show");
}
</script>

</body>
</html>