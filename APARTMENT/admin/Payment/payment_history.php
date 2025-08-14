<?php
include '../config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

if (!isset($_GET['tenant_id'])) {
    die("Invalid Request");
}

$tenant_id = intval($_GET['tenant_id']);

// Get Tenant Info
$tenant_query = mysqli_query($conn, "SELECT name, unit, apartment_name FROM tenants WHERE id = $tenant_id");
if (!$tenant = mysqli_fetch_assoc($tenant_query)) {
    die("Tenant not found.");
}

// Get Payment History
$payments = mysqli_query($conn, "
    SELECT amount, payment_date, status 
    FROM payments 
    WHERE tenant_id = $tenant_id 
    ORDER BY payment_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment History - <?= htmlspecialchars($tenant['name']) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
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
</style>
</head>
<body>
    <div class="wrapper">
<aside class="sidebar" id="sidebar">
  <h2>🏠 AptManager</h2>
  <ul>
  <li><a href="../index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
  <li><a href="payment.php"><i class="bi bi-people-fill"></i> Back to Payments</a></li>
</aside>

<div class="main">
<header class="top-header">
        <h1><strong>Payment History<br></strong> <?= htmlspecialchars($tenant['name']) ?></h1>
   
    </header>
<h2>Unit: <?= htmlspecialchars($tenant['unit']) ?> | Apartment: <?= htmlspecialchars($tenant['apartment_name']) ?></h2>

<?php
$summary = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) AS total_paid,
        SUM(CASE WHEN status = 'Pending' THEN amount ELSE 0 END) AS total_pending,
        COUNT(*) AS total_transactions
    FROM payments 
    WHERE tenant_id = $tenant_id
"));
?>
<div style="margin-top:15px; background:#ecf0f1; padding:15px; border-radius:5px;">
    <strong>Total Paid:</strong> Ksh <?= number_format($summary['total_paid'], 2) ?> |
    <strong>Pending:</strong> Ksh <?= number_format($summary['total_pending'], 2) ?> |
    <strong>Transactions:</strong> <?= $summary['total_transactions'] ?>
</div>



<table>
<thead>
<tr>
    <th>Date</th>
    <th>Amount (Ksh)</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
<?php if (mysqli_num_rows($payments) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($payments)): ?>
    <tr>
        <td><?= date('d M Y', strtotime($row['payment_date'])) ?></td>
        <td><?= number_format($row['amount'], 2) ?></td>
        <td style="color: <?= $row['status'] == 'Paid' ? 'green' : ($row['status'] == 'Pending' ? 'orange' : 'red') ?>;">
            <?= $row['status'] ?>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="3">No payment records found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

</body>
</html>
