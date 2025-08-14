<?php
session_start();
include '../config/db_connect.php';

$user_role = $_SESSION['role'] ?? '';
$sender_unit = $_SESSION['unit'] ?? '';


if ($user_role === 'tenant') {
    $stmt = $conn->prepare("
        SELECT * FROM chat_messages 
        WHERE (sender_unit = ? AND sender_type = 'tenant') 
           OR (receiver_unit = ? AND sender_type IN ('admin', 'user'))
        ORDER BY sent_at ASC
    ");
    $stmt->bind_param("ss", $sender_unit, $sender_unit);
} else {
    // Admin and user see all messages
    $stmt = $conn->prepare("SELECT * FROM chat_messages ORDER BY sent_at ASC");
}


$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $message = htmlspecialchars($row['message']);
    $timestamp = date('H:i', strtotime($row['sent_at']));
    $sender_type = $row['sender_type'];

    // Align: Tenant messages to right, Admin messages to left
    $isTenant = ($sender_type === 'tenant');
    $wrapClass = $isTenant ? 'chat-left' : 'chat-right';
    $bubbleClass = $isTenant ? 'bubble-left' : 'bubble-right';

    echo "<div class='$wrapClass'>
            <div class='$bubbleClass'>
                <span>$message</span>
                <div class='time'>$timestamp</div>
            </div>
          </div>";
}
?>
