<?php
include '../config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

$apartment_name = "";
$type_name = "";

if (isset($_GET['apartment_id'])) {
    $apartment_id = intval($_GET['apartment_id']);
    $apt_query = mysqli_query($conn, "SELECT apartment_name FROM apartment WHERE apartment_id = $apartment_id");
    if ($apt_row = mysqli_fetch_assoc($apt_query)) {
        $apartment_name = $apt_row['apartment_name'];
    }
}

if (isset($_GET['type_id'])) {
    $type_id = intval($_GET['type_id']);
    $type_query = mysqli_query($conn, "SELECT type_name FROM house_types WHERE id = $type_id");
    if ($type_row = mysqli_fetch_assoc($type_query)) {
        $type_name = $type_row['type_name'];
    }
}



$selected_apartment_id = isset($_GET['apartment_id']) ? intval($_GET['apartment_id']) : 0;
$selected_type_id = isset($_GET['type_id']) ? intval($_GET['type_id']) : 0;

// Fetch apartment cards
$apt_sql = "SELECT * FROM apartment ORDER BY apartment_name";
$apt_result = mysqli_query($conn, $apt_sql);

// If apartment selected fetch its house_types WITH vacant/occupied counts


$selected_apartment_id = isset($_GET['apartment_id']) ? (int)$_GET['apartment_id'] : 0;
$selected_type_id = isset($_GET['type_id']) ? (int)$_GET['type_id'] : 0;

// Fetch apartments
$apartment_result = mysqli_query($conn, "SELECT apartment_id, apartment_name FROM apartment");

// Fetch house types
$house_type_result = mysqli_query($conn, "SELECT id, type_name FROM house_types");

// Build WHERE conditions
$conds = ["h.status = 'Vacant'"];
if ($selected_apartment_id) {
    $conds[] = "a.apartment_id = $selected_apartment_id";
}
if ($selected_type_id) {
    $conds[] = "ht.id = $selected_type_id";
}

// Pagination setup
$limit = 25; // records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_sql = "
    SELECT COUNT(*) AS total
    FROM houses h
    JOIN house_types ht ON h.type_id = ht.id
    JOIN apartment a ON h.apartment_id = a.apartment_id
";

if ($conds) {
    $count_sql .= " WHERE " . implode(" AND ", $conds);
}

$count_result = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);




$sql = "
    SELECT 
        h.id AS unit_id, h.unit, h.floor, h.status, h.rent,
        ht.type_name, a.apartment_name
    FROM houses h
    JOIN house_types ht ON h.type_id = ht.id
    JOIN apartment a ON h.apartment_id = a.apartment_id
";

if ($conds) {
    $sql .= " WHERE " . implode(" AND ", $conds);
}

$sql .= " ORDER BY h.unit LIMIT $limit OFFSET $offset";


