<?php
session_start();
include '../config/db_connect.php';

$user_role = $_SESSION['role'] ?? '';
$sender_unit = $_SESSION['unit'] ?? '';
$message = trim($_POST['message'] ?? '');
$quoted = $_SESSION['quoted_message'] ?? null;

if ($message === '') exit;

if ($user_role === 'tenant') {
    $stmt = $conn->prepare("INSERT INTO chat_messages (sender_type, sender_unit, receiver_unit, message, quoted_message) VALUES ('tenant', ?, NULL, ?, NULL)");
    $stmt->bind_param("ss", $sender_unit, $message);
} else {
    $receiver = $_SESSION['chat_target_unit'] ?? null;
    if (!$receiver) exit('No target selected');

    $stmt = $conn->prepare("INSERT INTO chat_messages (sender_type, sender_unit, receiver_unit, message, quoted_message) VALUES ('admin', ?, ?, ?, ?)");
    $stmt->bind_param("ssss", $sender_unit, $receiver, $message, $quoted);
    unset($_SESSION['quoted_message']); // clear after sending
}


$stmt->execute();



?>
