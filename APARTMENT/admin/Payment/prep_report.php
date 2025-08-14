<?php
include '../Config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $new_balance = floatval($_POST['new_balance']);

    $query = "UPDATE prepayments SET balance = ? WHERE tenant_id = (SELECT id FROM tenants WHERE unit = ? LIMIT 1)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ds", $new_balance, $unit);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: prep_report.php?success=1");
    } else {
        echo "Update failed: " . mysqli_error($conn);
    }
}


$apartment_name = "";
$selected_apartment_id = isset($_GET['apartment_id']) ? intval($_GET['apartment_id']) : 0;



// Build the query with optional apartment filter
$query = "
    SELECT 
        t.name AS tenant_name,
        t.unit,
        a.apartment_name,
        ht.type_name,
        p.balance
    FROM prepayments p
    INNER JOIN tenants t ON p.tenant_id = t.id
    LEFT JOIN apartment a ON t.apartment_id = a.apartment_id
    LEFT JOIN house_types ht ON t.house_type = ht.id
    WHERE p.balance > 0
";


$query .= " ORDER BY t.name ASC";

// Pagination setup
$limit = 25; // records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Add LIMIT and OFFSET to the final query
$query .= " LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);


// Get total records for pagination count
$count_query = "
SELECT COUNT(*) as total
FROM tenants
JOIN houses ON tenants.unit = houses.unit
JOIN house_types ON houses.type_id = house_types.id
JOIN apartment ON houses.apartment_id = apartment.apartment_id
LEFT JOIN payments p ON tenants.id = p.tenant_id
";

if (!empty($conditions)) {
    $count_query .= " WHERE " . implode(" AND ", $conditions);
}

$count_result = mysqli_query($conn, $count_query);
$row = mysqli_fetch_assoc($count_result);
$total_records = $row['total'];
$total_pages = ceil($total_records / $limit);


