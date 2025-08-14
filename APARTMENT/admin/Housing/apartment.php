<?php
include '../config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apartment_name = $_POST['apartment_name'];
    $location = $_POST['location'];
    $landlord = !empty($_POST['landlord']) ? $_POST['landlord'] : null;

    $stmt = $conn->prepare("INSERT INTO apartment (apartment_name, location, landlord_name) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $apartment_name, $location, $landlord);
    $stmt->execute();
    header("Location: apartment.php");
    exit();
}

// Fetch apartments
$result = $conn->query("SELECT * FROM apartment");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Apartments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        .modal {
            display: none;
            position: fixed;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
              z-index: 9999;
        }

        .modal-content {
            background: white;
            padding: 20px;
            width: 400px;
            border-radius: 8px;
        }

        .modal input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        .modal .btn {
            width: 100%;
            padding: 10px;
        }
        .btn {
            width: 100%;
            padding: 10px;
        }
         .btn {
      padding: 10px 20px;
      background-color: #404447ff;
      color: white;
      border: none;
      border-radius: 5px;
      text-decoration: none;
      font-size: 14px;
      transition: background 0.3s;
    }


        .btn-add {
            padding: 10px 20px;
            background: green;
            color: white;
            border: none;
            cursor: pointer;
            margin-bottom: 20px;
        }

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    border-radius: 5px;
    overflow: hidden;
}
th, td {
    padding: 12px;
    text-align: left;
}
thead {
    background-color: #34495e;
    color: white;
}
tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}
table, tbody, tr, td {
    position: relative; /* crucial for absolutely positioned dropdowns */
    overflow: visible;
}

 .dropdown-container { position: relative; display: inline-block; }
    .dots-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: black; }
    .dropdown-menu { display: none; position: absolute; right: 0; top: 100%; background-color: #fff; min-width: 130px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15); z-index: 1; border-radius: 4px; padding: 5px 0; opacity: 0; transform: translateY(10px); transition: all 0.3s ease; }
    .dropdown-menu.show { display: block; opacity: 1; transform: translateY(0); }
    .dropdown-menu.open-up { bottom: 100%; top: auto; margin-bottom: 5px; }
    .dropdown-menu a, .dropdown-menu .delete-link { display: block; padding: 8px 12px; text-decoration: none; color: #333; font-size: 14px; background: none; border: none; width: 100%; text-align: left; cursor: pointer; }
  
</style>
</head>
<body>
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
      <li><a href="../Payment/prep_report.php"><i class="bi bi-bar-chart-line-fill"></i> Prepayments</a></li>
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
        <h1>Manage Apartments</h1>
    </header>

<button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">➕ Add Apartment</button>


<div id="addModal" class="modal">
    <div class="modal-content">
        <h3>Add Apartment</h3>
        <form method="POST">
            <input type="text" name="apartment_name" placeholder="Apartment Name" required>
            <input type="text" name="location" placeholder="Location" required>
            <input type="text" name="landlord" placeholder="Landlord Name (Optional)">
            <button type="submit" class="btn" style="background: green; color: white;">Save</button>
        </form>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Apartment Name</th>
            <th>Location</th>
            <th>Landlord</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['apartment_name']) ?></td>
            <td><?= htmlspecialchars($row['location']) ?></td>
            <td><?= htmlspecialchars($row['landlord_name'] ?? '-') ?></td>
            <td>
            <div class="dropdown-container">
              <button class="dots-btn">⋮</button>
              <div class="dropdown-menu">
                <a href="edit_apartment.php?id=<?= $row['apartment_id'] ?>">Edit</a>
                    <a href="delete_apartment.php?id=<?= $row['apartment_id'] ?>" onclick="return confirm('Are you sure you want to delete this apartment?')">Delete</a>
                </form>
              </div>
            </div>
          </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<script>
    // Close modal when clicking outside of it
    window.onclick = function(event) {
        const modal = document.getElementById('addModal');
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }


    // Dropdown functionality
    
document.querySelectorAll('.dots-btn').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    const menu = this.nextElementSibling;

    // Close other dropdowns
    document.querySelectorAll('.dropdown-menu').forEach(m => {
      if (m !== menu) m.classList.remove('show', 'open-up');
    });

    // Temporarily show invisibly to measure height
    menu.style.visibility = 'hidden';
    menu.style.display = 'block';
    const menuHeight = menu.offsetHeight;
    const buttonRect = this.getBoundingClientRect();
    const spaceBelow = window.innerHeight - buttonRect.bottom;
    const spaceAbove = buttonRect.top;

    // Decide direction
    if (spaceBelow < menuHeight && spaceAbove > menuHeight) {
      menu.classList.add('open-up');
    } else {
      menu.classList.remove('open-up');
    }

    // Restore visibility and show
    menu.style.display = '';
    menu.style.visibility = '';
    menu.classList.toggle('show');
  });
});

// Close dropdown if clicked outside
document.addEventListener('click', () => {
  document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.remove('show', 'open-up'));
});

</script>
<script src="../assets/js/script.js"></script>

</body>
</html>
