//
function openModal() {
    document.getElementById("modal").classList.add("active");
}

function closeModal() {
    document.getElementById("modal").classList.remove("active");
}

/* Close when clicking outside */
window.onclick = function(e) {
    const modal = document.getElementById("modal");
    if (e.target === modal) {
        closeModal();
    }
}

function negativesave(){
    let saving;
   

    if (saving < 0){
          saving = document.getElementById("saving").classList.replace("red")

    }
}
//

let collections = [];
let expenses = [];

// Add collection line
function addline_collect() {
    const input = document.getElementById("collection").value;

    if (!input.includes("-")) {
        alert("Use format: Name - Amount");
        return;
    }

    const parts = input.split("-");
    collections.push({
        name: parts[0].trim(),
        value: parseFloat(parts[1]) || 0
    });

    document.getElementById("collection").value = "";
    updatePreview();
}

// Add expense line
function addline_expense() {
    const input = document.getElementById("expense").value;

    if (!input.includes("-")) {
        alert("Use format: Name - Amount");
        return;
    }

    const parts = input.split("-");
    expenses.push({
        name: parts[0].trim(),
        value: parseFloat(parts[1]) || 0
    });

    document.getElementById("expense").value = "";
    updatePreview();
}


// MAIN LIVE PREVIEW

function updatePreview() {

    const month = document.getElementById("month").value;
    const previous = parseFloat(document.getElementById("previous").value) || 0;

    document.getElementById("p-month").innerText = month || "Month";
    document.getElementById("p-previous").innerText = "₱" + previous;

    // === COLLECTIONS ===
    let collectionHTML = "";
    let totalCollection = 0;

    collections.forEach(item => {
        totalCollection += item.value;
        collectionHTML += `
            <div class="line">
                <span>${item.name}</span>
                <span>₱${item.value}</span>
            </div>
        `;
    });

    document.querySelector("#previewReceipt h4 + .line").outerHTML = collectionHTML || `
        <div class="line"><span>-</span><span>₱0</span></div>
    `;

    // === EXPENSES ===
    let expenseHTML = "";
    let totalExpenses = 0;

    expenses.forEach(item => {
        totalExpenses += item.value;
        expenseHTML += `
            <div class="line">
                <span>${item.name}</span>
                <span>₱${item.value}</span>
            </div>
        `;
    });

    // Replace expense block
    const expenseHeader = document.querySelectorAll("#previewReceipt h4")[1];
    expenseHeader.nextElementSibling.outerHTML = expenseHTML || `
        <div class="line"><span>-</span><span>₱0</span></div>
    `;

    // === SAVINGS ===
    const savings = (previous + totalCollection) - totalExpenses;

    document.getElementById("p-savings").innerText = "₱" + savings;


    
}


// LIVE typing update
document.addEventListener("input", function(e) {
    if (e.target.closest(".form")) {
        updatePreview();
    }
});


function addManualReceipt() {

    const month = document.getElementById("month").value;
    const previous = parseFloat(document.getElementById("previous").value) || 0;

    if (!month) {
        alert("Please enter a month");
        return;
    }

    // === BUILD COLLECTION HTML ===
    let collectionsHTML = "";
    let totalCollection = 0;

    collections.forEach(item => {
        totalCollection += item.value;
        collectionsHTML += `
            <div class="line">
                <span>${item.name}</span>
                <span>₱${item.value}</span>
            </div>
        `;
    });

    // === BUILD EXPENSE HTML ===
    let expensesHTML = "";
    let totalExpenses = 0;

    expenses.forEach(item => {
        totalExpenses += item.value;
        expensesHTML += `
            <div class="line">
                <span>${item.name}</span>
                <span>₱${item.value}</span>
            </div>
        `;
    });

    // === COMPUTE SAVINGS ===
    const savings = (previous + totalCollection) - totalExpenses;

    // === CREATE RECEIPT ===
    const receiptHTML = `
    <div class="receipt">
        <h2 class="title">Financial Report</h2>
        <p class="subtitle">${month}</p>

        <div class="line">
            <span>Previous Balance</span>
            <span>₱${previous}</span>
        </div>

        <h4>Collections</h4>
        ${collectionsHTML}

        <div class="line total">
            <span>Total</span>
            <span>₱${totalCollection}</span>
        </div>

        <h4>Expenses</h4>
        ${expensesHTML}

        <div class="line total">
            <span>Total Expenses</span>
            <span>₱${totalExpenses}</span>
        </div>

        <div class="line savings">
            <span>Savings</span>
            <span>₱${savings}</span>
        </div>

        <hr>

        <div class="signatures">
            <div>
                <p>Prepared by:</p>
                <strong>Admin</strong>
            </div>
            <div>
                <p>Checked by:</p>
                <strong>Admin</strong>
            </div>
        </div>
    </div>
    `;

    // === ADD TO PAGE ===
    const container = document.getElementById("receiptcontainer");
    container.innerHTML += receiptHTML;

    // === RESET STATE ===
    collections = [];
    expenses = [];

    // Clear inputs
    document.getElementById("month").value = "";
    document.getElementById("previous").value = "";

    updatePreview();

    // Close modal
    closeModal();
}




