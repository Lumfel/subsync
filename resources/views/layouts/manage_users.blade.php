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
            <li class="active"><a href="manage_users.html">Manage Users</a></li>
            <li><a href="/deliquents">Deliquents</a></li>
            <li><a href="/analytics.html">Analytics</a></li>
            <li><a href="/reports.html">Reports</a></li>
            <li><a href="/mapping.html">Maps</a></li>
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
            <div>Admin</div>
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
            <h3>Records</h3>

            <table id="households">
                <thead>
                    <tr>
                        <th>Household</th>
                        <th>Location</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody></tbody>
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

<script>
function openAction(type){
    const modal = document.getElementById("manageModal");
    const title = document.getElementById("manageTitle");
    const body = document.getElementById("manageBody");

    modal.classList.add("show");

    // 🏠 HOUSEHOLD
    if(type === "household"){
        title.innerText = "Add Household";
        body.innerHTML = `
            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="text" placeholder="Household Name">
                    <input type="text" placeholder="Block">
                    <input type="text" placeholder="Lot">
                </div>
                <div class="manage-right">
                    <button>Add</button>
                </div>
            </div>
        `;
    }

    // 👤 MEMBER
    else if(type === "member"){
        title.innerText = "Add Member";
        body.innerHTML = `
            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="text" placeholder="Member Name">
                    <select>
                        <option>Family</option>
                        <option>Relative</option>
                        <option>Tenant</option>
                    </select>
                </div>
                <div class="manage-right">
                    <button>Add Member</button>
                </div>
            </div>
        `;
    }

    // ⚠️ STATUS
    else if(type === "status"){
        title.innerText = "Change Status";
        body.innerHTML = `
            <div class="manage-form-row">
                <div class="manage-left">
                    <select>
                        <option>Household 1</option>
                        <option>Household 2</option>
                    </select>

                    <select>
                        <option>Active</option>
                        <option>Warning</option>
                        <option>Delinquent</option>
                    </select>
                </div>
                <div class="manage-right">
                    <button>Update</button>
                </div>
            </div>
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
</script>