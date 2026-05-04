<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subsync - Manage Users</title>
<link rel="stylesheet" href="{{ asset('styles.css') }}">

</head>
<body>

<div class="container">
    <aside class="sidebar">
        <div class="logo">
            <img src="dashbaord_2.svg" alt="Logo">
        </div>
        <ul>
            <li><a href="/">Dashboard</a></li>
            <li><a href="/finance">Finance</a></li>
            <li class="active"><a href="manage_users">Manage Users</a></li>
            <li><a href="/deliquents">Deliquents</a></li>
            <li><a href="/analytics">Analytics</a></li>
            <li><a href="/reports">Reports</a></li>
            <li><a href="/mapping">Maps</a></li>
        </ul>
        <hr>
        <br>
        <h2>Terra Nova</h2>
    </aside>

    <main class="main">
        <header class="topbar">
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            <div class="page-title">
                <h2>User Management</h2>
                <p style="font-size: 0.8rem; opacity: 0.7;">Manually add new residents to the system</p>
            </div>
@auth
    <div class="admin-btn">
        {{ Auth::user()->name }}
    </div>
@endauth
        </header>
         <section class="manage-grid">

     <div class="manage-card" onclick="openAction('household')">
                <h3>🏠 Add Household</h3>
                <p>Create new household records</p>
            </div>

            <div class="manage-card" onclick="openAction('member')">
                <h3>👤 Add Member / Change Type</h3>
                <p>Add or update member roles</p>
            </div>

            <div class="manage-card" onclick="openAction('status')">
                <h3>⚠️ Change Status</h3>
                <p>Update household condition</p>
            </div>

            <div class="manage-card" onclick="openAction('officer')">
                <h3>🛡️ Manage Officers</h3>
                <p>Assign admin roles</p>
            </div>

            <div class="manage-card" onclick="openAction('view')">
                <h3>📋 View All Records</h3>
                <p>Inspect all households & members</p>
            </div>

        </section>

        <section class="content" style="grid-template-columns: 1fr; margin-top: 20px;">
            <!----
            <div class="panel">
                <h3>Add New Household Member</h3>
                <br>
                <form class="admin-registration-form">
                    <div class="cards" style="margin: 0; gap: 15px;">
                        <div style="flex: 2;">
                            <label>Household Full Name</label>
                            <input type="text" placeholder="e.g. John Doe" required style="width: 100%; margin-top: 8px; color: white; select option">
                        </div>
                        <div style="flex: 1;">
                            <label>Member Type</label>
                            <select style="width: 100%; margin-top: 8px; padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.08); color: white; border: none; backdrop-filter: blur(10px);">
                                <option value="family">Family Member</option>
                                <option value="relative">Relative</option>
                                <option value="tenant">Tenant</option>
                            </select>
                        </div>
                    </div>

                    <div class="cards" style="gap: 15px;">
                        <div style="flex: 1;">
                            <label>Block</label>
                            <input type="text" placeholder="Block No." required style="width: 100%; margin-top: 8px; color: white;">
                        </div>
                        <div style="flex: 1;">
                            <label>Lot</label>
                            <input type="text" placeholder="Lot No." required style="width: 100%; margin-top: 8px;    color: white;">
                        </div>
                    </div>
             </form>
          </div>
          --->
        </section>
        <br><br>
        <section class="panel">
    <h3>Households</h3>

    <table id="households">
        <thead>
            <tr>
                <th>ID</th>
                <th>Location</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
    @foreach($households as $household)
    <tr>
        <td>{{ $household->id }}</td>
        <td>{{ $household->location }}</td>
        <td>{{ $household->created_at->format('M d, Y') }}</td>
        <td>
    <button onclick="editHouse({{ $household->id }}, '{{ $household->location }}')">
        Edit
    </button>

    <form action="/households/{{ $household->id }}" method="POST"
          style="display:inline;"
          onsubmit="return confirm('Delete this household?')">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form>
</td>
    </tr>
    @endforeach
</tbody>
    </table>
</section>
        <br>
<br>
<section class="panel">
    <h3>Users</h3>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
    @foreach($users as $user)
    <tr>
        <td>{{ $user->id }}</td>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td>
    <button onclick="editUser(
        {{ $user->id }},
        '{{ $user->name }}',
        '{{ $user->email }}'
    )">Edit</button>

    <form action="/users/{{ $user->id }}" method="POST"
          style="display:inline;"
          onsubmit="return confirm('Delete this user?')">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form>