if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prepayment Report</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 5px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        thead {
            background-color: #34495e;
            color: white;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .type-card {
            padding: 10px 15px;
            border: 1px solid #ccc;
            text-decoration: none;
            border-radius: 4px;
            background: #f2f2f2;
        }
        .type-card.active {
            background: #2ecc71;
            color: white;
            font-weight: bold;
        }
        .search-container {
            margin: 10px 0;
        }
        .search-container input {
            padding: 10px;
            width: 300px;
        }

        .icon-button {
    background-color: transparent !important;
    border: none;
    padding: 0;
}

.icon-button:focus, .icon-button:hover {
    background-color: rgba(0, 0, 0, 0.05); /* optional subtle hover effect */
    box-shadow: none;
    outline: none;
}
.icon-button img {
    width: 24px;
    height: 24px;
    vertical-align: middle;
}
.export {
      float: right;
      margin-top: -30px;
      padding: 8px 14px;
      color: white;
      font-size: 0.9rem;
      border-radius: 5px;
      text-decoration: none;
    }



    .dropdown {
    position: relative;
    display: inline-block;
}

.dropbtn {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: black;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 100px;
    box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
    z-index: 1;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.dropdown-content a {
    padding: 10px 14px;
    display: block;
    text-decoration: none;
    color: #333;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.dropdown:hover .dropdown-content {
    display: block;
}

table, tbody, tr, td {
    position: relative; /* crucial for absolutely positioned dropdowns */
    overflow: visible;
}

    </style>
    <style>
#openCalculator:hover {
  background-color: #f0f0f0;
}
</style>

</head>
<body>

<div class="wrapper">
<aside class="sidebar" id="sidebar">
  <h2>🏠 AptManager</h2>
  <ul>
  <li><a href="../index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-building"></i> Manage Houses</a>
    <ul class="submenu">
      <li><a href="../Housing/apartment.php"><i class="bi bi-houses-fill"></i> Apartments</a></li>
      <li><a href="../Housing/housing.php"><i class="bi bi-houses-fill"></i> All Houses</a></li>
      <li><a href="../Housing/vacant.php"><i class="bi bi-door-open"></i> Vacant</a></li>
      <li><a href="../Housing/occupied.php"><i class="bi bi-person-check-fill"></i> Occupied</a></li>
      <li><a href="../Housing/house_type.php"><i class="bi bi-grid-1x2-fill"></i> House Type</a></li>
      <li><a href="../Housing/add_house.php"></i> ➕ Add House</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-person-circle"></i> Manage Tenants</a>
    <ul class="submenu">
      <li><a href="../Tenant/t_tenant.php"><i class="bi bi-people-fill"></i> All Tenants</a></li>
      <li><a href="../Tenant/former_tenant.php"><i class="bi bi-box-arrow-up-right"></i> Former Tenants</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-cash-stack"></i> Payments</a>
    <ul class="submenu">
      <li><a href="payment.php"><i class="bi bi-currency-dollar"></i> All Payments</a></li>
      <li><a href="overdue.php"><i class="bi bi-alarm-fill"></i> Overdue Payments</a></li>
       <li><a href="prep_report.php"><i class="bi bi-bar-chart-line-fill"></i> Prepayments</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-tools"></i> Maintenance</a>
    <ul class="submenu">
      <li><a href="../Maintenance/maintenance.php"><i class="bi bi-plus-square-fill"></i> New Request</a></li>
      <li><a href="../Maintenance/completed.php"><i class="bi bi-check2-circle"></i> Completed Requests</a></li>
    </ul>
  </li>

  <li><a href="../notification.php"><i class="bi bi-bell-fill"></i> Send Notification</a></li>
  <li><a href="../Include/users.php"><i class="bi bi-person-gear"></i> Manage Users</a></li>
  <li><a href="../Include/staff.php"><i class="bi bi-person-badge-fill"></i> Manage Staff</a></li>
</ul>
</aside>

   <div class="main">
        <header class="top-header">
            <h1>Prepayment Report</h1>
             <!-- Calculator Button in Header -->
            <h1><button id="openCalculator" title="Open Calculator" 
  style=" background-color: rgba(0, 0, 0, 0.05); /* optional subtle hover effect */
    box-shadow: none;
    outline: none;
     cursor: pointer;
     float: right;
      margin-top: -30px;
      padding: 8px 10px;
      color: white;
      font-size: 0.9rem;
      border-radius: 5px;
      text-decoration: none;;">
  <img src="../assets/img/math2.png" alt="Calculator" style="width: 24px; height: 24px;">
</button></h1>


             <div class="export">
    <div class="d-flex justify-content-between align-items-center mb-3">
        
    <!-- Export Buttons -->
    <div class="d-flex">
        <button onclick="printTable('reportTable')" class="icon-button" title="Print">
            <img src="https://cdn-icons-png.flaticon.com/512/1497/1497695.png" width="24" alt="Print">
        </button>
        <!-- Export to Excel -->
        <button onclick="exportTableToExcel('reportTable')" class="icon-button me-2" title="Export to Excel">
            <img src="https://cdn-icons-png.flaticon.com/512/732/732220.png" width="24" alt="Excel">
        </button>
    </div>
</div>
      </div>
        </header>

        <!-- Search Box -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search by name or unit..." onkeyup="searchTable()" />
        </div>

        <!-- Prepayment Table -->
        <table class="table table-bordered" id="reportTable">
            <thead>
                <tr>
                    <th>Tenant Name</th>
                    <th>Unit</th>
                    <th>Prepayment Balance (Ksh)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="reportTable">
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['tenant_name']) ?></td>
                        <td><?= htmlspecialchars($row['unit']) ?></td>
                        <td><strong><?= number_format($row['balance'], 2) ?></strong></td>
                        <td>
            <div class="dropdown">
                <button class="dropbtn">⋮</button>
                <div class="dropdown-content">
                    <a href="#" onclick="openEditModal('<?= $row['unit'] ?>', <?= $row['balance'] ?>)">Edit</a>
                </div>
            </div>
        </td>
    
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
  <!-- Calculator Modal -->
