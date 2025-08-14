<?php
include '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $category = $_POST['category'];

    $tenant_ids = [];

    // Fetch tenant IDs based on selected category
    if ($category === 'all') {
        $query = mysqli_query($conn, "SELECT id FROM tenants WHERE status = 'Active'");
    } elseif ($category === 'overdue') {
        $current_month = date('Y-m');

        $query = mysqli_query($conn, "
            SELECT tenants.id AS tenant_id
            FROM tenants
            JOIN houses ON tenants.unit = houses.unit
            LEFT JOIN (
                SELECT * FROM payments
                WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$current_month'
            ) AS p ON tenants.id = p.tenant_id
            WHERE tenants.status = 'Active'
            AND (p.id IS NULL OR p.status != 'Paid')
        ");
    } else {
        die("Invalid category");
    }

    // Handle query failure
    if (!$query) {
        die("Query Error: " . mysqli_error($conn));
    }

    // Collect tenant IDs
    while ($row = mysqli_fetch_assoc($query)) {
        $tenant_ids[] = $row['tenant_id'] ?? $row['id'];
    }

    // Insert notifications for each tenant
    $stmt = $conn->prepare("INSERT INTO notifications (tenant_id, title, message) VALUES (?, ?, ?)");
    foreach ($tenant_ids as $tenant_id) {
        $stmt->bind_param("iss", $tenant_id, $title, $message);
        $stmt->execute();
    }

    $stmt->close();

    // Redirect back with success
    header("Location: notification.php?success=1");
    exit();
} else {
    die("Invalid request method.");
}
?>
