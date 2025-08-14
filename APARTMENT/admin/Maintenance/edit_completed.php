<?php
include '../config/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid request.";
    exit();
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM completed_requests WHERE id = $id");

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Request not found.";
    exit();
}

$row = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $issue = mysqli_real_escape_string($conn, $_POST['issue']);
    $expense = floatval($_POST['expense']);
    $date = mysqli_real_escape_string($conn, $_POST['completion_date']);

    $update = mysqli_query($conn, "UPDATE completed_requests 
        SET unit='$unit', issue='$issue', expense_amount='$expense', completion_date='$date' 
        WHERE id=$id");

    if ($update) {
        header("Location: completed.php");
        exit();
    } else {
        echo "Failed to update: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Completed Request</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .edit-container {
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            height: 80px;
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
        }

        button[type="submit"] {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #2980b9;
        }

        .cancel-btn {
            background-color: #ccc;
            color: #333;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 15px;
            transition: background 0.3s ease;
        }

        .cancel-btn:hover {
            background-color: #b0b0b0;
        }
    </style>
</head>
<body>

<div class="edit-container">
    <h2>Edit Completed Maintenance Request</h2>
    <form method="POST">
        <label>Unit:</label>
        <input type="text" name="unit" value="<?= htmlspecialchars($row['unit']) ?>" required>

        <label>Issue:</label>
        <textarea name="issue" required><?= htmlspecialchars($row['issue']) ?></textarea>

        <label>Expense Amount (KES):</label>
        <input type="number" name="expense" step="0.01" value="<?= $row['expense_amount'] ?>" required>

        <label>Completion Date:</label>
        <input type="date" name="completion_date" value="<?= $row['completion_date'] ?>" required>

        <div class="btn-group">
            <button type="submit" name="update">Update</button>
            <a href="completed.php" class="cancel-btn">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>
