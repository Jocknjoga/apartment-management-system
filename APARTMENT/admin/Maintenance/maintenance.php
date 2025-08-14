<?php
include '../config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

// Handle form submission for new request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $issue = mysqli_real_escape_string($conn, $_POST['issue']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $date = date('Y-m-d');

    $insert = "INSERT INTO maintenance_requests (unit, issue, status, request_date) 
               VALUES ('$unit', '$issue', '$status', '$date')";
    mysqli_query($conn, $insert);
    header("Location: maintenance.php");
    exit();
}

// Handle marking as completed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_request'])) {
    $id = intval($_POST['request_id']);
    $unit = mysqli_real_escape_string($conn, $_POST['complete_unit']);
    $issue = mysqli_real_escape_string($conn, $_POST['complete_issue']);
    $expense = mysqli_real_escape_string($conn, $_POST['expense_amount']);
    $date = date('Y-m-d');

    $insert = "INSERT INTO completed_requests (unit, issue, expense_amount, completion_date) 
               VALUES ('$unit', '$issue', '$expense', '$date')";
    mysqli_query($conn, $insert);

    // Delete from maintenance_requests using unique id
    mysqli_query($conn, "DELETE FROM maintenance_requests WHERE id = $id");

    header("Location: maintenance.php");
    exit();
}


// Fetch dropdown data
$units_result = mysqli_query($conn, "SELECT unit FROM houses ORDER BY unit ASC");

$types_result = mysqli_query($conn, "SELECT DISTINCT type_name FROM house_types ORDER BY type_name ASC");

// Fetch maintenance requests
$requests_result = mysqli_query($conn, "SELECT * FROM maintenance_requests ORDER BY request_date DESC");


$insertNotif = $conn->prepare("INSERT INTO notifications (tenant_id, title, message) VALUES (?, ?, ?)");
$insertNotif->bind_param("iss", $tenant_id, $title, $message);
$title = "Maintenance Request Received";
$message = "Hi $username, your maintenance request on has been received on " . date("d M Y H:i");
$insertNotif->execute();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Requests</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
    <div class="wrapper">
<aside class="sidebar" id="sidebar">
  <h2>🏠 AptManager</h2>
  <ul>
    <li><a href="../index.php"><span></span> Dashboard</a></li>
     <li class="dropdown">
      <a href="#" class="dropdown-toggle"><span></span>Manage Houses </a>
      <ul class="submenu">
        <li><a href="../Housing/housing.php">All Houses</a></li>
        <li><a href="../Housing/vacant.php">Vacant</a></li>
        <li><a href="../Housing/occupied.php">Occupied</a></li>
        <li><a href="../Housing/house_type.php">House_type</a></li>
        <li><a href="../Housing/add_house.php">➕ Add House</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle"><span></span>Manage Tenants </a>
      <ul class="submenu">
        <li><a href="../Tenant/t_tenant.php"> All Tenants</a></li>
        <li><a href="../Tenant/former_tenant.php"> Former Tenants</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle"><span></span> Payments</a>
      <ul class="submenu">
        <li><a href="../Payment/payment.php"> All Payments</a></li>
        <li><a href="../Payment/overdue.php"> Overdue Payments</a></li>
        <li><a href="../Payment/prep_report.php"><i class="bi bi-alarm-fill"></i> Prepayments</a></li>
        <li><a href="../Payment/payment_report.php"> Payment Reports</a></li>
      </ul>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle"> Maintenance</a>
      <ul class="submenu">
        <li><a href="maintenance.php"> New Request</a></li>
        <li><a href="completed.php"> Completed Requests</a></li>
      </ul>
    </li>
    <li><a href="../notification.php">Send Notification</a></li>
    <li><a href="../Include/users.php">Manage Users</a></li>
     <li><a href="../Include/staff.php">Manage Staff</a></li>
  </ul>
</aside>


<div class="main">
<header class="top-header">
    <h1>Maintenance Requests</h1>
</header>

<button id="showFormBtn" class="mark-paid-btn" style="margin: 20px 0;">➕ Add Request</button>

<div id="formContainer" class="form-section" style="display: none;">
    <h2>Submit New Maintenance Request</h2>
    <form method="POST">
        <label>Unit:</label><br>
        <select name="unit" class="select2" style="width: 100%;" required>
            <option value="">Select Unit</option>
            <?php mysqli_data_seek($units_result, 0); while($row = mysqli_fetch_assoc($units_result)): ?>
                <option value="<?= htmlspecialchars($row['unit']) ?>"><?= htmlspecialchars($row['unit']) ?></option>
            <?php endwhile; ?>
        </select><br><br>

      

        <label>Issue:</label><br>
        <textarea name="issue" rows="3" style="width: 100%;" required></textarea><br><br>

        <label>Status:</label><br>
        <select name="status" required>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
        </select><br><br>

        <button type="submit" name="submit_request" class="mark-paid-btn">Submit Request</button>
    </form>
</div>

<h2>Maintenance Requests Table</h2>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Unit</th>
            <th>Issue</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($requests_result && mysqli_num_rows($requests_result) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($requests_result)): ?>
        <tr>
            <td><?= htmlspecialchars($row['request_date']) ?></td>
            <td><?= htmlspecialchars($row['unit']) ?></td>
            <td><?= htmlspecialchars($row['issue']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
                <?php if ($row['status'] !== 'Completed'): ?>
                    <button class="mark-completed-btn" 
                            data-id="<?= $row['id'] ?>"
                            data-unit="<?= htmlspecialchars($row['unit']) ?>"
                            data-issue="<?= htmlspecialchars($row['issue']) ?>"
                    >✔ Mark as Completed</button>
                <?php else: ?>
                    Completed
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" style="text-align:center;">No maintenance requests found.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<!-- Popup for expense input -->
<div id="expensePopup" style="display:none; position: fixed; top: 30%; left: 50%; transform: translate(-50%, -30%); background: #fff; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.3); border-radius: 8px; z-index: 1000;">
    <h3>Enter Expense Amount</h3>
    <form method="POST">
        <input type="hidden" name="request_id" id="popupId">
        <input type="hidden" name="complete_unit" id="popupUnit">
        <input type="hidden" name="complete_issue" id="popupIssue">
        <label>Expense Amount (KES):</label><br>
        <input type="number" name="expense_amount" required><br><br>
        <button type="submit" name="complete_request" class="mark-paid-btn">Confirm</button>
        <button type="button" id="closePopup" class="mark-paid-btn" style="background: #aaa;">Cancel</button>
    </form>
</div>

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

    $('.mark-completed-btn').on('click', function() {
        const id = $(this).data('id');
        const unit = $(this).data('unit');
        const issue = $(this).data('issue');

        $('#popupId').val(id);
        $('#popupUnit').val(unit);
        $('#popupIssue').val(issue);

        $('#expensePopup').fadeIn();
    });

    $('#closePopup').on('click', function() {
        $('#expensePopup').fadeOut();
    });
});
</script>

<script src="../assets/js/script.js"></script>

</body>
</html>
