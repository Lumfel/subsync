<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subsynce-members</title>
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
          
            @auth
    <div class="admin-btn">
        {{ Auth::user()->name }}
    </div>
@endauth
        </header>

        <!-- Cards -->
        <section class="cards-main">
       
            <div class="card active">
                <h3>Members</h3>
               <p>{{ $members->count() }}</p>
            </div>
            <div class="card">
                <a href="/residents">
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
        
    <div class="res_content " id="">


  @foreach($members as $member)
<div class="res_card yt">
    <div class="thumb">
        @if($member->household && $member->household->image)
            <img src="{{ asset('storage/' . $member->household->image) }}" alt="">
        @else
            <img src="{{ asset('default-house.png') }}" alt="">
        @endif
    </div>

    <div class="info">
        <h3 class="ok">
            {{ $member->user->name ?? 'Unknown User' }}
        </h3>

        <h4>
            Household:
            {{ $member->household->location ?? 'No Household' }}
        </h4>

        <p class="location">
            Type: {{ $member->member_type }}
        </p>
    </div>
</div>
@endforeach

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

</script>

</body>
</html>
</body>
</html>