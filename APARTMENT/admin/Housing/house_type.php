
<?php
include '../config/db_connect.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

$message = "";

// Fetch apartment list for dropdown
$apartment_result = mysqli_query($conn, "SELECT * FROM apartment ORDER BY apartment_name ASC");

// Delete House Type
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $check_houses = mysqli_query($conn, "SELECT * FROM houses WHERE type_id = $id LIMIT 1");
    if (mysqli_num_rows($check_houses) > 0) {
        $message = "⚠️ Cannot delete: house type is in use.";
    } else {
        $delete = mysqli_query($conn, "DELETE FROM house_types WHERE id = $id");
        $message = $delete ? "✅ House type deleted successfully." : "❌ Failed to delete.";
    }
}

// Add House Type
if (isset($_POST['add'])) {
    $type_name = mysqli_real_escape_string($conn, $_POST['type_name']);
    $default_rent = mysqli_real_escape_string($conn, $_POST['default_rent']);
    $apartment_id = (int)$_POST['apartment_id'];

    $check = mysqli_query($conn, "SELECT * FROM house_types WHERE type_name = '$type_name'");
    if (mysqli_num_rows($check) > 0) {
        $message = "⚠️ House type already exists.";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO house_types (type_name, default_rent, apartment_id) 
                  VALUES ('$type_name', '$default_rent', $apartment_id)");
        $message = $insert ? "✅ House type added." : "❌ Failed to add.";
    }
}

// Update House Type
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id'];
    $type_name = mysqli_real_escape_string($conn, $_POST['type_name']);
    $default_rent = mysqli_real_escape_string($conn, $_POST['default_rent']);

    $update = mysqli_query($conn, "UPDATE house_types SET type_name='$type_name', default_rent='$default_rent' WHERE id=$id");

    if ($update) {
        mysqli_query($conn, "UPDATE houses SET rent='$default_rent' WHERE type_id = $id");
        $message = "✅ House type and associated house rents updated.";
    } else {
        $message = "❌ Failed to update.";
    }
}

// Filter handling
$filter_apartment_id = isset($_GET['filter_apartment_id']) ? (int)$_GET['filter_apartment_id'] : null;


// Pagination setup
$limit = 25; // Number of rows per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;



$filter_query = "SELECT ht.*, a.apartment_name 
    FROM house_types ht 
    LEFT JOIN apartment a ON ht.apartment_id = a.apartment_id";

if ($filter_apartment_id) {
    $filter_query .= " WHERE ht.apartment_id = $filter_apartment_id";
}

$filter_query .= " ORDER BY ht.id DESC LIMIT $limit OFFSET $offset";
// Count total records for pagination
$count_query = "SELECT COUNT(*) AS total FROM house_types";
if ($filter_apartment_id) {
    $count_query .= " WHERE apartment_id = $filter_apartment_id";
}
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_rows = $count_row['total'];
$total_pages = ceil($total_rows / $limit);

$result = mysqli_query($conn, $filter_query);


