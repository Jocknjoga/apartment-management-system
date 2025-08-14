<?php

include '../config/db_connect.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

$message = "";

// Handle Add User
if (isset($_POST['add_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $message = "⚠️ Username already exists.";
    } else {
        if (mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')")) {
            $message = "✅ User added successfully.";
        } else {
            $message = "❌ Failed to add user.";
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $id");
    header("Location: users.php");
    exit();
}

// Fetch Users
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Management | AptManager</title>
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
        <h2>User Management</h2>
        <div>
            <a href="../index.php" class="btn btn-secondary me-2">Home</a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Date Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= ucfirst($row['role']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <div class="dropdown">
                        <button class="action-btn" data-bs-toggle="dropdown"><strong>⋮</strong></button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="edit_user.php?id=<?= $row['id'] ?>">Edit</a></li>
                            <li>
                                <a class="dropdown-item text-danger" href="users.php?delete=<?= $row['id'] ?>"
                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                   Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select class="form-control" name="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_user" class="btn btn-success">Add User</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
