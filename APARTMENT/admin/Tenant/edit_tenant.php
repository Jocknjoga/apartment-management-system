<?php
include '../config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

// Redirect if no ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: t_tenant.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch tenant data
$result = mysqli_query($conn, "SELECT * FROM tenants WHERE id = $id");
if (!$result || mysqli_num_rows($result) === 0) {
    echo "Tenant not found.";
    exit();
}

$tenant = mysqli_fetch_assoc($result);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $national_id = mysqli_real_escape_string($conn, $_POST['national_id']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "UPDATE tenants 
            SET name='$name', national_id='$national_id', phone='$phone', status='$status' 
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: t_tenant.php");
        exit();
    } else {
        $error = "❌ Failed to update tenant: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Tenant</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .form-box {
      max-width: 500px;
      margin: 50px auto;
      padding: 30px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .form-box h2 {
      margin-bottom: 20px;
      color: #34495e;
    }
    .form-box label {
      display: block;
      margin-top: 15px;
    }
    .form-box input, .form-box select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .form-box button {
      margin-top: 20px;
      background-color: #27ae60;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .form-box button:hover {
      background-color: #1e8449;
    }
    .error {
      color: red;
      margin-top: 10px;
    }
  </style>
</head>
<body>

<div class="form-box">
  <h2>Edit Tenant</h2>
  
  <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

  <form method="POST">
    <label for="name">Full Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($tenant['name']) ?>" required>

    <label for="national_id">National ID</label>
    <input type="text" name="national_id" value="<?= htmlspecialchars($tenant['national_id']) ?>" required>

    <label for="phone">Phone</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($tenant['phone']) ?>" required>

    <label for="status">Status</label>
    <select name="status" required>
      <option value="Active" <?= $tenant['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
      <option value="Pending" <?= $tenant['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
    </select>

    <button type="submit" name="update">Update Tenant</button>
  </form>
</div>

</body>
</html>
