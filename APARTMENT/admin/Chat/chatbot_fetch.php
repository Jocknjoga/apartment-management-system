<?php
session_start();
include '../config/db_connect.php';

$user_role = $_SESSION['role'] ?? '';
$sender_unit = $_SESSION['unit'] ?? '';

if ($user_role === 'tenant') {
    $stmt = $conn->prepare("SELECT * FROM chat_messages WHERE sender_unit = ? OR receiver_unit = ? ORDER BY sent_at ASC");
    $stmt->bind_param("ss", $sender_unit, $sender_unit);
} else {
    $stmt = $conn->prepare("SELECT * FROM chat_messages ORDER BY sent_at ASC");
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $sender = $row['sender_type'] === 'admin' ? 'Admin' : $row['sender_unit'];
    $msg = htmlspecialchars($row['message']);
    $quoted = $row['quoted_message'] ?? '';

    $alignClass = $row['sender_type'] === 'admin' ? 'chat-right' : 'chat-left';
    $dataAttr = ($user_role === 'admin' && $row['sender_type'] !== 'admin') 
                ? " data-unit='" . htmlspecialchars($row['sender_unit']) . "' data-message='" . htmlspecialchars($row['message']) . "'" 
                : '';

    echo "<div class='chat-message $alignClass'$dataAttr>";
    if (!empty($quoted)) {
        echo "<div class='quoted-message'>" . htmlspecialchars($quoted) . "</div>";
    }
    echo "<strong>$sender:</strong> $msg</div>";
}


?>
