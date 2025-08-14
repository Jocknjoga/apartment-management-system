<?php
include '../config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

$apartment_name = "";

if (isset($_GET['apartment_id'])) {
    $apartment_id = intval($_GET['apartment_id']);
    $apt_query = mysqli_query($conn, "SELECT apartment_name FROM apartment WHERE apartment_id = $apartment_id");
    if ($apt_row = mysqli_fetch_assoc($apt_query)) {
        $apartment_name = $apt_row['apartment_name'];
    }
}

$selected_apartment_id = isset($_GET['apartment_id']) ? intval($_GET['apartment_id']) : 0;

// Fetch apartments for filter cards
$apt_sql = "SELECT * FROM apartment ORDER BY apartment_name";
$apt_result = mysqli_query($conn, $apt_sql);

// Build tenant query based on selected filters
$query = "
    SELECT tenants.*, apartment.apartment_name
    FROM tenants
    JOIN houses ON tenants.unit = houses.unit
    JOIN apartment ON houses.apartment_id = apartment.apartment_id
    JOIN house_types ON houses.type_id = house_types.id
";

$conditions = [];
if ($selected_apartment_id) {
    $conditions[] = "apartment.apartment_id = $selected_apartment_id";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}



$query .= " ORDER BY tenants.created_at DESC";
$result = mysqli_query($conn, $query);