</td>
    </tr>
    @endforeach
</tbody>
    </table>
</section>
<br>
<section class="panel">
    <h3>Families</h3>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Family Name</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($families as $family)
           <tr>
    <td>{{ $family->id }}</td>
    <td>{{ $family->family_name }}</td>
    <td>
        <button onclick="editFamily(
            {{ $family->id }},
            '{{ $family->family_name }}'
        )">Edit</button>

        <form action="/families/{{ $family->id }}" method="POST"
              style="display:inline;"
              onsubmit="return confirm('Delete this family?')">
            @csrf
            @method('DELETE')
            <button type="submit">Delete</button>
        </form>
    </td>
</tr>
            @endforeach
        </tbody>
    </table>
</section>
<br>
<section class="panel">
    <h3>Members</h3>

    <table>
 <thead>
    <tr>
        <th>ID</th>
        <th>User ID</th>
        <th>Household ID</th>
        <th>Member Type</th>
        <th>Actions</th>
    </tr>
</thead>

<tbody>
    @foreach($members as $member)
    <tr>
        <td>{{ $member->id }}</td>
        <td>{{ $member->user_id }}</td>
        <td>{{ $member->house_id }}</td>
        <td>{{ $member->member_type }}</td>
      <td>
    <button onclick="editMember(
        {{ $member->id }},
        {{ $member->user_id }},
        {{ $member->house_id }},
        '{{ $member->member_type }}'
    )">Edit</button>

    <form action="/members/{{ $member->id }}" method="POST"
          style="display:inline;"
          onsubmit="return confirm('Delete this member?')">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form>
</td>
    </tr>
    @endforeach
</tbody>
    </table>
</section>
        <br> <br>
        <section class="panel">
            <h3>Officers</h3>

            <table id="Officers">
                <thead>
                    <tr>
                        <th>Postion</th>
                        <th>Name</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

        </section> 
   </main>
   <!-- MANAGE MODAL -->
<div id="manageModal" class="manage-modal" onclick="closeManageModal()">
    <div class="manage-modal-box" onclick="event.stopPropagation()">

        <span class="manage-close" onclick="closeManageModal()">✖</span>

        <h3 id="manageTitle">Action</h3>
        <div id="manageBody"></div>

    </div>
   

</body>
</html>
<script>
function openAction(type){
    const modal = document.getElementById("manageModal");
    const title = document.getElementById("manageTitle");
    const body = document.getElementById("manageBody");

    modal.classList.add("show");

    // 🏠 HOUSEHOLD
    /*
  if(type === "household"){
    title.innerText = "Add Household";
    body.innerHTML = `
        <form method="POST" action="/households">
            @csrf

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="text" name="location" placeholder="Blk 3 Lot 12" required>
                    <input type="number" name="family_id" placeholder="Family ID" required>
                    <input type="number" name="status_id" placeholder="Status ID" required>
                    <input type="number" name="members" placeholder="Members Count" required>
                </div>

                <div class="manage-right">
                    <button type="submit">Add</button>
                </div>
            </div>
        </form>
    `;
}
    */
   if(type === "household"){
    title.innerText = "Add Household";

    body.innerHTML = `
        <form method="POST" action="/households" enctype="multipart/form-data">
            @csrf

            <div class="manage-form-row">
                <div class="manage-left">
                    <input
                        type="text"
                        name="location"
                        placeholder="Blk 3 Lot 12"
                        required
                    >

                    <input
                        type="file"
                        name="image"
                        accept="image/png,image/jpeg,image/jpg"
                    >
                </div>

                <div class="manage-right">
                    <button type="submit">Add Household</button>
                </div>
            </div>
        </form>
    `;
}

    // 👤 MEMBER
   else if(type === "member"){
    title.innerText = "Add Member";

    body.innerHTML = `
        <form method="POST" action="/members">
            @csrf

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="number" name="user_id" placeholder="User ID" required>
                    <input type="number" name="house_id" placeholder="House ID" required>

                    <select name="member_type">
                        <option value="Family_member">Family Member</option>
                        <option value="Tenant">Tenant</option>
                    </select>
                </div>

                <div class="manage-right">
                    <button type="submit">Add Member</button>
                </div>
            </div>
        </form>
    `;
}

    // ⚠️ STATUS
   else if(type === "status"){
    title.innerText = "Change Status";

    body.innerHTML = `
        <form method="POST" action="/statuses">
            @csrf

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="number" name="house_id" placeholder="House ID" required>

                    <select name="status_type">
                        <option value="Active">Active</option>
                        <option value="Warning">Warning</option>
                        <option value="Delinquent">Delinquent</option>
                    </select>
                </div>

                <div class="manage-right">
                    <button type="submit">Update</button>
                </div>
            </div>
        </form>
    `;
}
    // 🛡️ OFFICER
    else if(type === "officer"){
        title.innerText = "Manage Officers";
        body.innerHTML = `
            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="text" placeholder="Officer Name">
                </div>
                <div class="manage-right">
                    <button>Assign</button>
                </div>
            </div>
        `;
    }

    // 📋 VIEW
    else if(type === "view"){
        title.innerText = "All Records";
        body.innerHTML = `
            <div class="manage-form-row">
                <div class="manage-left">
                    <p>Display records here</p>
                </div>
            </div>
        `;
    }
}

