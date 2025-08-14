<?php
 include '../config/db_connect.php'; 


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Send Notification</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    .notification-form {
      max-width: 600px;
      margin: 30px auto;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
    }

    .notification-form h2 {
      margin-bottom: 20px;
      color: #34495e;
    }

    .notification-form label {
      font-weight: bold;
      display: block;
      margin-top: 15px;
    }

    .notification-form input,
    .notification-form textarea,
    .notification-form select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .notification-form button {
      margin-top: 20px;
      background: #2ecc71;
      color: white;
      border: none;
      padding: 12px 20px;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
    }

    .notification-form button:hover {
      background: #27ae60;
    }
  </style>
</head>
<body>
<div class="wrapper">
<aside class="sidebar" id="sidebar">
  <h2>🏠 AptManager</h2>
  <ul>
  <li><a href="../admin/index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-building"></i> Manage Houses</a>
    <ul class="submenu">
      <li><a href="Housing/apartment.php"><i class="bi bi-houses-fill"></i> Apartments</a></li>
      <li><a href="Housing/housing.php"><i class="bi bi-houses-fill"></i> All Houses</a></li>
      <li><a href="Housing/vacant.php"><i class="bi bi-door-open"></i> Vacant</a></li>
      <li><a href="Housing/occupied.php"><i class="bi bi-person-check-fill"></i> Occupied</a></li>
      <li><a href="Housing/house_type.php"><i class="bi bi-grid-1x2-fill"></i> House Type</a></li>
      <li><a href="Housing/add_house.php"></i> ➕ Add House</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-person-circle"></i> Manage Tenants</a>
    <ul class="submenu">
      <li><a href="Tenant/t_tenant.php"><i class="bi bi-people-fill"></i> All Tenants</a></li>
      <li><a href="Tenant/former_tenant.php"><i class="bi bi-box-arrow-up-right"></i> Former Tenants</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-cash-stack"></i> Payments</a>
    <ul class="submenu">
      <li><a href="Payment/payment.php"><i class="bi bi-currency-dollar"></i> All Payments</a></li>
      <li><a href="Payment/overdue.php"><i class="bi bi-alarm-fill"></i> Overdue Payments</a></li>
       <li><a href="Payment/prep_report.php"><i class="bi bi-bar-chart-line-fill"></i> Prepayments</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-tools"></i> Maintenance</a>
    <ul class="submenu">
      <li><a href="Maintenance/maintenance.php"><i class="bi bi-plus-square-fill"></i> New Request</a></li>
      <li><a href="Maintenance/completed.php"><i class="bi bi-check2-circle"></i> Completed Requests</a></li>
    </ul>
  </li>

  <li><a href="notification.php"><i class="bi bi-bell-fill"></i> Send Notification</a></li>
  <li><a href="Include/users.php"><i class="bi bi-person-gear"></i> Manage Users</a></li>
  <li><a href="Include/staff.php"><i class="bi bi-person-badge-fill"></i> Manage Staff</a></li>

</ul>

</aside>

  <div class="main">
    <header class="top-header">
      <h1>Send Notification</h1>
    </header>

    <form class="notification-form" action="send_notification.php" method="POST">
      <h2>Create Notification</h2>

      <label for="title">Title</label>
      <input type="text" id="title" name="title" required>

      <label for="message">Message</label>
      <textarea id="message" name="message" rows="5" required></textarea>

      <label for="category">Recipient</label>
      <select id="category" name="category" required>
        <option value="">-- Select Recipient Group --</option>
        <option value="all">All Tenants</option>
        <option value="overdue">Overdue Tenants</option>
      </select>

      <button type="submit">Send Notification</button>
    </form>
  </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