const receiptsData = [
    {
        month: "January 2026",
        previous: "₱3,828.03",
        collections: [
            ["Monthly Dues", "₱49,500.00"],
            ["Pool & Clubhouse", "₱150,560.00"],
            ["Others", "₱3,828.03"]
        ],
        totalCollection: "₱203,888.03",
        expenses: [
            ["Salaries & Cash Advances", "₱82,473.34"],
            ["SSS & PhilHealth", "₱7,130.00"],
            ["Utilities", "₱24,960.39"],
            ["Maintenance", "₱42,802.00"],
            ["Projects", "₱3,356.00"],
            ["Miscellaneous", "₱8,525.55"]
        ],
        totalExpenses: "₱169,247.28",
        savings: "₱34,640.75"
    },

    // Duplicate or add more months here
    {
        month: "February 2026",
        previous: "₱34,640.75",
        collections: [
            ["Monthly Dues", "₱50,000.00"],
            ["Pool & Clubhouse", "₱120,000.00"]
        ],
        totalCollection: "₱170,000.00",
        expenses: [
            ["Utilities", "₱30,000.00"]
        ],
        totalExpenses: "₱150,000.00",
        savings: "₱20,000.00"
    }
];

function generateReceipts() {
    const container = document.getElementById("receiptcontainer");
    container.innerHTML = "";

    receiptsData.forEach(data => {

        let collectionsHTML = "";
        data.collections.forEach(item => {
            collectionsHTML += `
                <div class="line">
                    <span>${item[0]}</span>
                    <span>${item[1]}</span>
                </div>`;
        });

        let expensesHTML = "";
        data.expenses.forEach(item => {
            expensesHTML += `
                <div class="line">
                    <span>${item[0]}</span>
                    <span>${item[1]}</span>
                </div>`;
        });

        const receiptHTML = `
        <div class="receipt">
            <h2 class="title">Financial Report</h2>
            <p class="subtitle">${data.month}</p>

            <div class="line">
                <span>Previous Balance</span>
                <span>${data.previous}</span>
            </div>

            <h4>Collections</h4>
            ${collectionsHTML}

            <div class="line total">
                <span>Total</span>
                <span>${data.totalCollection}</span>
            </div>

            <h4>Expenses</h4>
            ${expensesHTML}

            <div class="line total">
                <span>Total Expenses</span>
                <span>${data.totalExpenses}</span>
            </div>

            <div class="line savings">
                <span>Savings</span>
                <span>${data.savings}</span>
            </div>

            <hr>

            <div class="signatures">
                <div>
                    <p>Prepared by:</p>
                    <strong>Marilou L. Durgados</strong>
                </div>
                <div>
                    <p>Checked by:</p>
                    <strong>Ruby Joy Vinson</strong>
                </div>
            </div>
        </div>
        `;

        container.innerHTML += receiptHTML;
    });
}

// Run on load
window.onload = generateReceipts;