/* CLOSE MODAL */
function closeManageModal(){
    document.getElementById("manageModal").classList.remove("show");
}

/* OPTIONAL SIDEBAR FIX */
function toggleSidebar(){
    document.querySelector(".sidebar").classList.toggle("active");
}



function editHouse(id, location){
    const modal = document.getElementById("manageModal");
    const title = document.getElementById("manageTitle");
    const body = document.getElementById("manageBody");

    modal.classList.add("show");
    title.innerText = "Edit Household";

    body.innerHTML = `
        <form method="POST" action="/households/${id}">
            @csrf
            @method('PUT')

            <div class="manage-form-row">
                <div class="manage-left">
                    <input
                        type="text"
                        name="location"
                        value="${location}"
                        required
                    >
                </div>

                <div class="manage-right">
                    <button type="submit">Save Changes</button>
                </div>
            </div>
        </form>
    `;
}
function editUser(id, name, email){
    const modal = document.getElementById("manageModal");
    const title = document.getElementById("manageTitle");
    const body = document.getElementById("manageBody");

    modal.classList.add("show");
    title.innerText = "Edit User";

    body.innerHTML = `
        <form method="POST" action="/users/${id}">
            @csrf
            @method('PUT')

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="text" name="name" value="${name}" required>
                    <input type="email" name="email" value="${email}" required>
                </div>

                <div class="manage-right">
                    <button type="submit">Save Changes</button>
                </div>
            </div>
        </form>
    `;
}

function editFamily(id, familyName){
    const modal = document.getElementById("manageModal");
    const title = document.getElementById("manageTitle");
    const body = document.getElementById("manageBody");

    modal.classList.add("show");
    title.innerText = "Edit Family";

    body.innerHTML = `
        <form method="POST" action="/families/${id}">
            @csrf
            @method('PUT')

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="text" name="family_name" value="${familyName}" required>
                </div>

                <div class="manage-right">
                    <button type="submit">Save Changes</button>
                </div>
            </div>
        </form>
    `;
}

function editMember(id, userId, houseId, memberType){
    const modal = document.getElementById("manageModal");
    const title = document.getElementById("manageTitle");
    const body = document.getElementById("manageBody");

    modal.classList.add("show");
    title.innerText = "Edit Member";

    body.innerHTML = `
        <form method="POST" action="/members/${id}">
            @csrf
            @method('PUT')

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="number" name="user_id" value="${userId}" required>
                    <input type="number" name="house_id" value="${houseId}" required>

                    <select name="member_type">
                        <option value="Family_member"
                            ${memberType === 'Family_member' ? 'selected' : ''}>
                            Family Member
                        </option>
                        <option value="Tenant"
                            ${memberType === 'Tenant' ? 'selected' : ''}>
                            Tenant
                        </option>
                    </select>
                </div>

                <div class="manage-right">
                    <button type="submit">Save Changes</button>
                </div>
            </div>
        </form>
    `;
}
</script>