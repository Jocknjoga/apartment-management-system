<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['tenant_name']) || $_SESSION['role'] != 'tenant') {
    header("Location: ../reg.php");
    exit();
}

$username = $_SESSION['tenant_name'];

// Fetch tenant info
$stmt = $conn->prepare("SELECT * FROM tenants WHERE name = ? AND status = 'Active'");
if (!$stmt) {
    die("Tenant query failed: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$tenant = $result->fetch_assoc();

if (!$tenant) {
    die("Tenant not found or not active.");
}

// Fetch house info
$unit = $tenant['unit'];
$unitStmt = $conn->prepare("
    SELECT h.unit, h.rent, ht.type_name 
    FROM houses h
    JOIN house_types ht ON h.type_id = ht.id
    WHERE h.unit = ?
");
if (!$unitStmt) {
    die("Unit query failed: " . $conn->error);
}
$unitStmt->bind_param("s", $unit);
$unitStmt->execute();
$unitResult = $unitStmt->get_result();
$unitInfo = $unitResult->fetch_assoc();

if (!$unitInfo) {
    die("Unit details not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Unit Information</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #e9ecef;
      margin: 0;
      padding: 0;
    }

    header {
      position: sticky;
      top: 0;
      width: 100%;
      background: #2c3e50;
      color: white;
      text-align: center;
      padding: 20px;
      z-index: 999;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    header h1 {
      margin: 0;
      font-size: 24px;
    }

    header p {
      margin: 5px 0 0;
      font-size: 16px;
      color:rgb(183, 178, 178);
    }

    .main-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 20px;
    }

    .unit-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      width: 800px;
      transition: transform 0.3s;
    
    }
    

    .unit-card:hover {
      transform: scale(1.02);
    }

    .unit-card h2 {
      background: #2c3e50;
      color: white;
      padding: 20px;
      border-top-left-radius: 10px;
      border-top-right-radius: 10px;
      margin: 0;
      font-size: 22px;
    }

    .unit-table {
      width: 100%;
      border-collapse: collapse;
    }

    .unit-table td {
      padding: 15px 20px;
      border-bottom: 1px solid #f1f1f1;
    }

    .unit-table td.label {
      font-weight: bold;
      width: 40%;
      background: #f9f9f9;
    }

    .back-btn {
      margin-top: 20px;
      display: inline-block;
      text-decoration: none;
      background: #2c3e50;
      color: white;
      padding: 10px 20px;
      border-radius: 6px;
      transition: background 0.3s;
    }

    .back-btn:hover {
      background: rgb(2, 23, 43);
    }

    @media (max-width: 640px) {
      .unit-card {
        width: 90%;
      }

      header h1 {
        font-size: 20px;
      }

      header p {
        font-size: 14px;
      }
    }
      .main-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 20px;
    }

    .unit-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      width: 800px;
      transition: transform 0.3s;
    
    }
  </style>
</head>
<body>
 

<header>
  <h1>Unit Information Page </h1>
</header>

<div class="main-content">
  <div class="unit-card">
    <h2>🏘️ Your Unit Information</h2>
    <table class="unit-table">
      <tr>
        <td class="label">Tenant Name:</td>
        <td><?= htmlspecialchars($tenant['name']) ?></td>
      </tr>
      <tr>
        <td class="label">House Type:</td>
        <td><?= htmlspecialchars($unitInfo['type_name']) ?></td>
      </tr>
      <tr>
        <td class="label">Unit:</td>
        <td><?= htmlspecialchars($unitInfo['unit']) ?></td>
      </tr>
      <tr>
        <td class="label">Monthly Rent:</td>
        <td>Ksh <?= number_format($unitInfo['rent'], 2) ?></td>
      </tr>
      <tr>
        <td class="label">Assigned On:</td>
        <td><?= date("d M Y", strtotime($tenant['created_at'])) ?></td>
      </tr>
    </table>
  </div>

  <a href="index.php" class="back-btn">Home</a>
</div>
  <script src="chat.js"></script>
</body>
</html>
