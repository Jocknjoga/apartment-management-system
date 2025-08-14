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

// Fetch apartments for filter cards
$apt_sql = "SELECT * FROM apartment ORDER BY apartment_name";
$apt_result = mysqli_query($conn, $apt_sql);

// Fetch house types if an apartment is selected
$type_result = [];
if ($selected_apartment_id) {
    $type_sql = "
      SELECT ht.id, ht.type_name, COUNT(t.id) AS tenant_count
      FROM house_types ht
      LEFT JOIN houses h ON h.type_id = ht.id AND h.apartment_id = $selected_apartment_id
      LEFT JOIN former_tenants t ON t.unit = h.unit
      WHERE ht.apartment_id = $selected_apartment_id
      GROUP BY ht.id, ht.type_name";
    $type_result = mysqli_query($conn, $type_sql);
}

// Pagination setup
$limit = 25; // Records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;


// Build tenant query based on selected filters
$query = "
    SELECT ft.*, apt.apartment_name
    FROM former_tenants ft
    LEFT JOIN houses h ON ft.unit = h.unit
    LEFT JOIN apartment apt ON h.apartment_id = apt.apartment_id
    LEFT JOIN house_types ht ON h.type_id = ht.id
";

$conditions = [];
if ($selected_apartment_id) {
    $conditions[] = "apt.apartment_id = $selected_apartment_id";
}
if ($selected_type_id) {
    $conditions[] = "ht.id = $selected_type_id";
}
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Clone for count
$count_query = "
    SELECT COUNT(*) AS total
    FROM former_tenants ft
    LEFT JOIN houses h ON ft.unit = h.unit
    LEFT JOIN apartment apt ON h.apartment_id = apt.apartment_id
    LEFT JOIN house_types ht ON h.type_id = ht.id
";

if (!empty($conditions)) {
    $count_query .= " WHERE " . implode(" AND ", $conditions);
}

$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_rows = $count_row['total'];
$total_pages = ceil($total_rows / $limit);


$query .= " ORDER BY ft.moved_out_at DESC"; // always keep order at the end
$query .= " LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);