$table_result = mysqli_query($conn, $sql);
// Fetch house types with count of vacant units for selected apartment
if ($selected_apartment_id) {
    $type_result = mysqli_query($conn, "
        SELECT ht.id, ht.type_name,
            COUNT(h.id) AS vacant_units
        FROM house_types ht
        LEFT JOIN houses h ON h.type_id = ht.id 
            AND h.status = 'Vacant' 
            AND h.apartment_id = $selected_apartment_id
        WHERE ht.apartment_id = $selected_apartment_id
        GROUP BY ht.id, ht.type_name
    ");
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>vacant</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
    .show-all-btn {
      float: right;
      margin-top: -30px;
      padding: 8px 14px;
      background-color: rgb(27, 201, 79);
      color: white;
      font-size: 0.9rem;
      border-radius: 5px;
      text-decoration: none;
    }

    .action-btn {
      padding: 6px 12px;
      background-color: #2980b9;
      color: white;
      border: none;
      border-radius: 5px;
      text-decoration: none;
      font-size: 14px;
      transition: background 0.3s;
    }

    .action-btn:hover {
      background-color: #1c5984;
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
      <li><a href="apartment.php"><i class="bi bi-houses-fill"></i> Apartments</a></li>
      <li><a href="housing.php"><i class="bi bi-houses-fill"></i> All Houses</a></li>
      <li><a href="vacant.php"><i class="bi bi-door-open"></i> Vacant</a></li>
      <li><a href="occupied.php"><i class="bi bi-person-check-fill"></i> Occupied</a></li>
      <li><a href="house_type.php"><i class="bi bi-grid-1x2-fill"></i> House Type</a></li>
      <li><a href="add_house.php"></i> ➕ Add House</a></li>
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
      <li><a href="../Payment/payment.php"><i class="bi bi-currency-dollar"></i> All Payments</a></li>
      <li><a href="../Payment/overdue.php"><i class="bi bi-alarm-fill"></i> Overdue Payments</a></li>
      <li><a href="../Payment/prep_report.php"><i class="bi bi-bar-chart-line-fill"></i> Prepayments</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-tools"></i> Maintenance</a>
    <ul class="submenu">
      <li><a href="../aintenance/maintenance.php"><i class="bi bi-plus-square-fill"></i> New Request</a></li>
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
  <h1>
    <?php if ($apartment_name): ?>
      <?= htmlspecialchars($apartment_name) ?>
    <?php else: ?>
      Vacant Units
    <?php endif; ?>
  <br>

  <?php if ($type_name): ?>
      <?= htmlspecialchars($type_name) ?>
    </h1>
  <?php endif; ?>
      <?php if ($selected_type_id || $selected_apartment_id): ?>
        <a href="vacant.php" class="show-all-btn">🔄 Show All</a>
      <?php endif; ?>

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

    <!-- Apartment filter cards -->
<?php if (!$selected_apartment_id): ?>
  <div class="type-cards">
    <?php foreach ($apt_result as $apt): ?>
      <a href="?apartment_id=<?= $apt['apartment_id'] ?>"
         class="type-card">
        <h4><?= htmlspecialchars($apt['apartment_name']) ?></h4>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>


<!-- Type cards if an apartment is selected -->
<?php if ($selected_apartment_id && mysqli_num_rows($type_result) > 0): ?>
  <div class="type-cards">
    <?php while ($type = mysqli_fetch_assoc($type_result)): ?>
      <a href="?apartment_id=<?= $selected_apartment_id ?>&type_id=<?= $type['id'] ?>"
         class="type-card <?= $selected_type_id === $type['id'] ? 'active' : '' ?>">
        <h4><?= htmlspecialchars($type['type_name']) ?></h4>
        <p style="color:red;"><strong>Vacant:</strong> <?= $type['vacant_units'] ?></p>
    </a>
    <?php endwhile; ?>
  </div>
<?php endif; ?>

    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search by unit or type..."/>
    </div>

    <section class="housing-table">
      <table class="table table-bordered" id="reportTable">
        <thead>
          <tr>
            <th>Apartment</th><th>Unit</th><th>Floor</th><th>Type</th><th>Rent</th><th>Action</th>
          </tr>
        </thead>
        <tbody id="houseTable">
        <?php if ($table_result && mysqli_num_rows($table_result) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($table_result)): ?>
            <tr>
              <td><?= htmlspecialchars($row['apartment_name']) ?></td>
              <td><?= htmlspecialchars($row['unit']) ?></td>
              <td><?= $row['floor'] ?></td>
              <td><?= htmlspecialchars($row['type_name']) ?></td>
              <td>Ksh <?= number_format($row['rent']) ?></td>
            <td>
  <a class="action-btn" href="../Tenant/assign_tenant.php?unit_id=<?= $row['unit_id'] ?>">Assign</a>
</td>

            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" style="text-align:center;">No houses found matching your selections.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
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
    </section>
  </div>
</div>

<script>
  document.getElementById("searchInput").addEventListener("keyup", function () {
    const val = this.value.toLowerCase();
    document.querySelectorAll("#houseTable tr").forEach(tr => {
      const unit = tr.cells[1]?.textContent.toLowerCase() || "";
      const type = tr.cells[3]?.textContent.toLowerCase() || "";
      tr.style.display = (unit.includes(val) || type.includes(val)) ? "" : "none";
    });
  });

  document.querySelectorAll('.dots-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const menu = btn.nextElementSibling;
      document.querySelectorAll('.dropdown-menu').forEach(m => m !== menu && m.classList.remove('show'));
      menu.classList.toggle('show');
    });
  });
  document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
  });
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

<script src="../assets/js/script.js"></script>
</body>
</html>
