<?php
include '../config/db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE id = $id");
    echo "Marked as read";
}
?>
