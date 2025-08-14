<?php
include '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $delete = mysqli_query($conn, "DELETE FROM completed_requests WHERE id = $id");

    if ($delete) {
        header("Location: completed.php");
        exit();
    } else {
        echo "Failed to delete record: " . mysqli_error($conn);
    }
} else {
    echo "Invalid deletion request.";
}
