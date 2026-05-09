/* =========================
   ELEMENT REFERENCES
========================= */
const manageModal = document.getElementById("manageModal");
const manageTitle = document.getElementById("manageTitle");
const manageBody = document.getElementById("manageBody");

const dropdownModal = document.getElementById("dropdownModal");
const dropdownBody = document.getElementById("dropdownBody");


/* =========================
   MODAL CONTROLS
========================= */
function closeManageModal() {
    manageModal.classList.remove("show");
}

function closeDropdownModal() {
    dropdownModal.classList.remove("show");
}

function openManageModal(title, content) {
    manageTitle.innerText = title;
    manageBody.innerHTML = content;
    manageModal.classList.add("show");
}

function openDropdown(content) {
    dropdownBody.innerHTML = content;
    dropdownModal.classList.add("show");
}


/* =========================
   MAIN ACTION HANDLER
========================= */
function openAction(type) {
    switch (type) {
        case "household":
            openManageModal("Add Household", householdForm());
            break;

        case "user":
            openManageModal("Add User", userForm());
            break;

        case "family":
            openManageModal("Add Family", familyForm());
            break;

        case "member":
            openDropdown(selectHouseholdPanel());
            bindHouseholdClicks();
            break;

        case "status":
            openManageModal("Change Status", statusForm());
            break;

        case "officer":
            openManageModal("Manage Officers", officerForm());
            break;
    }
}


/* =========================
   FORM TEMPLATES
========================= */
function householdForm() {
    return `
        <form method="POST" action="/households" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="${csrfToken()}">

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="text" name="location" placeholder="Blk 3 Lot 12" required>
                    <input type="file" name="image" accept="image/png,image/jpeg,image/jpg">
                </div>

                <div class="manage-right">
                    <button type="submit">Add Household</button>
                </div>
            </div>
        </form>
    `;
}

function userForm() {
    return `
        <form method="POST" action="/users">
            <input type="hidden" name="_token" value="${csrfToken()}">

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="text" name="name" placeholder="Full Name" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <div class="manage-right">
                    <button type="submit">Add User</button>
                </div>
            </div>
        </form>
    `;
}

function familyForm() {
    return `
        <form method="POST" action="/families">
            <input type="hidden" name="_token" value="${csrfToken()}">

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="text" name="family_name" placeholder="Family Name" required>
                </div>

                <div class="manage-right">
                    <button type="submit">Add Family</button>
                </div>
            </div>
        </form>
    `;
}

function statusForm() {
    return `
        <form method="POST" action="/statuses">
            <input type="hidden" name="_token" value="${csrfToken()}">

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="number" name="house_id" placeholder="Household ID" required>

                    <select name="status">
                        <option value="Active">Active</option>
                        <option value="Warning">Warning</option>
                        <option value="Delinquent">Delinquent</option>
                    </select>

                    <input type="text" name="reason" placeholder="Reason">
                </div>

                <div class="manage-right">
                    <button type="submit">Update</button>
                </div>
            </div>
        </form>
    `;
}

