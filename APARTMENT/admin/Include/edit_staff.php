<?php
session_start();
include '../config/db_connect.php';

// Restrict to admins only
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../reg.php");
    exit();
}

// Get Staff ID from URL
if (!isset($_GET['staff_id'])) {
    header("Location: staff.php");
    exit();
}

$staff_id = (int)$_GET['staff_id'];

// Fetch staff details
$staff_result = mysqli_query($conn, "SELECT * FROM staff WHERE staff_id = $staff_id");
if (mysqli_num_rows($staff_result) != 1) {
    header("Location: staff.php");
    exit();
}

$staff = mysqli_fetch_assoc($staff_result);

// Handle form submission
if (isset($_POST['updateStaff'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $update = "UPDATE staff SET name = '$name', contact = '$contact', role = '$role' WHERE staff_id = $staff_id";
    
    if (mysqli_query($conn, $update)) {
        header("Location: staff.php");
        exit();
    } else {
        $error = "Error updating staff: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Edit Staff Member</h2>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($staff['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contact</label>
            <input type="text" class="form-control" name="contact" value="<?= htmlspecialchars($staff['contact']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <input type="text" class="form-control" name="role" value="<?= htmlspecialchars($staff['role']) ?>" required>
        </div>
        <button type="submit" name="updateStaff" class="btn btn-success">Update Staff</button>
        <a href="staff.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
