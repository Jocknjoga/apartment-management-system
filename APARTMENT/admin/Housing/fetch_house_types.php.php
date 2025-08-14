<?php
include '../config/db_connect.php';

$apartment_id = intval($_GET['apartment_id']);

$sql = "SELECT id, type_name, default_rent 
        FROM house_types 
        WHERE apartment_id = $apartment_id";

$result = mysqli_query($conn, $sql);

$types = [];
while ($row = mysqli_fetch_assoc($result)) {
  $types[] = $row;
}

header('Content-Type: application/json');
echo json_encode($types);
?>
