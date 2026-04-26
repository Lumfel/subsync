<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Finance - Subsync</title>
<link rel="stylesheet" href="styles.css">
</head>

<body>

<div class="container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <img src="dashbaord_2.svg">
        </div>

        <ul>
             <li><a href="/">Dashboard</a></li>
            <li class="active"><a href="/finance">Finance</a></li>
            <li><a href="/manage_users">Manage Users</a></li>
            <li><a href="/deliquents">Deliquents</a></li>
            <li><a href="/analytics">Analytics</a></li>
            <li><a href="/reports">Reports</a></li>
            <li><a href="/mapping"> Maps</a></li>
        </ul>

        <hr><br>
        <h2>Terra Nova</h2>
    </aside>

    <!-- Main -->
    <main class="main">


        <header class="topbar">
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            <input type="text" placeholder="Search...">
            <div>Admin</div>
        </header>
        <div id="modal" class="modal">

    <div class="modal-content split">

        <!-- LEFT: LIVE PREVIEW -->
        <div class="preview">

            <div class="receipt" id="previewReceipt">

                <h2 class="title">Financial Report</h2>
                <p class="subtitle" id="p-month">Month</p>

                <div class="line">
                    <span>Previous Balance</span>
                    <span id="p-previous">₱0</span>
                </div>

                <h4>Collections</h4>
                <div class="line">
                    <span id="p-collection-name">-</span>
                    <span id="p-collection-value">₱0</span>
                </div>

                <h4>Expenses</h4>
                <div class="line">
                    <span id="p-expense-name">-</span>
                    <span id="p-expense-value">₱0</span>
                </div>

                <div class="line savings">
                    <span>Savings</span>
                    <span id="p-savings">₱0</span>
                </div>

            </div>

        </div>

        <!-- RIGHT: INPUT FORM -->
        <div class="form_container">
        <div class="form">

            <span class="close" onclick="closeModal()">✖</span>

            <h3>Add Financial Report</h3>

            <input type="month" id="month" placeholder="Month">
            <input type="text" id="previous" placeholder="Previous Balance">
           

        

            <button onclick="addManualReceipt()">Add Receipt</button>

        </div>
        <br>
        <div class="form">
            <h3>Add collection line</h3>
             <input type="text" id="collection" placeholder="Collection (Name - Amount)">
            <button onclick="addline_collect()">Add</button>

        </div>
        <br>
        <br>
        <div class="form">
            <h3>Add Expense line</h3>
             <input type="text" id="expense" placeholder="Expense (Name - Amount)">
            <button onclick="addline_expense()">Add</button>   

        </div>
        
        </div>

    </div>

</div>
      


        <!-- SUMMARY -->
        <section class="cards-main">
            <div class="card">
                <h3>Total Collected</h3>
                <p>₱120,000</p>
            </div>
            <div class="card">
                <h3>Total Expenses</h3>
                <p>₱45,000</p>
            </div>
            <div class="card">
                <h3>Balance</h3>
                <p>₱75,000</p>
            </div>
        </section>
        <section class="cards">
            <div class="card">
            <h3>Upload Excel File</h3>
            <input type="file" id="excelFile" accept=".xlsx, .xls">
            <button onclick="uploadExcel()">Upload</button>
            <p id="fileName"></p>
            </div>
            <div class="cards-main">
            <div class="card" style="cursor: pointer;" onclick="openModal()">
            <h1>Manual</h1>
            </div>
            </div>
            
        </section>


<div class="receipt-container" id="receiptcontainer">

    <section class="receipt">
        <!-- your receipt content -->
    </section>

</div>
<br>



        <!-- UPLOAD -->

        <!-- TABLE -->
        <section class="panel">
            <h3>Records</h3>

            <table id="excelTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

        </section>

    </main>

</div>

<!-- Excel Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
function toggleSidebar() {
    document.querySelector(".sidebar").classList.toggle("active");
}

function uploadExcel() {
    const file = document.getElementById("excelFile").files[0];

    if (!file) {
        alert("Select a file first");
        return;
    }

    document.getElementById("fileName").innerText = file.name;

    const reader = new FileReader();

    reader.onload = function(e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: "array" });

        const sheet = workbook.Sheets[workbook.SheetNames[0]];
        const json = XLSX.utils.sheet_to_json(sheet);

        const tbody = document.querySelector("#excelTable tbody");
        tbody.innerHTML = "";

        json.forEach(row => {
            tbody.innerHTML += `
                <tr>
                    <td>${row.Name || ""}</td>
                    <td>₱${row.Amount || ""}</td>
                    <td>${row.Date || ""}</td>
                </tr>
            `;
        });
    };

    reader.readAsArrayBuffer(file);
}
</script>

<script>
function scrollReceipts(direction) {
    const container = document.querySelector('.receipt-container');
    const scrollAmount = 420; // matches receipt width

    container.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}
</script>

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/analysis.js') }}"></script>
<script src="{{ asset('js/d3.v7.min.js') }}"></script>
<script src="{{ asset('js/finance_script.js') }}"></script>
<script src="{{ asset('js/household_and_deliquent.js') }}"></script>

</body>
</html>