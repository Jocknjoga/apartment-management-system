<?php
include '../config/db_connect.php';
$tenant_id = intval($_GET['tenant_id']);
$amount = floatval($_GET['amount']);
$date = $_GET['date'];

// Fetch tenant details
$query = "
SELECT tenants.name, tenants.unit, tenants.apartment_name 
FROM tenants 
WHERE tenants.id = '$tenant_id'
";
$result = mysqli_query($conn, $query);
$tenant = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
<title>Payment Receipt</title>
<style>
body { font-family: Arial; padding: 20px; }
.receipt { border: 1px solid #000; padding: 20px; max-width: 400px; margin: auto; }
h2 { text-align: center; }
</style>
</head>
<body>

<div class="receipt">
  <h2>Payment Receipt</h2>
  <p><strong>Tenant:</strong> <?= htmlspecialchars($tenant['name']) ?></p>
  <p><strong>Unit:</strong> <?= htmlspecialchars($tenant['unit']) ?></p>
  <p><strong>Apartment:</strong> <?= htmlspecialchars($tenant['apartment_name']) ?></p>
  <p><strong>Amount Paid:</strong> Ksh <?= number_format($amount, 2) ?></p>
  <p><strong>Date:</strong> <?= date('d M Y', strtotime($date)) ?></p>
  <p><strong>Being Payment to Smart Apt Agency For the Month of:</strong> <?= date('M Y', strtotime($date)) ?></p>
</div>

<script>
window.print();
</script>

</body>
</html>