// Handle marking as paid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {
    $tenant_id = intval($_POST['tenant_id']);
    $payment_date = date('Y-m-d');
    $current_month = date('Y-m');

    $rent_query = "
        SELECT houses.rent 
        FROM tenants 
        JOIN houses ON tenants.unit = houses.unit 
        WHERE tenants.id = '$tenant_id'
    ";
    $rent_result = mysqli_query($conn, $rent_query);

    if ($rent_row = mysqli_fetch_assoc($rent_result)) {
        $amount = floatval($rent_row['rent']);
    } else {
        die("Error: Could not retrieve rent amount.");
    }

    // Check if payment exists for this month
    $check = "SELECT id FROM payments WHERE tenant_id = '$tenant_id' AND DATE_FORMAT(payment_date, '%Y-%m') = '$current_month'";
    $check_result = mysqli_query($conn, $check);

    if (mysqli_num_rows($check_result) > 0) {
        $update = "UPDATE payments SET amount = '$amount', payment_date = '$payment_date', status = 'Paid' WHERE tenant_id = '$tenant_id' AND DATE_FORMAT(payment_date, '%Y-%m') = '$current_month'";
        mysqli_query($conn, $update);
    } else {
        $insert = "INSERT INTO payments (tenant_id, amount, payment_date, status) VALUES ('$tenant_id', '$amount', '$payment_date', 'Paid')";
        mysqli_query($conn, $insert);
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Handle reversing payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reverse_paid'])) {
    $tenant_id = intval($_POST['tenant_id']);
    $current_month = date('Y-m');
    $delete = "DELETE FROM payments WHERE tenant_id = '$tenant_id' AND status = 'Paid' AND DATE_FORMAT(payment_date, '%Y-%m') = '$current_month'";
    mysqli_query($conn, $delete);

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Filters
$month_filter = isset($_GET['month']) ? mysqli_real_escape_string($conn, $_GET['month']) : '';



// Pagination settings
$limit = 25; // number of records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total records for pagination
$count_sql = "
    SELECT COUNT(*) AS total
    FROM tenants
    JOIN houses ON tenants.unit = houses.unit
    JOIN house_types ON houses.type_id = house_types.id
    JOIN apartment ON houses.apartment_id = apartment.apartment_id
    LEFT JOIN payments AS p ON tenants.id = p.tenant_id
    WHERE tenants.status = 'Active'
";

if ($month_filter) {
    $count_sql .= " AND DATE_FORMAT(p.payment_date, '%Y-%m') = '$month_filter'";
}
if ($selected_apartment_id) {
    $count_sql .= " AND apartment.apartment_id = $selected_apartment_id";
}

$count_result = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Main query
// ✅ Important Fix for SQL Query (updated part only)
$query = "
SELECT 
  tenants.id AS tenant_id,
  tenants.name,
  tenants.unit,
  apartment.apartment_name,
  house_types.type_name,
  houses.rent,
  p.amount,
  p.payment_date,
  p.status
FROM tenants
JOIN houses ON tenants.unit = houses.unit
JOIN house_types ON houses.type_id = house_types.id
JOIN apartment ON houses.apartment_id = apartment.apartment_id
LEFT JOIN (
    SELECT * FROM payments
) AS p ON tenants.id = p.tenant_id
WHERE tenants.status = 'Active'
";
if ($month_filter) {
    $query .= " AND DATE_FORMAT(p.payment_date, '%Y-%m') = '$month_filter'";
}

if ($selected_apartment_id) {
    $query .= " AND apartment.apartment_id = $selected_apartment_id";
}


$query .= " ORDER BY tenants.name ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payments</title>
<link rel="stylesheet" href="../assets/css/style.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


<style>
.type-card {
  display: inline-block;
  padding: 10px 15px;
  background: #eee;
  margin: 5px 5px 15px 0;
  border-radius: 4px;
  text-decoration: none;
  color: #333;
}
.type-card.active {
  background: #28a745;
  color: white;
}
.show-all-btn {
  display: inline-block;
  padding: 8px 14px;
  background-color: rgb(27, 201, 79);
  color: white;
  font-size: 0.9rem;
  border-radius: 5px;
  text-decoration: none;
}
.show-all-btn:hover {
  background-color: #0056b3;
}
.month-filter-section {
  margin: 20px 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.month-filter-section form {
  display: flex;
  align-items: center;
}
.month-filter-section select {
  padding: 6px 12px;
  border-radius: 5px;
  font-size: 0.95rem;
}
table {
  width: 100%;
  border-collapse: collapse;
  background-color: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
}
thead {
  background-color: #34495e;
  color: white;
}
th, td {
  padding: 14px;
  text-align: left;
  font-size: 15px;
}
tbody tr:nth-child(even) {
  background-color: #f9f9f9;
}
tbody tr:hover {
  background-color: #f1f1f1;
}
.status-paid { color: green; font-weight: bold; }
.status-pending { color: orange; font-weight: bold; }
.status-failed { color: red; font-weight: bold; }
.mark-paid-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 6px 10px;
  cursor: pointer;
  border-radius: 4px;
}
.mark-paid-btn:hover {
  background: #0056b3;
}
.dropdown-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  color: black;
}
.dropdown-content {
  display: none;
  position: absolute;
  background: white;
  border: 1px solid #ccc;
  min-width: 100px;
  z-index: 100;
}
.dropdown-content form,
.dropdown-content a {
  display: block;
  width: 100%;
  padding: 5px;
  text-decoration: none;
  color: black;
  background: white;
  text-align: left;
}
.dropdown-content form button {
  width: 100%;
  background: gray;
  color: #fff;
  border: none;
  padding: 5px;
  cursor: pointer;
}
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
  <h1>Payment Summary</h1>

<h1 style="margin-bottom: 10px;">
    <?php if ($apartment_name): ?>
        <?= htmlspecialchars($apartment_name) ?> |
    <?php endif; ?>
    <?php if ($month_filter): ?>
        <?= date('F Y', strtotime($month_filter . '-01')) ?>
    <?php endif; ?>
</h1>
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
<!-- Apartment Filter First -->
<div style="margin: 20px 0; display: flex; flex-wrap: wrap; gap: 10px;">
    <?php
    $apt_sql = "SELECT apartment_id, apartment_name FROM apartment ORDER BY apartment_name";
    $apt_result = mysqli_query($conn, $apt_sql);

    while ($row = mysqli_fetch_assoc($apt_result)):
        $apartment_id = $row['apartment_id'];
        $apartment_name = $row['apartment_name'];
        $isActive = ($selected_apartment_id == $apartment_id) ? 'active' : '';
    ?>
    <a href="payment.php?apartment_id=<?= $apartment_id ?>" class="type-card <?= $isActive ?>">
        <?= htmlspecialchars($apartment_name) ?>
    </a>
    <?php endwhile; ?>
</div>

