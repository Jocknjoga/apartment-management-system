<?php
session_start();
include '../config/db_connect.php';

$tenant_id = $_SESSION['tenant_id'] ?? null;
$notif_id = $_POST['id'] ?? null;

if ($tenant_id && $notif_id) {
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND tenant_id = ?");
    $stmt->bind_param("ii", $notif_id, $tenant_id);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
}
?>