function officerForm() {
    return `
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


/* =========================
   HOUSEHOLD SELECTOR
========================= */
function selectHouseholdPanel() {
    return `
        <div class="household-select-layout">
            <h2>Select Household</h2>

            <input
                type="text"
                placeholder="Search household..."
                class="search-users"
                onkeyup="filterHouseholds(this.value)"
            >

            <div class="household-list">
                ${window.householdsHTML || '<p>No households found.</p>'}
            </div>
        </div>
    `;
}

function filterHouseholds(search) {
    search = search.toLowerCase();

    document.querySelectorAll(".household-item").forEach(item => {
        item.style.display = item.innerText.toLowerCase().includes(search)
            ? "block"
            : "none";
    });
}

function bindHouseholdClicks() {
    document.querySelectorAll(".household-item").forEach(item => {
        item.onclick = function () {
            openHouseholdMembers(
                this.dataset.family,
                this.dataset.status,
                this.dataset.members
            );
        };
    });
}


/* =========================
   HOUSEHOLD MEMBER PANEL
========================= */
function openHouseholdMembers(familyName, status, members) {
    openDropdown(`
        <div class="drop-layout">

            <div class="drop-house-card">
                <div class="drop-house-image">
                    <img src="/storage/households/default.jpg">
                    <div class="drop-house-name">${familyName}</div>
                </div>

                <div class="drop-house-meta">
                    <span>Status: ${status}</span>
                    <span>Members: ${members}</span>
                </div>

                <div id="dropZone" class="drop-zone">
                    Drag users here
                </div>
            </div>

            <div class="drop-user-panel">
                <h3>Search Users</h3>

                <input
                    type="text"
                    placeholder="Search user..."
                    class="search-users"
                    onkeyup="filterUsers(this.value)"
                >

                <div class="user-list">
                    ${renderUsers()}
                </div>
            </div>
        </div>
    `);

    initDragDrop();
}


/* =========================
   USERS
========================= */
function renderUsers() {
    return window.usersHTML || '<p>No users found.</p>';
}

function filterUsers(search) {
    search = search.toLowerCase();

    document.querySelectorAll(".user-item").forEach(user => {
        user.style.display = user.innerText.toLowerCase().includes(search)
            ? "block"
            : "none";
    });
}


/* =========================
   DRAG DROP
========================= */
function initDragDrop() {
    const userItems = document.querySelectorAll(".user-item");
    const dropZone = document.getElementById("dropZone");

    if (!dropZone) return;

    userItems.forEach(item => {
        item.addEventListener("dragstart", function () {
            this.classList.add("dragging");
        });

        item.addEventListener("dragend", function () {
            this.classList.remove("dragging");
        });
    });

    dropZone.addEventListener("dragover", e => e.preventDefault());

    dropZone.addEventListener("drop", function (e) {
        e.preventDefault();

        const dragged = document.querySelector(".dragging");

        if (dragged) {
            const clone = dragged.cloneNode(true);
            clone.classList.remove("dragging");
            clone.draggable = false;

            dropZone.appendChild(clone);
            dragged.remove();
        }
    });
}


/* =========================
   TABLE SWITCH + SEARCH
========================= */
function editHouse(id, location) {
    openManageModal("Edit Household", `
        <form method="POST" action="/households/${id}">
            <input type="hidden" name="_token" value="${csrfToken()}">
            <input type="hidden" name="_method" value="PUT">

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="text" name="location" value="${location}" required>
                </div>

                <div class="manage-right">
                    <button type="submit">Save Changes</button>
                </div>
            </div>
        </form>
    `);
}

function editUser(id, name, email) {
    openManageModal("Edit User", `
        <form method="POST" action="/users/${id}">
            <input type="hidden" name="_token" value="${csrfToken()}">
            <input type="hidden" name="_method" value="PUT">

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
    `);
}

function editFamily(id, familyName) {
    openManageModal("Edit Family", `
        <form method="POST" action="/families/${id}">
            <input type="hidden" name="_token" value="${csrfToken()}">
            <input type="hidden" name="_method" value="PUT">

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="text" name="family_name" value="${familyName}" required>
                </div>

                <div class="manage-right">
                    <button type="submit">Save Changes</button>
                </div>
            </div>
        </form>
    `);
}

function editMember(id, userId, houseId, memberType) {
    openManageModal("Edit Member", `
        <form method="POST" action="/members/${id}">
            <input type="hidden" name="_token" value="${csrfToken()}">
            <input type="hidden" name="_method" value="PUT">

            <div class="manage-form-row">
                <div class="manage-left">
                    <input type="number" name="user_id" value="${userId}" required>
                    <input type="number" name="house_id" value="${houseId}" required>
                </div>

                <div class="manage-right">
                    <button type="submit">Save Changes</button>
                </div>
            </div>
        </form>
    `);
}
function switchPanel() {
    const selected = document.getElementById("tableSelector").value;

    document.querySelectorAll(".table-panel").forEach(panel => {
        panel.style.display = "none";
    });

    document.getElementById(selected).style.display = "block";
    document.getElementById("searchBar").value = "";
    filterTable();
}

function filterTable() {
    const input = document.getElementById("searchBar").value.toLowerCase();

    const activePanel = [...document.querySelectorAll(".table-panel")]
        .find(panel => panel.style.display !== "none");

    if (!activePanel) return;

    activePanel.querySelectorAll("tbody tr").forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input)
            ? ""
            : "none";
    });
}


/* =========================
   SIDEBAR
========================= */
function toggleSidebar() {
    document.querySelector(".sidebar").classList.toggle("active");
}


/* =========================
   HELPERS
========================= */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}



window.openAction = openAction;
window.closeManageModal = closeManageModal;
window.closeDropdownModal = closeDropdownModal;
window.openHouseholdMembers = openHouseholdMembers;
window.filterUsers = filterUsers;
window.filterHouseholds = filterHouseholds;
window.switchPanel = switchPanel;
window.filterTable = filterTable;
window.toggleSidebar = toggleSidebar;

window.editHouse = editHouse;
window.editUser = editUser;
window.editFamily = editFamily;
window.editMember = editMember;