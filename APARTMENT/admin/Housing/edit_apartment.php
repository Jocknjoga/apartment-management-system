<?php
include '../config/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid apartment ID.");
}

$apartment_id = intval($_GET['id']);

// Fetch the existing apartment data
$stmt = $conn->prepare("SELECT * FROM apartment WHERE apartment_id = ?");
$stmt->bind_param("i", $apartment_id);
$stmt->execute();
$result = $stmt->get_result();
$apartment = $result->fetch_assoc();

if (!$apartment) {
    die("Apartment not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apartment_name = $_POST['apartment_name'];
    $location = $_POST['location'];
    $landlord = !empty($_POST['landlord']) ? $_POST['landlord'] : null;

    $update = $conn->prepare("UPDATE apartment SET apartment_name = ?, location = ?, landlord_name = ? WHERE apartment_id = ?");
    $update->bind_param("sssi", $apartment_name, $location, $landlord, $apartment_id);
    $update->execute();

    header("Location: apartment.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Apartment</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-container {
            width: 400px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.2);
        }
        .form-container h2 {
            margin-bottom: 20px;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            background: #2c3e50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-container a {
            display: block;
            margin-top: 15px;
            text-align: center;
            text-decoration: none;
            color: #2980b9;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Apartment</h2>
    <form method="POST">
        <input type="text" name="apartment_name" placeholder="Apartment Name" value="<?= htmlspecialchars($apartment['apartment_name']) ?>" required>
        <input type="text" name="location" placeholder="Location" value="<?= htmlspecialchars($apartment['location']) ?>" required>
        <input type="text" name="landlord" placeholder="Landlord Name (optional)" value="<?= htmlspecialchars($apartment['landlord_name']) ?>">
        <button type="submit">Update Apartment</button>
    </form>
</div>

</body>
</html>
