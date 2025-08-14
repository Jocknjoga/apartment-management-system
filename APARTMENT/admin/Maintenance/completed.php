<?php
include '../config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

// Pagination settings
$limit = 25; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total records
$count_query = "SELECT COUNT(*) as total FROM completed_requests";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch paginated records
$result = mysqli_query($conn, "SELECT * FROM completed_requests ORDER BY completion_date DESC LIMIT $limit OFFSET $offset");

$total_expenses_result = mysqli_query($conn, "SELECT SUM(expense_amount) AS total_expense FROM completed_requests");
$total_expenses_row = mysqli_fetch_assoc($total_expenses_result);
$total_expenses = $total_expenses_row['total_expense'] ?? 0;



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Completed Maintenance Requests</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
  
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    thead {
      background: #34495e;
      color: #fff;
    }
    th, td {
      padding: 12px;
      text-align: left;
    }
    .total-expense {
      margin-top: 20px;
      padding: 15px;
      background: #ecf0f1;
      border-radius: 5px;
      font-size: 18px;
      font-weight: bold;
    }
    .dropdown-container {
      position: relative;
      display: inline-block;
    }
    .dots-btn {
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      color: black;
    }
    .dropdown-menu {
      display: none;
      position: absolute;
      right: 0;
      top: 100%;
      background-color: #fff;
      min-width: 130px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      z-index: 10;
      border-radius: 5px;
      padding: 5px 0;
    }
    .dropdown-menu.show {
      display: block;
    }
    .dropdown-menu a,
    .dropdown-menu form button {
      display: block;
      padding: 8px 12px;
      text-align: left;
      background: none;
      border: none;
      width: 100%;
      font-size: 14px;
      color: #333;
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
    <li><a href="../index.php"><span></span> Dashboard</a></li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle"><span></span>Manage Houses </a>
      <ul class="submenu">
        <li><a href="../Housing/housing.php">All Houses</a></li>
        <li><a href="../Housing/vacant.php">Vacant</a></li>
        <li><a href="../Housing/occupied.php">Occupied</a></li>
        <li><a href="../Housing/house_type.php">House_type</a></li>
        <li><a href="../Housing/add_house.php">➕ Add House</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle"><span></span>Manage Tenants </a>
      <ul class="submenu">
        <li><a href="../Tenant/t_tenant.php"> All Tenants</a></li>
        <li><a href="../Tenant/former_tenant.php"> Former Tenants</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle"><span></span> Payments</a>
      <ul class="submenu">
        <li><a href="../Payment/payment.php"> All Payments</a></li>
        <li><a href="../Payment/overdue.php"> Overdue Payments</a></li>
        <li><a href="../Payment/prep_report.php"><i class="bi bi-bar-chart-line-fill"></i> Prepayments</a></li>
        
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle"> Maintenance</a>
      <ul class="submenu">
         <li><a href="maintenance.php"> New Request</a></li>
         <li><a href="completed.php"> Completed Requests</a></li>
      </ul>
    </li>
    <li><a href="../notification.php">Send Notification</a></li>
  <li><a href="../Include/users.php">Manage Users</a></li>
  <li><a href="../Include/staff.php">Manage Staff</a></li>
  </ul>
</aside>

<div class="main">
  <header class="top-header">
    <h1>Completed Maintenance Requests</h1>

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

  <h2>Track Expenses</h2>

  <table class="table table-bordered" id="reportTable">
    <thead>
      <tr>
        <th>Completion Date</th>
        <th>Unit</th>
        <th>Issue</th>
        <th>Expense Amount (KES)</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= htmlspecialchars($row['completion_date']) ?></td>
            <td><?= htmlspecialchars($row['unit']) ?></td>
            <td><?= htmlspecialchars($row['issue']) ?></td>
            <td><?= number_format($row['expense_amount'], 2) ?></td>
            <td>
              <div class="dropdown-container">
                <button class="dots-btn">⋮</button>
                <div class="dropdown-menu">
                  <a href="edit_completed.php?id=<?= $row['id'] ?>">Edit</a>
                  <form method="POST" action="delete_completed.php" onsubmit="return confirm('Delete this record?');">
                   <input type="hidden" name="id" value="<?= $row['id'] ?>">
                     <button type="submit" style="background: none; border: none; padding: 8px 12px; cursor: pointer;">Delete</button>
                   </form>

                </div>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="5" style="text-align:center;">No completed maintenance requests found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="total-expense">
    Total Maintenance Expenses: KES <?= number_format($total_expenses, 2) ?>
  </div>
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
  document.querySelectorAll('.dots-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      const menu = this.nextElementSibling;
      document.querySelectorAll('.dropdown-menu').forEach(m => {
        if (m !== menu) m.classList.remove('show');
      });
      menu.classList.toggle('show');
    });
  });

  document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
      menu.classList.remove('show');
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
