<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subsync - Manage Users</title>
<link rel="stylesheet" href="{{ asset('styles.css') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">


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
            <div class="manage-card" onclick="openAction('user')">
                <h3>➕ Add User</h3>
                <p>Create new system user</p>
            </div>
            <div class="manage-card" onclick="openAction('family')">
                    <h3>👨‍👩‍👧 Add Family</h3>
                     <p>Create family record</p>
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
            <!-----
            <div class="manage-card" onclick="openAction('view')">
                <h3>📋 View All Records</h3>
                <p>Inspect all households & members</p>
            </div>
            --->

        </section>

        <!-- FILTER + TABLE SWITCH -->
<section class="panel" style="margin-top:20px;">
    <div style="display:flex; gap:15px; align-items:center;">
        <input
            type="text"
            id="searchBar"
            placeholder="Search..."
            onkeyup="filterTable()"
            style="flex:1;"
        >

        <select id="tableSelector" onchange="switchPanel()">
            <option value="householdsPanel">Households</option>
            <option value="usersPanel">Users</option>
            <option value="familiesPanel">Families</option>
            <option value="membersPanel">Members</option>
        </select>
    </div>
</section>

<br>

<section class="panel">

    <div id="householdsPanel" class="table-panel" style="display:block;">
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
    </div>

    <div id="usersPanel" class="table-panel" style="display:none;">
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
    </div>

    <div id="familiesPanel" class="table-panel" style="display:none;">
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
    </div>

    <div id="membersPanel" class="table-panel" style="display:none;">
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
                <td>{{ $member->user->name ?? 'N/A' }}</td>
               <td>{{ $member->household->location ?? 'N/A' }}</td>
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
    </div>

</section>
      
        <br><br>
   </main>
   <!-- MANAGE MODAL -->
    <div id="manageModal" class="manage-modal" onclick="closeManageModal()">
    <div class="manage-modal-box" onclick="event.stopPropagation()">
        <span class="manage-close" onclick="closeManageModal()">✖</span>
        <h3 id="manageTitle">Action</h3>
        <div id="manageBody"></div>
    </div>
</div> <!-- THIS WAS MISSING -->

<div id="dropdownModal" class="manage-modal" onclick="closeDropdownModal()">
    <div class="manage-modal-dropdown-box" onclick="event.stopPropagation()">
        <span class="manage-close" onclick="closeDropdownModal()">✖</span>
        <h3 id="dropdownTitle">Manage Members</h3>
        <div id="dropdownBody"></div>
    </div>
</div>

<script src="{{ asset('js/manage_user.js') }}" defer></script>

<script>
window.householdsHTML = `
@foreach($households as $household)
    <div class="household-item"
         data-family="{{ $household->family->family_name ?? 'No Family' }}"
         data-status="{{ $household->status ?? 'Active' }}"
       data-members="{{ $household->householdMembers()->count() }}"
        <h3>{{ $household->location }}</h3>
        <p>ID: {{ $household->id }}</p>
    </div>
@endforeach
`;

window.usersHTML = `
@foreach($users as $user)
    <div class="user-item" draggable="true">
        {{ $user->name }}
    </div>
@endforeach
`;
</script>
</body>
</html>