?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage House Types | AptManager</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        /* (Same styles as you had previously) */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05);}
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        thead { background: #34495e; color: #fff; }
        .message { margin-top: 10px; padding: 10px; background: #dff0d8; border-radius: 5px; }
        .top-header { display: flex; justify-content: space-between; align-items: center; }
        .add-btn { background: #3498db; color: #fff; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px; }
        .add-btn:hover { background: #2980b9; }
        .dropdown { position: relative; display: inline-block; }
        .dropdown-content { display: none; position: absolute; right: 0; background-color: #fff; min-width: 100px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); z-index: 1; border-radius: 5px; }
        .dropdown-content a { color: black; padding: 8px 12px; text-decoration: none; display: block; }
        .three-dot { cursor: pointer; font-size: 18px; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .popup { background: #fff; padding: 20px; border-radius: 8px; width: 300px; position: relative; }
        .close-btn { position: absolute; top: 5px; right: 10px; cursor: pointer; color: red; }
        select { width: 100%; padding: 8px; margin-top: 10px; }

        .apt-card {
    padding: 10px 15px;
    border-radius: 6px;
    background: #ecf0f1;
    color: #2c3e50;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.3s;
    border: 1px solid #bdc3c7;
}
.apt-card:hover {
    background: #dcdde1;
}
.apt-card.active {
    background: #3498db;
    color: white;
    border-color: #2980b9;
}

    </style>
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
      <li><a href="../Payment/prep_report.php"><i class="bi bi-bar-chart-line-fill"></i> Prepayments</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-tools"></i> Maintenance</a>
    <ul class="submenu">
      <li><a href="../aintenance/maintenance.php"><i class="bi bi-plus-square-fill"></i> New Request</a></li>
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
        <h1>Manage House Types</h1>
        <button class="add-btn" onclick="openAdd()">➕ Add Type</button>
    </header>

    <div style="margin: 20px 0; display: flex; flex-wrap: wrap; gap: 10px;">
    <a href="?" class="apt-card <?= !$filter_apartment_id ? 'active' : '' ?>">All Apartments</a>
    <?php mysqli_data_seek($apartment_result, 0); while($apt = mysqli_fetch_assoc($apartment_result)): ?>
        <a href="?filter_apartment_id=<?= $apt['apartment_id'] ?>" 
           class="apt-card <?= ($filter_apartment_id == $apt['apartment_id']) ? 'active' : '' ?>">
            <?= htmlspecialchars($apt['apartment_name']) ?>
        </a>
    <?php endwhile; ?>
</div>


    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Type Name</th>
                <th>Default Rent (KES)</th>
                <th>Apartment</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['type_name']) ?></td>
                <td>Ksh <?= number_format($row['default_rent'], 2) ?></td>
                <td><?= htmlspecialchars($row['apartment_name'] ?? '—') ?></td>
                <td>
                    <div class="dropdown">
                        <span class="three-dot" onclick="toggleDropdown(this)">⋮</span>
                        <div class="dropdown-content">
                            <a href="#" onclick="openEdit(<?= $row['id'] ?>, '<?= htmlspecialchars($row['type_name'], ENT_QUOTES) ?>', <?= $row['default_rent'] ?>)">Edit</a>
                            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this type?')">Delete</a>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
      <div class="pagination">
  <?php if ($page > 1): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Prev</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="<?= $page == $i ? 'active' : '' ?>">
      <?= $i ?>
    </a>
  <?php endfor; ?>

  <?php if ($page < $total_pages): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &raquo;</a>
  <?php endif; ?>
</div>

<style>
.pagination {
  text-align: center;
  margin-top: 20px;
}
.pagination a {
  padding: 8px 12px;
  margin: 0 4px;
  border: 1px solid #ccc;
  text-decoration: none;
  color: #333;
  border-radius: 4px;
}
.pagination a.active {
  background-color: #34495e;
  color: white;
  border-color: #34495e;
}
</style>
  </div>
</div>

<!-- Add Popup -->
<div class="overlay" id="addOverlay">
    <div class="popup">
        <span class="close-btn" onclick="closeAdd()">✖</span>
        <h3>Add New House Type</h3>
        <form method="POST">
            <input type="text" name="type_name" placeholder="House Type Name" required>
            <input type="number" name="default_rent" placeholder="Default Rent (KES)" step="0.01" required>
            <select name="apartment_id" required>
                <option value="">-- Select Apartment --</option>
                <?php mysqli_data_seek($apartment_result, 0); while($apt = mysqli_fetch_assoc($apartment_result)): ?>
                    <option value="<?= $apt['apartment_id'] ?>"><?= htmlspecialchars($apt['apartment_name']) ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="add">Add Type</button>
        </form>
    </div>
</div>

<!-- Edit Popup -->
<div class="overlay" id="editOverlay">
    <div class="popup">
        <span class="close-btn" onclick="closeEdit()">✖</span>
        <h3>Edit House Type</h3>
        <form method="POST">
            <input type="hidden" name="id" id="editId">
            <input type="text" name="type_name" id="editTypeName" placeholder="House Type Name" required>
            <input type="number" name="default_rent" id="editRent" placeholder="Default Rent (KES)" step="0.01" required>
            <button type="submit" name="edit">Update</button>
        </form>
    </div>
</div>

<script>
function openAdd() {
    document.getElementById('addOverlay').style.display = 'flex';
}
function closeAdd() {
    document.getElementById('addOverlay').style.display = 'none';
}
function openEdit(id, typeName, rent) {
    document.getElementById('editId').value = id;
    document.getElementById('editTypeName').value = typeName;
    document.getElementById('editRent').value = rent;
    document.getElementById('editOverlay').style.display = 'flex';
}
function closeEdit() {
    document.getElementById('editOverlay').style.display = 'none';
}
function toggleDropdown(element) {
    const dropdown = element.nextElementSibling;
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    document.querySelectorAll('.dropdown-content').forEach(d => {
        if (d !== dropdown) d.style.display = 'none';
    });
}
document.addEventListener('click', function(e) {
    if (!e.target.matches('.three-dot')) {
        document.querySelectorAll('.dropdown-content').forEach(d => d.style.display = 'none');
    }
});
</script>
<script src="../assets/js/script.js"></script>
</body>
</html>
