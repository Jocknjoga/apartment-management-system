<?php
include 'config/db_connect.php';
session_start();

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if user is system user (super_admin, admin, or user)
    $user_query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $user_result = mysqli_query($conn, $user_query);

    if ($user_result && mysqli_num_rows($user_result) == 1) {
        $user = mysqli_fetch_assoc($user_result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];

            // Store client_id only for admins & users
            if (!empty($user['client_id'])) {
                $_SESSION['client_id'] = $user['client_id'];
            }

            // Redirect based on role
            if ($user['role'] == 'super_admin') {
                header("Location: admin/super_admin.php");
            } elseif ($user['role'] == 'admin') {
                header("Location: admin/index.php");
            } elseif ($user['role'] == 'user') {
                header("Location: user/index.php");
            } else {
                $error = "Invalid role assigned to this user.";
            }
            exit();
        } else {
            $error = "Invalid user credentials.";
        }
    } else {
        // Check if user is a tenant (login using name + national_id)
        $tenant_query = "SELECT * FROM tenants 
                         WHERE name = '$username' 
                         AND national_id = '$password' 
                         AND status = 'Active'
                         LIMIT 1";
        $tenant_result = mysqli_query($conn, $tenant_query);

        if ($tenant_result && mysqli_num_rows($tenant_result) == 1) {
            $tenant = mysqli_fetch_assoc($tenant_result);
            
            $_SESSION['tenant_id'] = $tenant['id'];
            $_SESSION['tenant_name'] = $tenant['name'];
            $_SESSION['role'] = 'tenant';

            header("Location: Tenant/index.php");
            exit();
        } else {
            $error = "Invalid tenant credentials or inactive tenant.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart AptManager | Login</title>
    <link rel="stylesheet" href="assets/css/reg.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #dce3eb;
        }
        .top-header {
            background: #2c3e50;
            color: white;
            display: flex;
            align-items: center;
            padding: 10px 30px;
        }
        .top-header img {
            height: 50px;
            margin-right: 15px;
        }
        .account-page {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 80px);
            flex-direction: column;
            padding-top: 20px;
        }
        .login-box {
            display: flex;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 800px;
            width: 90%;
        }
        .login-box img {
            width: 250px;
            margin-right: 30px;
        }
    </style>
</head>
<body>

<header class="top-header">
    <img src="assets/img/logo.png" alt="Smart AptManager Logo">
    <h1>Maskani Smart AptManager</h1>
</header>
<div class="account-page">
    <div class="login-box">
        
<img src="assets/img/logo3.png" alt="Logo">
        
        

        <div class="flex-fill">
            <h4 class="mb-3">Login</h4>

            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button class="btn btn-primary w-100" name="login">Login</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
