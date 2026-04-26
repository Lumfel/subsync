<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subsynce-Residents</title>
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
            <li><a href="/manage_users">Manage Users</a></li>
            <li><a href="finance">Finance</a></li>
              <li><a href="/manage_users">Manage Users</a></li>
            <li><a href="/deliquents">Deliquents</a></li>
            <li><a href="/analytics">Analytics</a></li>
            <li><a href="/reports">Reports</a></li>
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
                <a href="/members">
                <h3>Members</h3>
                <p>128</p>
                </a>
            </div>
        
            <div class="card active">
                <h3>Houses</h3>
                <p>96</p>
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
        
     <div class="res_content" id="residents">
    <!----

    <div class="res_card yt">
        <div class="thumb">
            <img src="ChatGPT Image Mar 14, 2026, 05_18_38 PM.png" alt="">
        </div>
        <div class="info">
            <h4>Household: Dela Cruz Family</h4>
            <p class="reason">Unpaid Bills</p>
            <p class="location">Blk 3 Lot 12</p>
        </div>
    </div>

    
    <div class="res_card yt">
        <div class="thumb">
            <img src="ChatGPT Image Mar 15, 2026, 07_38_59 AM.png" alt="">
        </div>
        <div class="info">
            <h4>Household: Santos Family</h4>
            <p class="reason warning">Noise Complaint</p>
            <p class="location">Blk 5 Lot 8</p>
        </div>
    </div>

  
    <div class="res_card yt">
        <div class="thumb">
            <img src="ChatGPT Image Mar 14, 2026, 06_37_33 PM (1).png" alt="">
        </div>
        <div class="info">
            <h4>Household: Reyes Family</h4>
            <p class="reason ok">Paid Association Fee</p>
            <p class="location">Blk 2 Lot 4</p>
        </div>
    </div>
-->


</div>




    </main>
<div id="viewModal" class="modal">
  <div class="modal-content">

    <span class="close" onclick="closeViewModal()">✖</span>

    <div id="modalBody"></div>

  </div>
</div>

</div>

<script>
function toggleSidebar() {
    document.querySelector(".sidebar").classList.toggle("active");
}
</script>

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/analysis.js') }}"></script>
<script src="{{ asset('js/d3.v7.min.js') }}"></script>
<script src="{{ asset('js/finance_script.js') }}"></script>
<script src="{{ asset('js/household and deliquent.js') }}"></script>

</body>
</html>
</body>
</html>