<!-- Show Month Filter only after apartment is selected -->
<?php if ($selected_apartment_id): ?>
<div class="month-filter-section">
    <form method="get">
        <input type="hidden" name="apartment_id" value="<?= $selected_apartment_id ?>">
        <label for="month">📅 Filter by Month:</label>
        <select name="month" id="month" onchange="this.form.submit()">
            <option value="">-- Select Month --</option>
            <?php 
            $current_year = date('Y');
            $months = [
                '01' => 'January', '02' => 'February', '03' => 'March',
                '04' => 'April', '05' => 'May', '06' => 'June',
                '07' => 'July', '08' => 'August', '09' => 'September',
                '10' => 'October', '11' => 'November', '12' => 'December'
            ];
            foreach ($months as $num => $name):
                $month_val = $current_year . '-' . $num;
                $selected = ($month_filter == $month_val) ? 'selected' : '';
            ?>
            <option value="<?= $month_val ?>" <?= $selected ?>><?= $name . ' ' . $current_year ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($month_filter): ?>
<a href="payment.php" class="show-all-btn">🔄 Show All Payments</a>
    <?php endif; ?>
</div>
<?php endif; ?>

    <!-- Search Box -->
    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search by name, or unit...">
    </div>

  
    <table class="table table-bordered" id="reportTable">

<thead>
<tr>
<th>Tenant</th>
<th>Unit</th>
<th>Apartment</th>
<th>Amount</th>
<th>Payment Date</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if ($result && mysqli_num_rows($result) > 0): ?>
<?php while($row = mysqli_fetch_assoc($result)): 
$status = $row['status'];
$tenant_id = $row['tenant_id'];
$rent = $row['rent'];
?>
<tr>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['unit']) ?></td>
<td><?= htmlspecialchars($row['apartment_name']) ?></td>
<td><?= $status === 'Paid' ? "Ksh " . number_format($row['amount'], 2) : '<span style="color:red;">Ksh ' . number_format($rent, 2) . '</span>' ?></td>
<td><?= $status === 'Paid' ? date('d M Y', strtotime($row['payment_date'])) : '-' ?></td>
<td class="status-<?= strtolower($status) ?>"><?= $status ?? 'Pending' ?></td>
<td>
<?php if ($status !== 'Paid'): ?>
<form method="POST" style="margin:0;">
<input type="hidden" name="tenant_id" value="<?= $tenant_id ?>">
<button type="submit" name="mark_paid" class="mark-paid-btn" onclick="return confirm('Mark this tenant as paid?')">Mark as Paid</button>
</form>
<?php else: ?>
<span style="color:green; font-size:1.2rem;">&#10004;</span>
<div style="display:inline-block; position:relative; margin-left:8px;">
<button class="dropdown-btn">&#8942;</button>
<div class="dropdown-content">
<form method="POST" style="margin:0;">
<input type="hidden" name="tenant_id" value="<?= $tenant_id ?>">
<button type="submit" name="reverse_paid" onclick="return confirm('Reverse this payment?')">Reverse</button>
</form>
<a href="receipt.php?tenant_id=<?= $tenant_id ?>&amount=<?= $row['amount'] ?>&date=<?= $row['payment_date'] ?>" target="_blank">Print</a>
<a href="payment_history.php?tenant_id=<?= $tenant_id ?>">History</a>

</div>
</div>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="8" style="text-align:center;">No payment records found.</td>
</tr>
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


</div>
</div>
<script>
document.querySelectorAll('.dropdown-btn').forEach(btn => {
btn.addEventListener('click', function(e) {
e.stopPropagation();
const dropdown = this.nextElementSibling;
dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
});
});
window.addEventListener('click', () => {
document.querySelectorAll('.dropdown-content').forEach(drop => {
drop.style.display = 'none';
});
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  const table = document.getElementById("reportTable");
  const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

  searchInput.addEventListener("keyup", function () {
    const filter = searchInput.value.toLowerCase();

    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];
      const cells = row.getElementsByTagName("td");
      let match = false;

      for (let j = 0; j < cells.length; j++) {
        const cellText = cells[j].textContent.toLowerCase();
        if (cellText.includes(filter)) {
          match = true;
          break;
        }
      }

      row.style.display = match ? "" : "none";
    }
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
