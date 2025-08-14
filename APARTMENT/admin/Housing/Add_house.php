<?php
include '../config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

$selected_apartment_id = isset($_GET['apartment_id']) ? intval($_GET['apartment_id']) : 0;

// Fetch all apartments
$apartment_query = "SELECT * FROM apartment";
$apartment_result = mysqli_query($conn, $apartment_query);

// Fetch house types filtered by selected apartment
$type_query = "SELECT * FROM house_types";
if ($selected_apartment_id > 0) {
  $type_query .= " WHERE apartment_id = $selected_apartment_id";
}
$type_result = mysqli_query($conn, $type_query);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $apartment_id = intval($_POST['apartment_id']);
  $unit = mysqli_real_escape_string($conn, $_POST['unit']);
  $floor = intval($_POST['floor']);
  $type_id = intval($_POST['type_id']);
  $rent = intval($_POST['rent']);
  $status = 'Vacant';

  $sql = "INSERT INTO houses (unit, floor, type_id, apartment_id, status, rent) 
          VALUES ('$unit', $floor, $type_id, $apartment_id, '$status', $rent)";

  if (mysqli_query($conn, $sql)) {
    echo "<p class='success-msg'>✅ House added successfully!</p>";
  } else {
    echo "<p class='error-msg'>❌ Error: " . mysqli_error($conn) . "</p>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add New House</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>
<body>
  <div class="wrapper">
<aside class="sidebar" id="sidebar">
  <h2>🏠 AptManager</h2>
  <ul>
  <li><a href="../index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-building"></i> Manage Houses</a>
    <ul class="submenu">
      <li><a href="apartment.php"><i class="bi bi-houses-fill"></i> Apartments</a></li>
      <li><a href="housing.php"><i class="bi bi-houses-fill"></i> All Houses</a></li>
      <li><a href="vacant.php"><i class="bi bi-door-open"></i> Vacant</a></li>
      <li><a href="occupied.php"><i class="bi bi-person-check-fill"></i> Occupied</a></li>
      <li><a href="house_type.php"><i class="bi bi-grid-1x2-fill"></i> House Type</a></li>
      <li><a href="add_house.php"></i> ➕ Add House</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-person-circle"></i> Manage Tenants</a>
    <ul class="submenu">
      <li><a href="../Tenant/t_tenant.php"><i class="bi bi-people-fill"></i> All Tenants</a></li>
      <li><a href="../Tenant/former_tenant.php"><i class="bi bi-box-arrow-up-right"></i> Former Tenants</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-cash-stack"></i> Payments</a>
    <ul class="submenu">
      <li><a href="../Payment/payment.php"><i class="bi bi-currency-dollar"></i> All Payments</a></li>
      <li><a href="../Payment/overdue.php"><i class="bi bi-alarm-fill"></i> Overdue Payments</a></li>
      <li><a href="../Payment/payment_report.php"><i class="bi bi-bar-chart-line-fill"></i> Payment Reports</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-tools"></i> Maintenance</a>
    <ul class="submenu">
      <li><a href="../Maintenance/maintenance.php"><i class="bi bi-plus-square-fill"></i> New Request</a></li>
      <li><a href="../Maintenance/completed.php"><i class="bi bi-check2-circle"></i> Completed Requests</a></li>
    </ul>
  </li>

  <li><a href="../notification.php"><i class="bi bi-bell-fill"></i> Send Notification</a></li>
  <li><a href="../Include/users.php"><i class="bi bi-person-gear"></i> Manage Users</a></li>
  <li><a href="../Include/staff.php"><i class="bi bi-person-badge-fill"></i> Manage Staff</a></li>
</ul>

</aside>

    <div class="main">
      <header class="top-header">
        <h1>Add New House</h1>
      </header>

      <form method="POST" class="form-box">
        <label for="apartment_id">Apartment:</label>
        <select name="apartment_id" id="apartment_id" onchange="filterHouseTypes()" required>
          <option value="">-- Select Apartment --</option>
          <?php while ($apt = mysqli_fetch_assoc($apartment_result)): ?>
            <option value="<?= $apt['apartment_id'] ?>" <?= ($apt['apartment_id'] == $selected_apartment_id) ? 'selected' : '' ?>>
              <?= htmlspecialchars($apt['apartment_name']) ?>
            </option>
          <?php endwhile; ?>
        </select>

        <label for="unit">House Unit (e.g. Apt 101):</label>
        <input type="text" name="unit" id="unit" required />

        <label for="floor">Floor:</label>
        <input type="number" name="floor" id="floor" min="0" required />

        <label for="type">Type:</label>
        <select name="type_id" id="type" onchange="updateRent()" required>
          <option value="">-- Select Type --</option>
          <?php while ($row = mysqli_fetch_assoc($type_result)): ?>
            <option value="<?= $row['id'] ?>" data-rent="<?= $row['default_rent'] ?>">
              <?= htmlspecialchars($row['type_name']) ?>
            </option>
          <?php endwhile; ?>
        </select>

        <label for="rent">Monthly Rent (Ksh):</label>
        <input type="number" name="rent" id="rent" min="0" required />

        <button type="submit">➕ Add House</button>
      </form>
    </div>
  </div>

  <script>
    function updateRent() {
      const typeSelect = document.getElementById('type');
      const selectedOption = typeSelect.options[typeSelect.selectedIndex];
      const rent = selectedOption.getAttribute('data-rent');
      if (rent) {
        document.getElementById('rent').value = rent;
      }
    }

    function filterHouseTypes() {
      const apartmentSelect = document.getElementById('apartment_id');
      const selectedApartmentId = apartmentSelect.value;
      window.location.href = "add_house.php?apartment_id=" + selectedApartmentId;
    }
  </script>
  <script src="../assets/js/script.js"></script>
</body>
</html>