<div id="calculatorModal" style="display:none; position:fixed; top:15%; left:50%; transform:translateX(-50%);
background:#fff; border:1px solid #ccc; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.3); z-index:1000;">
  <h4>Calculator</h4>
  <input type="text" id="calcInput" readonly style="width: 100%; margin-bottom: 10px; font-size: 18px; padding: 8px;">
  <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 5px;">
    <button onclick="appendCalc('7')">7</button>
    <button onclick="appendCalc('8')">8</button>
    <button onclick="appendCalc('9')">9</button>
    <button onclick="appendCalc('/')">÷</button>
    <button onclick="appendCalc('4')">4</button>
    <button onclick="appendCalc('5')">5</button>
    <button onclick="appendCalc('6')">6</button>
    <button onclick="appendCalc('*')">×</button>
    <button onclick="appendCalc('1')">1</button>
    <button onclick="appendCalc('2')">2</button>
    <button onclick="appendCalc('3')">3</button>
    <button onclick="appendCalc('-')">−</button>
    <button onclick="appendCalc('0')">0</button>
    <button onclick="appendCalc('.')">.</button>
    <button onclick="calculate()">=</button>
    <button onclick="appendCalc('+')">+</button>
    <button onclick="clearCalc()" colspan="4" style="grid-column: span 4;">C</button>
  </div>
  <button onclick="closeCalculator()" style="margin-top:10px;">x</button>
</div>


<!-- edit module -->

        <div id="editModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);
    background:white; padding:20px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.3); z-index:999;">
    <h3>Edit Prepayment Balance</h3>
    <form method="POST" action="prep_report.php">
        <input type="hidden" name="unit" id="editUnit">
        <label for="newBalance">New Balance:</label>
        <input type="number" step="0.01" name="new_balance" id="newBalance" required><br><br>
        <button type="submit">Update</button>
        <button type="button" onclick="closeEditModal()">Cancel</button>
    </form>
</div>

<div id="modalOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
     background:rgba(0,0,0,0.5); z-index:998;" onclick="closeEditModal()"></div>


         <div class="pagination">
  <?php if ($page > 1): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Prev</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="<?= $page == $i ? 'active' : '' ?>">
      <?= $i ?>
    </a>
  <?php endfor; ?>

  <?php if ($page < $total_pages): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &raquo;</a>
  <?php endif; ?>
</div>

<style>
.pagination {
  text-align: center;
  margin-top: 20px;
}
.pagination a {
  padding: 8px 12px;
  margin: 0 4px;
  border: 1px solid #ccc;
  text-decoration: none;
  color: #333;
  border-radius: 4px;
}
.pagination a.active {
  background-color: #34495e;
  color: white;
  border-color: #34495e;
}
</style>


    </div>
</div>

<script>
// Simple client-side search filter
function searchTable() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll("#reportTable tr");

    rows.forEach(row => {
        const name = row.children[0].textContent.toLowerCase();
        const unit = row.children[1].textContent.toLowerCase();

        if (name.includes(input) || unit.includes(input)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
  // Export to Excel
  function exportTableToExcel(tableID) {
    const table = document.getElementById(tableID);
    const wb = XLSX.utils.table_to_book(table, { sheet: "Report" });
    XLSX.writeFile(wb, "report.xlsx");
  }

  // Print only table
  function printTable(tableID) {
    const table = document.getElementById(tableID);
    const win = window.open('', '', 'height=700,width=900');
    win.document.write('<html><head><title>Print Report</title>');
    win.document.write('<style>table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid black; padding: 8px; }</style>');
    win.document.write('</head><body>');
    win.document.write(table.outerHTML);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    win.print();
    win.close();
  }
</script>
<script>
function openEditModal(unit, balance) {
    document.getElementById("editUnit").value = unit;
    document.getElementById("newBalance").value = balance;
    document.getElementById("editModal").style.display = "block";
    document.getElementById("modalOverlay").style.display = "block";
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
    document.getElementById("modalOverlay").style.display = "none";
}
</script>
<script>
function appendCalc(value) {
  document.getElementById('calcInput').value += value;
}
function clearCalc() {
  document.getElementById('calcInput').value = '';
}
function calculate() {
  try {
    document.getElementById('calcInput').value = eval(document.getElementById('calcInput').value);
  } catch (e) {
    alert('Invalid calculation');
  }
}
function closeCalculator() {
  document.getElementById('calculatorModal').style.display = 'none';
}
document.getElementById('openCalculator').addEventListener('click', function () {
  document.getElementById('calculatorModal').style.display = 'block';
});
</script>

<script src="../assets/js/script.js"></script>
</body>
</html>
