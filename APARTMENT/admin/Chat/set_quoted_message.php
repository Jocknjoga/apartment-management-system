<?php
session_start();

if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quoted'])) {
    $_SESSION['quoted_message'] = $_POST['quoted'];
    echo "Quoted message set.";
} else {
    echo "No quoted message provided.";
}
?>

