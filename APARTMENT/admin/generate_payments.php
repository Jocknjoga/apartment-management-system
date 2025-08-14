<?php
include 'config/db_connect.php';

$current_month = date('Y-m');

// Step 1: Fetch all active tenants
$tenant_query = mysqli_query($conn, "SELECT id, unit FROM tenants WHERE status = 'Active'");

while ($tenant = mysqli_fetch_assoc($tenant_query)) {
    $tenant_id = $tenant['id'];
    $unit = $tenant['unit'];

    // Step 2: Get rent amount from houses table
    $rent_query = mysqli_query($conn, "SELECT rent FROM houses WHERE unit = '$unit'");
    if ($rent_row = mysqli_fetch_assoc($rent_query)) {
        $rent_amount = $rent_row['rent'];
    } else {
        echo "❌ Could not find house details for Tenant ID $tenant_id<br>";
        continue;
    }

    // Step 3: Check if payment exists for this month
    $check_query = mysqli_query($conn, "
        SELECT id FROM payments 
        WHERE tenant_id = $tenant_id 
        AND DATE_FORMAT(payment_date, '%Y-%m') = '$current_month'
    ");

    if (mysqli_num_rows($check_query) == 0) {
        // Step 4: Insert pending payment
        $insert_query = mysqli_query($conn, "
            INSERT INTO payments (tenant_id, amount, status, payment_date)
            VALUES ($tenant_id, $rent_amount, 'Pending', CURDATE())
        ");

        if ($insert_query) {
            echo "✅ Pending payment created for Tenant ID: $tenant_id<br>";
        } else {
            echo "❌ Failed for Tenant ID: $tenant_id - " . mysqli_error($conn) . "<br>";
        }
    }
}

echo "<br>✔️ Expected payments generation complete.";
?>
