const homes = [
    {
        image: 'ChatGPT Image Mar 14, 2026, 05_18_38 PM.png',
        household: 'Dela Cruz Family',
        members: [{ name: "Isha Dela-Cruz", role: "Family member"},{ name: "daniel Dela-Cruz", role: "family member"}],
        status: 'ok',
        reason: 'Unpaid Bills',
        location: 'Blk 3 Lot 12',

        hidden_status: "Deliquent"
    },
    {
        image: 'ChatGPT Image Mar 14, 2026, 05_18_38 PM.png',
        household: 'felippe  Family',
        members: [{ name: "Isha Dela-Cruz", role: "Family member"},{ name: "daniel Dela-Cruz", role: "family member"}],
        status: 'warning',
        reason: 'noisy',
        location: 'Blk 3 Lot 12',
        
        hidden_status: "ok"
    },
      {
        image: 'ChatGPT Image Mar 14, 2026, 05_18_38 PM.png',
        household: 'Paul  Family',
        members: [{ name: "Isha Dela-Cruz", role: "Family member"},{ name: "daniel Dela-Cruz", role: "family member"}],
        status: 'unpaid',
        reason: 'dog bite',
        location: 'Blk 3 Lot 12',

         hidden_status: "ok"
    }
    
];
//.reason.unpaid { color: #f87171; }
//.reason.warning { color: #facc15; }
//.reason.ok,h3.ok,h4.ok { color: #22c55e; }

function generateRes(){
    const container = document.getElementById("residents");
    container.innerHTML="";

    homes.forEach((item,index) => {

        const card = document.createElement('div'); card.className ='res_card yt';
        card.onclick = () => openViewModal(index); //important

        card.innerHTML =`  
      
        <div class="thumb">
            <img src="${item.image}" alt="">
        </div>
        <div class="info">
            <h4>Household: ${item.household}</h4>
            <p>Members: ${item.members.length}</p>
            <p class="reason ${item.status}"> ${item.reason} </p>
            <p class="location"> ${item.location}</p>

       
           
        </div>
  
`;
container.appendChild(card);
    })
    
}

function GenerateMembers(){
    const container = document.getElementById("res_members");
    container.innerHTML = "";

    homes.forEach((home, homeIndex) => {
        home.members.forEach((member, memberIndex) => {

            const card = document.createElement('div');
            card.className = 'res_card yt';

            card.onclick = () => openViewModal_members(homeIndex, memberIndex);

            card.innerHTML = `
                <div class="thumb">
                    <img src="${home.image}" alt="">
                </div>
                <div class="info">
                    <h3 class="ok">${member.name}</h3>
                    <p>${member.role}</p>
                    <p class="location">${home.location}</p>
                </div>
            `;

            container.appendChild(card);
        });
    });
}
window.onload = function () {

    if (document.getElementById("residents")) {
        generateRes();
    }

    if (document.getElementById("res_members")) {
        GenerateMembers();
    }

};


function isDeliquent(homes)
{
homes.hidden_status === "Deliquent";
}

function isokay(homes)
{
homes.hidden_status != "Deliquent" && homes.hidden_status === "ok";
}



/*

homes[]
 ├── home 1
 │     └── members[]
 │           ├── member 1
 │           ├── member 2
 │
 ├── home 2
       └── members[]

*/
function openViewModal(index) {
    const home = homes[index];

    const modal = document.getElementById("viewModal");
    const body = document.getElementById("modalBody");

    // Build members list
    let membersHTML = "";
    home.members.forEach(member => {
        membersHTML += `<li>${member.name} (${member.role})</li>`;
    });

    body.innerHTML = `
        <h2>${home.household}</h2>
        <img src="${home.image}" style="width:100%; border-radius:10px; margin:10px 0;">

        <p><strong>Location:</strong> ${home.location}</p>
        <p><strong>Status:</strong> ${home.reason}</p>

        <h4>Members:</h4>
        <ul>${membersHTML}</ul>
    `;

    modal.classList.add("active");
}
homes.forEach((home, homeIndex) => {
    home.members.forEach((member, memberIndex) => {

        const card = document.createElement('div');
        card.className = 'res_card yt';

        // 🎯 NOW this works
        card.onclick = () => openViewModal_members(homeIndex, memberIndex);

        card.innerHTML = `
            <div class="thumb">
                <img src="${home.image}" alt="">
            </div>
            <div class="info">
                <h3 class="ok">${member.name}</h3>
                <p>${member.role}</p>
                <p class="location">${home.location}</p>
            </div>
        `;

        container.appendChild(card);
    });
});
function closeViewModal() {
    document.getElementById("viewModal").classList.remove("active");
}

function openViewModal_members(homeIndex, memberIndex) {
    const home = homes[homeIndex];
    const member = home.members[memberIndex];

    const modal = document.getElementById("viewModal");
    const body = document.getElementById("modalBody");

    body.innerHTML = `
        <h2>${member.name}</h2>

        <img src="${home.image}" 
             style="width:100%; border-radius:10px; margin:10px 0;">

        <p><strong>Role:</strong> ${member.role}</p>
        <p><strong>Household:</strong> ${home.household}</p>
        <p><strong>Location:</strong> ${home.location}</p>
    `;

    modal.classList.add("active");
}

