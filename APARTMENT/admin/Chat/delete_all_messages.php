<?php
session_start();
include '../config/db_connect.php';

if ($_SESSION['role'] === 'admin') {
    // Delete all messages
    $conn->query("TRUNCATE TABLE chat_messages");
}

// Redirect back to chatbot
header("Location: ../index.php");
exit;
?>
