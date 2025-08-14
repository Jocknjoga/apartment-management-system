<?php
include '../config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

// Restrict to admins only
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../reg.php");
    exit();
}

// Handle Add Staff
if (isset($_POST['addStaff'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $insert = "INSERT INTO staff (name, contact, role) VALUES ('$name', '$contact', '$role')";
    if (mysqli_query($conn, $insert)) {
        header("Location: staff.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Handle Delete Staff
if (isset($_GET['delete'])) {
    $staffID = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM staff WHERE staff_id = $staffID");
    header("Location: staff.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .action-btn {
            border: none;
            background: none;
            cursor: pointer;
            color: black;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="d-flex justify-content-between mb-4">
        <h2>Staff Management</h2>
        <div>
            <a href="../index.php" class="btn btn-secondary me-2">Home</a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">Add Staff</button>
        </div>
    </div>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM staff ORDER BY staff_id DESC");
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['contact']) . "</td>
                        <td>" . htmlspecialchars($row['role']) . "</td>
                        <td>
                            <div class='dropdown'>
                                <button class='action-btn' data-bs-toggle='dropdown'><strong>⋮</strong></button>
                                <ul class='dropdown-menu'>
                                    <li><a class='dropdown-item' href='edit_staff.php?staff_id={$row['staff_id']}'>Edit</a></li>
                                    <li><a class='dropdown-item text-danger' href='staff.php?delete={$row['staff_id']}' onclick='return confirm(\"Are you sure you want to delete this staff?\")'>Delete</a></li>
                                </ul>
                            </div>
                        </td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact</label>
                    <input type="text" class="form-control" name="contact" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" name="role" placeholder="e.g. Caretaker, Agent" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" name="addStaff" class="btn btn-success">Add Staff</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
