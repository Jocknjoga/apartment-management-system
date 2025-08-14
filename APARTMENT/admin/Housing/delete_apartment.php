<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Config/db_connect.php';

if (isset($_GET['id'])) {
    echo "Reached delete script<br>";

    $apartment_id = intval($_GET['id']);
    echo "Apartment ID: $apartment_id<br>";

    // ✅ Corrected column name: apartment_id
    $check_sql = "SELECT apartment_id FROM apartment WHERE apartment_id = $apartment_id";
    $check = mysqli_query($conn, $check_sql);

    if (!$check) {
        die("Error: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($check) == 0) {
        echo "<script>alert('Apartment not found!'); window.location.href='apartment.php';</script>";
        exit();
    }

    // ✅ Corrected column name: apartment_id
    $delete_sql = "DELETE FROM apartment WHERE apartment_id = $apartment_id";
    $delete = mysqli_query($conn, $delete_sql);

    if ($delete) {
        echo "<script>alert('Apartment deleted successfully!'); window.location.href='apartment.php';</script>";
    } else {
        echo "<script>alert('Delete failed: " . mysqli_error($conn) . "'); window.location.href='apartment.php';</script>";
    }

} else {
    echo "No ID was provided!";
    exit();
}