// Handle permanent delete
if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    mysqli_query($conn, "DELETE FROM former_tenants WHERE id = $delete_id");
    echo "<script>alert('Record permanently deleted!'); window.location.href='former_tenant.php';</script>";
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Former Tenants</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    /* Use your existing CSS from before — no changes */
    .show-all-btn { float: right; margin-top: -30px; padding: 8px 14px; background-color: rgb(27, 201, 79); color: white; font-size: 0.9rem; border-radius: 5px; text-decoration: none; }
    .type-card { display: inline-block; padding: 10px 15px; background: #eee; margin: 5px; border-radius: 4px; text-decoration: none; color: #333; }
    .type-card p { margin: 4px 0; font-size: 0.9rem; }
    table { width: 100%; margin-top: 15px; background-color: white; border-collapse: collapse; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); overflow: hidden; }
    thead { background-color: #34495e; color: white; }
    th, td { padding: 14px; text-align: left; font-size: 15px; }
    tbody tr:nth-child(even) { background-color: #f9f9f9; }
    tbody tr:hover { background-color: #f1f1f1; }
    .status {
      text-transform: uppercase;
      font-weight: bold;
      color: red;
    }
    .Active { color: green; } .Pending { color: orange; } .Former, .Moved\ Out { color: red; }
    .dropdown-container { position: relative; display: inline-block; }
    .dots-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: black; }
    .dropdown-menu { display: none; position: absolute; right: 0; top: 100%; background-color: #fff; min-width: 130px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15); z-index: 1; border-radius: 4px; padding: 5px 0; opacity: 0; transform: translateY(10px); transition: all 0.3s ease; }
    .dropdown-menu.show { display: block; opacity: 1; transform: translateY(0); }
    .dropdown-menu.open-up { bottom: 100%; top: auto; margin-bottom: 5px; }
    .dropdown-menu a, .dropdown-menu .delete-link { display: block; padding: 8px 12px; text-decoration: none; color: #333; font-size: 14px; background: none; border: none; width: 100%; text-align: left; cursor: pointer; }
 table, tbody, tr, td {
    position: relative; /* crucial for absolutely positioned dropdowns */
    overflow: visible;
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
      <li><a href="t_tenant.php"><i class="bi bi-people-fill"></i> All Tenants</a></li>
      <li><a href="former_tenant.php"><i class="bi bi-box-arrow-up-right"></i> Former Tenants</a></li>
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
  <h1>
    <?php if ($apartment_name): ?>
      <?= htmlspecialchars($apartment_name) ?>
    <?php else: ?>
      Former Tenants
    <?php endif; ?>
  <br>
  <?php if ($type_name): ?>
      <?= htmlspecialchars($type_name) ?>
    </h1>
  <?php endif; ?>

      <?php if ($selected_apartment_id || $selected_type_id): ?>
        <a href="former_tenant.php" class="show-all-btn">🔄 Show All</a>
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
      <a href="former_tenant.php?apartment_id=<?= $apt['apartment_id'] ?>"
         class="type-card">
        <h4><?= htmlspecialchars($apt['apartment_name']) ?></h4>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

    <!-- House Type Filter (if apartment selected) -->
    <?php if ($selected_apartment_id && mysqli_num_rows($type_result) > 0): ?>
      <div class="type-cards">
        <?php while ($type = mysqli_fetch_assoc($type_result)): ?>
          <a href="former_tenant.php?apartment_id=<?= $selected_apartment_id ?>&type_id=<?= $type['id'] ?>" class="type-card <?= $selected_type_id == $type['id'] ? 'active' : '' ?>">
            <?= $type['type_name'] ?>
            <p>Tenants: <strong><?= $type['tenant_count'] ?></strong></p>
          </a>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>

    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search by name, ID, or unit...">
    </div>

   <table class="table table-bordered" id="reportTable">
      <thead>
        <tr>
          <th>Full Name</th>
          <th>National ID</th>
          <th>Phone</th>
          <th>Unit</th>
          <th>Apartment</th>
          <th>Status</th>
          <th>Moved Out At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="tenantTable">
        <?php while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['national_id']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['unit']) ?></td>
            <td><?= htmlspecialchars($row['apartment_name']) ?></td>
            <td class="status"><?= htmlspecialchars($row['status']) ?></td>
            <td><?= date('d M Y, h:i A', strtotime($row['moved_out_at'])) ?></td>
            <td>
            <div class="dropdown-container">
              <button class="dots-btn">⋮</button>
              <div class="dropdown-menu">
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this tenant?');">
                  <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="delete-link">Delete</button>
                </form>
              </div>
            </div>
          </td>
          </tr>
        <?php endwhile; ?>
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
  </main>
</div>

<script>
document.getElementById("searchInput").addEventListener("keyup", function () {
  const input = this.value.toLowerCase();
  const rows = document.querySelectorAll("#tenantTable tr");

  rows.forEach(row => {
    const name = row.cells[0].textContent.toLowerCase();
    const id = row.cells[1].textContent.toLowerCase();
    const unit = row.cells[3].textContent.toLowerCase();

    row.style.display = (name.includes(input) || id.includes(input) || unit.includes(input)) ? "" : "none";
  });
});

document.querySelectorAll('.dots-btn').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    const menu = this.nextElementSibling;

    document.querySelectorAll('.dropdown-menu').forEach(m => {
      if (m !== menu) m.classList.remove('show', 'open-up');
    });

    if (menu.classList.contains('show')) {
      menu.classList.remove('show', 'open-up');
    } else {
      menu.classList.add('show');
      const rect = menu.getBoundingClientRect();
      const spaceBelow = window.innerHeight - rect.bottom;
      const menuHeight = menu.offsetHeight;
      if (spaceBelow < menuHeight + 10) {
        menu.classList.add('open-up');
      }
    }
  });
});

document.addEventListener('click', () => {
  document.querySelectorAll('.dropdown-menu').forEach(menu => {
    menu.classList.remove('show', 'open-up');
  });
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
