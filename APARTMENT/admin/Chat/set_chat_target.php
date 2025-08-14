<?php
session_start();

if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['target'])) {
    $_SESSION['chat_target_unit'] = $_POST['target'];
    echo "Target set to " . htmlspecialchars($_POST['target']);
} else {
    echo "No target provided";
}
?>
