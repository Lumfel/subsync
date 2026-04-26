<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports</title>
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
      <li><a href="/">Dashboard</a></li>
            <li><a href="/finance">Finance</a></li>
            <li><a href="/manage_users">Manage Users</a></li>
            <li ><a href="/deliquents">Deliquents</a></li>
            <li><a href="/analytics">Analytics</a></li>
            <li class="active"><a href="/reports">Reports</a></li>
            <li><a href="/mapping"> Maps</a></li>


        </ul>
        <hr>
        <br>
        <br>
        <h2>Terra Nova </h2>
    </aside>
    <main class="main">

        <header class="topbar">
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            <input type="text" placeholder="Search...">
          
            <div>Admin</div>
        </header>

        <!-- Cards -->
        <section class="cards-main">
          
            <div class="card">
                  <a href="members.html">
                <h3>Members</h3>
                <p>128</p>
                </a>
            </div>
          
         
            <div class="card">
                <a href="residents.html">
                <h3>Houses</h3>
                <p>96</p>
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
<div class="card"><h1>Analytics</h1></div>
        <div class="graph_container">
            
            <div class="card"></div> 
            <div class="card"></div>
            <div class="card"></div>
    

            <div class="card"><h1>Complaints</h1></div>
            <div class="card"><h1>Deliquents</h1></div>
            <div class="card"><h1>Recent Reports</h1></div>
      
        
        </div>
        <div class="graph_container1">
            <div class="card"></div>
            <div class="card"></div>

            <div class="card"><h1>Heat Map</h1></div>
            <div class="card"><h1>Security logs</h1></div>
        </div>

    </main>

</div>

<script>
function toggleSidebar() {
    document.querySelector(".sidebar").classList.toggle("active");
}
</script>

</body>
</html>