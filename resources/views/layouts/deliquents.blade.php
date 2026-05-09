<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Deliquents</title>
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
            <li class="active"><a href="/deliquents">Deliquents</a></li>
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
       
            <div class="card">
                <a href="/members">
                <h3>Members</h3>
                <p>128</p>
                </a>
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
        
       <div class="res_content">
         @foreach($delinquents as $delinquent)
    <div class="res_card yt">
        <div class="thumb">
            @if($delinquent->household && $delinquent->household->image)
                <img src="{{ asset('storage/' . $delinquent->household->image) }}" alt="">
            @else
                <img src="{{ asset('default-house.png') }}" alt="">
            @endif
        </div>

        <div class="info">
            <h4>
                Household #{{ $delinquent->house_id }}
            </h4>

            <p class="reason">
                {{ $delinquent->reason }}
            </p>

            <p class="location">
                {{ $delinquent->household->location ?? 'No location' }}
            </p>

            <small>
                Flagged: {{ \Carbon\Carbon::parse($delinquent->date_flagged)->format('M d, Y') }}
            </small>
        </div>
    </div>
    @endforeach
        </div>

<!----       {
  
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
            <p class="reason">Noise Complaint</p>
            <p class="location">Blk 5 Lot 8</p>
        </div>
    </div>


    <div class="res_card yt">
        <div class="thumb">
            <img src="ChatGPT Image Mar 14, 2026, 06_37_33 PM (1).png" alt="">
        </div>
        <div class="info">
            <h4>Household: Reyes Family</h4>
            <p class="reason">Unpaid Association Fee</p>
            <p class="location">Blk 2 Lot 4</p>
        </div>
    </div>
}
--->



    </main>

</div>

<script>

</script>

</body>
</html>
</body>
</html>