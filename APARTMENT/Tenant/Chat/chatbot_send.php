<?php
session_start();
include '../config/db_connect.php';

$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$sender_unit = isset($_SESSION['unit']) ? $_SESSION['unit'] : '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    if ($message === '') exit;

    if ($user_role === 'tenant') {
        $stmt = $conn->prepare("INSERT INTO chat_messages (sender_type, sender_unit, receiver_unit, message) VALUES ('tenant', ?, NULL, ?)");
        $stmt->bind_param("ss", $sender_unit, $message);
    } else {
        // Admin sending to specific tenant (to be implemented later)
        $receiver = $_SESSION['chat_target_unit'] ?? null;
        $stmt = $conn->prepare("INSERT INTO chat_messages (sender_type, sender_unit, receiver_unit, message) VALUES ('admin', ?, ?, ?)");
        $stmt->bind_param("sss", $sender_unit, $receiver, $message);
    }

    $stmt->execute();
}


