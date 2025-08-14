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

// ✅ Always fetch tenant details first (so they load even without POST)
$tenant_query = mysqli_query($conn, "SELECT name, unit, apartment_name FROM tenants WHERE id = $tenant_id");
if (!$tenant = mysqli_fetch_assoc($tenant_query)) {
    die("Tenant not found.");
}

// ✅ Process partial payment
if (isset($_POST['add_partial'])) {

    $partial_amount = floatval($_POST['partial_amount']);
    $unit = mysqli_real_escape_string($conn, $tenant['unit']);

    $rent_query = mysqli_query($conn, "SELECT rent FROM houses WHERE unit = '$unit'");
    if ($rent_query && mysqli_num_rows($rent_query) > 0) {
        $rent_data = mysqli_fetch_assoc($rent_query);
        $rent_expected = floatval($rent_data['rent']);

        $month_start = date('Y-m-01');
        $month_end   = date('Y-m-t');

        $total_query = mysqli_query($conn, "
            SELECT SUM(amount) AS total_paid 
            FROM partial_payments 
            WHERE tenant_id = $tenant_id 
            AND DATE(payment_date) BETWEEN '$month_start' AND '$month_end'
        ");
        $total_row = mysqli_fetch_assoc($total_query);
        $total_partial = floatval($total_row['total_paid'] ?? 0);

        $new_total = $total_partial + $partial_amount;
        $balance = max(0, $rent_expected - $new_total);

        if ($new_total > $rent_expected) {
            die("<p style='color:red;'>❌ Cannot accept this amount. It exceeds the expected rent.</p>");
        } else {
            mysqli_query($conn, "
                INSERT INTO partial_payments (tenant_id, amount, balance, rent_expected) 
                VALUES ($tenant_id, $partial_amount, $balance, $rent_expected)
            ");

            $existing_payment = mysqli_query($conn, "
                SELECT id FROM payments 
                WHERE tenant_id = $tenant_id 
                AND MONTH(payment_date) = MONTH(CURRENT_DATE()) 
                AND YEAR(payment_date) = YEAR(CURRENT_DATE())
            ");

            if (mysqli_num_rows($existing_payment) == 0) {
                mysqli_query($conn, "
                    INSERT INTO payments (tenant_id, amount, status, payment_date) 
                    VALUES ($tenant_id, $partial_amount, 'Pending', NOW())
                ");
            } else {
                mysqli_query($conn, "
                    UPDATE payments 
                    SET amount = amount + $partial_amount 
                    WHERE tenant_id = $tenant_id 
                    AND MONTH(payment_date) = MONTH(CURRENT_DATE()) 
                    AND YEAR(payment_date) = YEAR(CURRENT_DATE())
                ");
            }

            if ($balance <= 0) {
                mysqli_query($conn, "
                    UPDATE payments 
                    SET status = 'Paid', amount = $rent_expected 
                    WHERE tenant_id = $tenant_id 
                    AND MONTH(payment_date) = MONTH(CURRENT_DATE()) 
                    AND YEAR(payment_date) = YEAR(CURRENT_DATE())
                ");
            }

            // ✅ Redirect with success message
            header("Location: t_tenant.php?success=Partial+payment+added+successfully");
            exit();
            
            
        }
    }
}

// ✅ Payment History
$payments = mysqli_query($conn, "
    SELECT amount, payment_date, status 
    FROM payments 
    WHERE tenant_id = $tenant_id 
    ORDER BY payment_date DESC
");
if (isset($_GET['delete_partial'])) {
    $delete_id = intval($_GET['delete_partial']);
    mysqli_query($conn, "DELETE FROM partial_payments WHERE id = $delete_id AND tenant_id = $tenant_id");
    header("Location: payment_history.php?tenant_id=$tenant_id");
    exit;
}

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
.success-msg {
    background: #27ae60;
    color: white;
    padding: 10px;
    border-radius: 5px;
    margin-top: 15px;
}
</style>
</head>
<body>
<div class="wrapper">
<aside class="sidebar" id="sidebar">
  <h2>🏠 AptManager</h2>
  <ul>
    <li><a href="../index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    <li><a href="t_tenant.php"><i class="bi bi-people-fill"></i> Back to Tenants</a></li>
  </ul>
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

<!-- ========================= -->
<!-- PARTIAL PAYMENTS SECTION -->
<!-- ========================= -->
<h3 style="margin-top: 40px;">📌 Partial Payments</h3>

<!-- Add Partial Payment Form -->
<form method="POST" style="margin-top: 10px; background: #f4f4f4; padding: 15px; border-radius: 5px;">
    <label><strong>Partial Amount (Ksh):</strong></label><br>
    <input type="number" name="partial_amount" step="0.01" min="1" required style="padding: 8px; width: 200px;">
    <button type="submit" name="add_partial" style="padding: 8px 15px; background: #27ae60; color: white; border: none; border-radius: 5px;">Add Partial Payment</button>
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
</form>


<!-- Display Partial Payment Records -->
<table style="margin-top: 20px;">
    <thead>
        <tr>
            <th>Date</th>
            <th>Amount Paid (Ksh)</th>
            <th>Expected Rent (Ksh)</th>
            <th>Balance (Ksh)</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $partial_q = mysqli_query($conn, "
            SELECT id, amount, rent_expected, balance, payment_date 
 
            FROM partial_payments 
            WHERE tenant_id = $tenant_id 
            ORDER BY payment_date DESC
        ");

        if (mysqli_num_rows($partial_q) > 0):
            while ($p = mysqli_fetch_assoc($partial_q)):
        ?>
        <tr>
            <td><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
            <td><?= number_format($p['amount'], 2) ?></td>
            <td><?= number_format($p['rent_expected'], 2) ?></td>
            <td><?= number_format($p['balance'], 2) ?></td>
             <td>
                <a href="payment_history.php?tenant_id=<?= $tenant_id ?>&delete_partial=<?= $p['id'] ?>" 
                   onclick="return confirm('Are you sure you want to delete this partial payment?')" 
                   style="color: red; text-decoration: none;">🗑 Delete</a>
            </td>
            </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="5">No partial payments found.</td></tr>
        <?php endif; ?>
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



</div>
</div>
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


</body>
</html>
