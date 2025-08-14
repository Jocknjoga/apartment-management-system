<?php
include '../config/db_connect.php';
session_start();

// Ensure tenant is logged in
if (!isset($_SESSION['tenant_id'])) {
    die("Access Denied");
}

$tenant_id = $_SESSION['tenant_id'];

// Fetch the unit and name of the logged-in tenant
$tenant_unit = '';
$tenant_name = '';
$tenant_query = mysqli_query($conn, "SELECT unit, name FROM tenants WHERE id = $tenant_id LIMIT 1");
if ($tenant_row = mysqli_fetch_assoc($tenant_query)) {
    $tenant_unit = $tenant_row['unit'];
    $tenant_name = $tenant_row['name'];
}

// Handle form submission for new request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $unit = mysqli_real_escape_string($conn, $tenant_unit);
    $issue = mysqli_real_escape_string($conn, $_POST['issue']);
    $status = 'In Progress';
    $date = date('Y-m-d');

    // Insert into maintenance_requests
    $insert = "INSERT INTO maintenance_requests (unit, issue, status, request_date) 
               VALUES ('$unit', '$issue', '$status', '$date')";
    mysqli_query($conn, $insert);

    // Insert into tenant_requests for tenant history
    $insert_tenant = "INSERT INTO tenant_requests (unit, issue, status, request_date) 
                      VALUES ('$unit', '$issue', '$status', '$date')";
    mysqli_query($conn, $insert_tenant);

    // Send Notification
    $title = "Maintenance Request Received";
    $message = "Hi $tenant_name, your maintenance request has been received on " . date("d M Y H:i");
    $insertNotif = $conn->prepare("INSERT INTO notifications (tenant_id, title, message) VALUES (?, ?, ?)");
    $insertNotif->bind_param("iss", $tenant_id, $title, $message);
    $insertNotif->execute();

    header("Location: maintenance.php");
    exit();
}

// Fetch in-progress requests from maintenance_requests
$in_progress_result = mysqli_query($conn, "SELECT * FROM maintenance_requests WHERE unit = '$tenant_unit'");

// Fetch completed requests from completed_requests
$completed_result = mysqli_query($conn, "SELECT * FROM completed_requests WHERE unit = '$tenant_unit'");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Requests</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .form-section {
            background: #fff;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        thead {
            background: #34495e;
            color: #fff;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
    </style>
</head>
<body>
  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

  <!-- Sidebar -->
   <div class="sidebar" id="sidebar">
    <h2>🏠 AptTenant</h2>
    <a href="index.php">Home</a>
    <a href="unit_info.php">Unit Info</a>
    <a href="maintenance.php">Maintenance</a>
    <a href="../logout.php">Logout</a>
  </div>

<div class="main">
<header class="top-header">
    <h1>Maintenance Requests</h1>
</header>

<button id="showFormBtn" class="mark-paid-btn" style="margin: 20px 0;">➕ Add Request</button>

<div id="formContainer" class="form-section" style="display: none;">
    <h2>Submit New Maintenance Request</h2>
    <form method="POST">
       <label>Unit:</label><br>
       <input type="text" name="unit_display" value="<?= htmlspecialchars($tenant_unit) ?>" readonly style="width: 100%; background-color: #eee; border: 1px solid #ccc;"><br><br>
       <label>Issue:</label><br>
       <textarea name="issue" rows="3" style="width: 100%;" required></textarea><br><br>
       <input type="hidden" name="unit" value="<?= htmlspecialchars($tenant_unit) ?>">

       <button type="submit" name="submit_request" class="mark-paid-btn">Submit Request</button>
    </form>
</div>

<h2>Maintenance Requests Table</h2>
<table>
    <thead>
        <tr>
        
            <th>Unit</th>
            <th>Issue</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $has_data = false;

    // In-progress requests
    if ($in_progress_result && mysqli_num_rows($in_progress_result) > 0) {
        while ($row = mysqli_fetch_assoc($in_progress_result)) {
            $has_data = true;
            echo "<tr>
                    <td>" . htmlspecialchars($row['unit']) . "</td>
                    <td>" . htmlspecialchars($row['issue']) . "</td>
                    <td>" . htmlspecialchars($row['status']) . "</td>
                  </tr>";
        }
    }

    // Completed requests
    if ($completed_result && mysqli_num_rows($completed_result) > 0) {
        while ($row = mysqli_fetch_assoc($completed_result)) {
            $has_data = true;
            echo "<tr>
                    <td>" . htmlspecialchars($row['unit']) . "</td>
                    <td>" . htmlspecialchars($row['issue']) . "</td>
                    <td><span style='color: green; font-weight: bold;'>Completed</span></td>
                  </tr>";
        }
    }

    if (!$has_data) {
        echo "<tr><td colspan='4' style='text-align:center;'>No maintenance requests found.</td></tr>";
    }
    ?>
    </tbody>
</table>

</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2();

    $('#showFormBtn').on('click', function() {
        $('#formContainer').slideToggle();
    });
});
</script>

</body>
</html>
