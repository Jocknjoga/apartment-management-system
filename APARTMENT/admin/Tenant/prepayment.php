<?php
include '../Config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}
$selected_tenant_id = isset($_GET['tenant_id']) ? intval($_GET['tenant_id']) : 0;
$tenant_info = null;

// Fetch all tenants for dropdown (FIXED: removed invalid house_id join)
$tenant_list_result = mysqli_query($conn, "SELECT id, name, unit FROM tenants WHERE status = 'Active' ORDER BY name");

// 1. Fetch tenant and unit rent
if ($selected_tenant_id > 0) {
    $tenant_query = mysqli_query($conn, "SELECT name, unit FROM tenants WHERE id = $selected_tenant_id");

    if (mysqli_num_rows($tenant_query) > 0) {
        $tenant_info = mysqli_fetch_assoc($tenant_query);
        $unit = $tenant_info['unit'];

        // Get rent amount based on unit from houses table
        $house_query = mysqli_query($conn, "SELECT rent FROM houses WHERE unit = '" . mysqli_real_escape_string($conn, $unit) . "'");
        if (mysqli_num_rows($house_query) > 0) {
            $house_info = mysqli_fetch_assoc($house_query);
            $rent_amount = $house_info['rent'];
        } else {
            $rent_amount = 0;
        }
    }
}

