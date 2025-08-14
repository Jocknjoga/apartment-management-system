<?php
session_start();
include '../config/db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reg.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$id = (int)$_GET['id'];
$message = "";

// Fetch user details
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: users.php");
    exit();
}

// Handle Update
if (isset($_POST['update_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET username = '$username', role = '$role', password = '$hashed_password' WHERE id = $id");
    } else {
        $update = mysqli_query($conn, "UPDATE users SET username = '$username', role = '$role' WHERE id = $id");
    }

    if ($update) {
        $message = "✅ User updated successfully.";
        $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
        $user = mysqli_fetch_assoc($result);
    } else {
        $message = "❌ Failed to update user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit User | AptManager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Edit User</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select class="form-control" name="role" required>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">New Password <small>(leave blank to keep existing)</small></label>
            <input type="password" class="form-control" name="password">
        </div>

        <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
        <a href="users.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
