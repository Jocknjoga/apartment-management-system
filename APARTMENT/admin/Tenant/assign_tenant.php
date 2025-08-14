<?php
include '../config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}
$preselected_unit_id = isset($_GET['unit_id']) ? intval($_GET['unit_id']) : null;

// Fetch all vacant houses
$vacant_query = "SELECT id, unit FROM houses WHERE status = 'Vacant'";
$vacant_result = mysqli_query($conn, $vacant_query);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $national_id = mysqli_real_escape_string($conn, $_POST['national_id']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $unit_id = intval($_POST['unit_id']);

    // Get the unit name based on ID
    $unit_query = mysqli_query($conn, "SELECT unit FROM houses WHERE id = $unit_id");
    $unit_row = mysqli_fetch_assoc($unit_query);
    $unit = $unit_row['unit'];

    // Insert tenant
    $insert = "INSERT INTO tenants (name, national_id, phone, unit, status)
               VALUES ('$name', '$national_id', '$phone', '$unit', 'Active')";
    
    if (mysqli_query($conn, $insert)) {
        // Update house status
        mysqli_query($conn, "UPDATE houses SET status = 'Occupied' WHERE id = $unit_id");
        $success = "✅ Tenant assigned successfully!";
    } else {
        $error = "❌ Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assign Tenant</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
      background-color: #f9f9f9;
    }

    h2 {
      color: #2e7d32;
    }

    form {
      background-color: #fff;
      padding: 25px;
      border-radius: 8px;
      max-width: 500px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    input, select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .btn-group {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }

    button[type="submit"],
    .cancel-btn {
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      font-size: 14px;
      text-decoration: none;
      text-align: center;
    }

    button[type="submit"] {
      background-color: #28a745;
      color: white;
    }

    button[type="submit"]:hover {
      background-color: #218838;
    }

    .cancel-btn {
      background-color: #ccc;
      color: #333;
    }

    .cancel-btn:hover {
      background-color: #b3b3b3;
    }

    .message {
      margin-top: 20px;
      font-weight: bold;
    }

    .message.success { color: green; }
    .message.error { color: red; }
  </style>
</head>
<body>

<h2>Assign New Tenant</h2>

<?php if (isset($success)): ?>
  <div class="message success"><?= $success ?></div>
<?php elseif (isset($error)): ?>
  <div class="message error"><?= $error ?></div>
<?php endif; ?>

<form method="POST">
  <label>Full Name:</label>
  <input type="text" name="name" required>

  <label>National ID:</label>
  <input type="text" name="national_id" required>

  <label>Phone:</label>
  <input type="text" name="phone" required>

  <label>Assign to Vacant Unit:</label>
  <select name="unit_id" required>
    <option value="">-- Select Vacant Unit --</option>
    <?php while ($row = mysqli_fetch_assoc($vacant_result)): ?>
      <option value="<?= $row['id'] ?>" <?= ($row['id'] == $preselected_unit_id ? 'selected' : '') ?>>
        <?= $row['unit'] ?>
      </option>
    <?php endwhile; ?>
  </select>

  <div class="btn-group">
    <button type="submit">Assign Tenant</button>
    <a href="../index.php" class="cancel-btn">Cancel</a>
  </div>
</form>

</body>
</html>