// 2. Handle prepayment form submission
if (isset($_POST['submit_prepayment']) && $selected_tenant_id > 0) {
    $prepaid_amount = floatval($_POST['amount']);

    if ($prepaid_amount <= 0 || !$tenant_info || $rent_amount <= 0) {
        header("Location: ?tenant_id=$selected_tenant_id&error=1");
        exit;
    }

    $today = date('Y-m-d');
    $month = date('n');
    $year = date('Y');

    // Check if payment for current month exists
    $payment_check = mysqli_query($conn, "SELECT * FROM payments 
        WHERE tenant_id = $selected_tenant_id AND MONTH(payment_date) = $month AND YEAR(payment_date) = $year");

    $payment_status = 'Pending';

    if (mysqli_num_rows($payment_check) > 0) {
        $existing = mysqli_fetch_assoc($payment_check);
        $payment_status = $existing['status'];
    } else {
        // If not found, insert payment record
        mysqli_query($conn, "INSERT INTO payments (tenant_id, amount, payment_date, status) 
            VALUES ($selected_tenant_id, $rent_amount, '$today', 'Pending')");
    }

    $deducted = 0;
    $new_balance = $prepaid_amount;


   // Check total existing partial payments for the month
$partial_sum_q = mysqli_query($conn, "SELECT SUM(amount) AS total_paid FROM partial_payments 
    WHERE tenant_id = $selected_tenant_id AND month = $month AND year = $year");
$partial_sum = mysqli_fetch_assoc($partial_sum_q);
$total_partial_paid = floatval($partial_sum['total_paid'] ?? 0);

$remaining_balance = $rent_amount - $total_partial_paid;

if ($total_partial_paid > 0) {
    if ($prepaid_amount >= $remaining_balance) {
        // Enough to clear remaining rent
        mysqli_query($conn, "UPDATE payments 
            SET status = 'Paid', amount = $rent_amount 
            WHERE tenant_id = $selected_tenant_id 
            AND MONTH(payment_date) = $month AND YEAR(payment_date) = $year");

        // Remove ALL partial payment entries for this month
        mysqli_query($conn, "DELETE FROM partial_payments 
            WHERE tenant_id = $selected_tenant_id AND month = $month AND year = $year");

        $deducted = $remaining_balance;
        $new_balance = $prepaid_amount - $remaining_balance;
    } else {
        // Still not enough, just update partial payments
        mysqli_query($conn, "INSERT INTO partial_payments 
            (tenant_id, amount, balance, rent_expected, payment_date, month, year) 
            VALUES ($selected_tenant_id, $prepaid_amount, " . ($remaining_balance - $prepaid_amount) . ", $rent_amount, '$today', $month, $year)");

        $deducted = $prepaid_amount;
        $new_balance = 0;
    }

} else {
    // Continue with existing logic for fresh payment


    if ($payment_status == 'Pending') {
        if ($prepaid_amount >= $rent_amount) {
            // Full rent covered
            mysqli_query($conn, "UPDATE payments 
                SET status = 'Paid', amount = $rent_amount 
                WHERE tenant_id = $selected_tenant_id AND MONTH(payment_date) = $month AND YEAR(payment_date) = $year");

            $deducted = $rent_amount;
            $new_balance = $prepaid_amount - $rent_amount;
        } else {
            // Not enough to cover full rent
            mysqli_query($conn, "INSERT INTO partial_payments 
                (tenant_id, amount, balance, rent_expected, payment_date, month, year) 
                VALUES ($selected_tenant_id, $prepaid_amount, $prepaid_amount, $rent_amount, '$today', $month, $year)");

            $deducted = $prepaid_amount;
            $new_balance = 0;
        }
    }

    }

    // Handle prepayment balance
    $existing_prepayment = mysqli_query($conn, "SELECT * FROM prepayments WHERE tenant_id = $selected_tenant_id AND status = 'Active'");

    if (mysqli_num_rows($existing_prepayment) > 0) {
        $record = mysqli_fetch_assoc($existing_prepayment);
        $updated_balance = $record['balance'] + $new_balance;

        mysqli_query($conn, "UPDATE prepayments 
            SET balance = $updated_balance, last_deduction = '$today' 
            WHERE id = " . $record['id']);
    } else {
        mysqli_query($conn, "INSERT INTO prepayments 
            (tenant_id, amount, balance, date_paid, last_deduction, status, rent_expected) 
            VALUES ($selected_tenant_id, $prepaid_amount, $new_balance, '$today', '$today', 'Active', $rent_amount)");
    }

   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_prepayment'])) {
    // ... your existing prepayment logic ...

    // After ALL inserts/updates are done successfully:
    header("Location: ?tenant_id=$selected_tenant_id&success=1");
    exit();
}

}
// Delete prepayment
if (isset($_GET['delete_id']) && $selected_tenant_id > 0) {
    $delete_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM prepayments WHERE id = $delete_id AND tenant_id = $selected_tenant_id");
    header("Location: ?tenant_id=$selected_tenant_id&success=deleted");
    exit;
}


// 3. Fetch prepayment records
$prepayments_result = mysqli_query($conn, "SELECT * FROM prepayments WHERE tenant_id = $selected_tenant_id ORDER BY date_paid DESC");
?>

<!-- HTML VIEW STARTS HERE -->
<!DOCTYPE html>
<html>
<head>
    <title>Prepayments</title>
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
            <li><a href="t_tenant.php"><i class="bi bi-people-fill"></i> Back to Tenants</a></li>
        </ul>
    </aside>

    <div class="main">
        <header class="top-header">
            <?php if ($tenant_info): ?>
                <h1><strong>Rent Prepayments For<br></strong> <?= htmlspecialchars($tenant_info['name']) ?> (<?= htmlspecialchars($tenant_info['unit']) ?>)</h1>
            <?php else: ?>
                <h1><strong>Rent Prepayments<br></strong></h1>
            <?php endif; ?>
        </header>

        <?php if (isset($_GET['success'])): ?>
            <div style="color: green;">Prepayment recorded successfully.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div style="color: red;">Invalid tenant or amount.</div>
        <?php endif; ?>

        <!-- Tenant Dropdown -->
        <form method="GET" style="margin: 10px 0;">
            <label for="tenant_id">Select Tenant:</label>
            <select name="tenant_id" onchange="this.form.submit()" style="padding: 8px; width: 250px;">
                <option value="">-- Choose Tenant --</option>
                <?php while ($tenant = mysqli_fetch_assoc($tenant_list_result)): ?>
                    <option value="<?= $tenant['id'] ?>" <?= $selected_tenant_id == $tenant['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tenant['name']) ?> (<?= htmlspecialchars($tenant['unit']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <!-- Prepayment Form -->
        <?php if ($tenant_info): ?>
            <form method="POST" style="margin-top: 10px; background: #f4f4f4; padding: 15px; border-radius: 5px;">
                <label for="amount">Prepayment Amount (Ksh):</label><br>
                <input type="number" name="amount" step="0.01" min="1" required style="padding: 8px; width: 200px;">
                <button type="submit" name="submit_prepayment" style="padding: 8px 15px; background: #27ae60; color: white; border: none; border-radius: 5px;">Submit Prepayment</button>
            </form>
        <?php endif; ?>

        <!-- Prepayment Records Table -->
        <h3><?= $selected_tenant_id ? 'Prepayments for Selected Tenant' : 'All Prepayments' ?></h3>
        <table>
            <thead>
            <tr>
                <th>Date Paid</th>
                <th>Amount</th>
                <th>Balance</th>
                <th>Last Deduction</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($prepayments_result)): ?>
                <tr>
                    <td><?= date("d M Y", strtotime($row['date_paid'])) ?></td>
                    <td>Ksh <?= number_format($row['amount'], 2) ?></td>
                    <td>Ksh <?= number_format($row['balance'], 2) ?></td>
                    <td><?= $row['last_deduction'] ? date("d M Y", strtotime($row['last_deduction'])) : 'N/A' ?></td>
                    <td><?= $row['status'] ?></td>
                    <td>
            <a href="?tenant_id=<?= $selected_tenant_id ?>&delete_id=<?= $row['id'] ?>" 
               onclick="return confirm('Are you sure you want to delete this prepayment?')"
               style="color: red; text-decoration: none; font-weight: bold;">
               🗑 Delete
            </a>
        </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
