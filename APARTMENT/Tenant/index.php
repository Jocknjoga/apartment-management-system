<?php
session_start();

if (!isset($_SESSION['tenant_name']) || $_SESSION['role'] != 'tenant') {
    header("Location: ../reg.php");
    exit();
}

include '../config/db_connect.php';

// Get tenant info
$username = $_SESSION['tenant_name'];
$stmt = $conn->prepare("SELECT * FROM tenants WHERE name = ? AND status = 'Active'");
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Access denied. Tenant record not found or inactive.";
    exit();
}

$tenant = $result->fetch_assoc();
$tenant_id = $tenant['id'];

// Payment summary
$summary_query = mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) AS total_paid,
        SUM(CASE WHEN status = 'Pending' THEN amount ELSE 0 END) AS total_pending,
        COUNT(*) AS total_transactions
    FROM payments 
    WHERE tenant_id = $tenant_id
");
$summary = mysqli_fetch_assoc($summary_query);

// Payment history
$payments = mysqli_query($conn, "
    SELECT amount, payment_date, status 
    FROM payments 
    WHERE tenant_id = $tenant_id 
    ORDER BY payment_date DESC
");


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenant Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <style>
.message-row {
  border: 1px solid #ccc;
  padding: 10px;
  margin-bottom: 8px;
  background: #fff;
  cursor: pointer;
  border-left: 5px solid transparent;
}

.message-row.unread {
  font-weight: bold;
  border-left-color: green;
}

.message-preview {
  font-size: 14px;
  color: #333;
}

.message-full {
  display: none;
  padding-top: 5px;
  font-size: 13px;
  color: #444;
}

.message-row.open .message-full {
  display: block;
}

.chat-left, .chat-right {
  display: flex;
  margin: 8px;
}

.chat-left {
  justify-content: flex-start;
}

.chat-right {
  justify-content: flex-end;
}

.bubble-left, .bubble-right {
  padding: 10px 14px;
  border-radius: 15px;
  font-size: 14px;
  line-height: 1.4;
  max-width: 75%;
  position: relative;
}

.bubble-left {
  background-color: #3498db;
  color: white;
  border-top-left-radius: 0;
}

.bubble-right {
  background-color: #ecf0f1;
  color: #2c3e50;
  border-top-right-radius: 0;
}

.bubble-left .time, .bubble-right .time {
  font-size: 10px;
  text-align: right;
  margin-top: 5px;
  opacity: 0.7;
}


.delete-btn {
  background: none;
  border: none;
  color: red;
  float: right;
  cursor: pointer;
  font-size: 16px;
  margin-left: 10px;
}
.delete-btn:hover {
  color: darkred;
}


</style>

</head>
<body>

  <!-- Sidebar Toggle Button (Mobile) -->
  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <h2>🏠 AptTenant</h2>
    <a href="index.php">Home</a>
    <a href="unit_info.php">Unit Info</a>
    <a href="maintenance.php">Maintenance</a>
    <a href="../logout.php">Logout</a>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <header>
      <h1>Welcome, <?= htmlspecialchars($_SESSION['tenant_name']) ?>!</h1>
    </header>

    <!-- Payment Summary -->
    <div style="margin-bottom: 15px; background: #ecf0f1; padding: 15px; border-radius: 5px;">
      <strong>Total Paid:</strong> Ksh <?= number_format($summary['total_paid'], 2) ?> |
      <strong>Pending:</strong> Ksh <?= number_format($summary['total_pending'], 2) ?> |
      <strong>Transactions:</strong> <?= $summary['total_transactions'] ?>
    </div>

    <!-- Payment History -->
    <div>
      <div class="section-title">Payment History</div>
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Amount Paid</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($payments) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($payments)): ?>
              <tr>
                <td><?= date('d M Y', strtotime($row['payment_date'])) ?></td>
                <td>KES <?= number_format($row['amount'], 2) ?></td>
                <td style="color: <?= $row['status'] == 'Paid' ? 'green' : ($row['status'] == 'Pending' ? 'orange' : 'red') ?>;">
                  <?= $row['status'] ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="3">No payment records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      
<!-- ========================= -->
<!-- PARTIAL PAYMENTS SECTION -->
<!-- ========================= -->
<h3 style="margin-top: 40px;">📌 Partial Payments</h3>

<!-- Add Partial Payment Form -->

<?php
if (isset($_POST['add_partial'])) {
    $partial_amount = floatval($_POST['partial_amount']);

    // Get expected rent from housing table
    $unit = mysqli_real_escape_string($conn, $tenant['unit']);
    $rent_query = mysqli_query($conn, "SELECT rent FROM houses WHERE unit = '$unit'");

    if ($rent_query && mysqli_num_rows($rent_query) > 0) {
        $rent_data = mysqli_fetch_assoc($rent_query);
        $rent_expected = floatval($rent_data['rent']);

        // Get current month range
        $month_start = date('Y-m-01');
        $month_end = date('Y-m-t');

        // Calculate total partial payments made this month
        $total_query = mysqli_query($conn, "
            SELECT SUM(amount) AS total_paid 
            FROM partial_payments 
            WHERE tenant_id = $tenant_id 
            AND DATE(payment_date) BETWEEN '$month_start' AND '$month_end'
        ");

        $total_row = mysqli_fetch_assoc($total_query);
        $total_partial = floatval($total_row['total_paid'] ?? 0);

        $new_total = $total_partial + $partial_amount;
        $balance = max(0, $rent_expected - $new_total);

        // ✅ Prevent overpayment
        if ($new_total > $rent_expected) {
            echo "<p style='color:red;'>❌ Cannot accept this amount. It exceeds the expected rent of Ksh " . number_format($rent_expected, 2) . ".</p>";
        } else {
            // ✅ Insert into partial_payments
            mysqli_query($conn, "
                INSERT INTO partial_payments (tenant_id, amount, balance, rent_expected) 
                VALUES ($tenant_id, $partial_amount, $balance, $rent_expected)
            ");

            // ✅ Check if a payment record exists for current month
            $existing_payment = mysqli_query($conn, "
                SELECT id FROM payments 
                WHERE tenant_id = $tenant_id 
                AND MONTH(payment_date) = MONTH(CURRENT_DATE()) 
                AND YEAR(payment_date) = YEAR(CURRENT_DATE())
            ");

            if (mysqli_num_rows($existing_payment) == 0) {
                // No payment yet — insert new pending record
                mysqli_query($conn, "
                    INSERT INTO payments (tenant_id, amount, status, payment_date) 
                    VALUES ($tenant_id, $partial_amount, 'Pending', NOW())
                ");
            } else {
                // Payment exists — update amount
                mysqli_query($conn, "
                    UPDATE payments 
                    SET amount = amount + $partial_amount 
                    WHERE tenant_id = $tenant_id 
                    AND MONTH(payment_date) = MONTH(CURRENT_DATE()) 
                    AND YEAR(payment_date) = YEAR(CURRENT_DATE())
                ");
            }

            // ✅ If fully paid, update status
            if ($balance <= 0) {
                mysqli_query($conn, "
                    UPDATE payments 
                    SET status = 'Paid', amount = $rent_expected 
                    WHERE tenant_id = $tenant_id 
                    AND MONTH(payment_date) = MONTH(CURRENT_DATE()) 
                    AND YEAR(payment_date) = YEAR(CURRENT_DATE())
                ");
            }

        }
      }
}
?>


<!-- Display Partial Payment Records -->
<table style="margin-top: 20px;">
    <thead>
        <tr>
            <th>Date</th>
            <th>Amount Paid (Ksh)</th>
            <th>Expected Rent (Ksh)</th>
            <th>Balance (Ksh)</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $partial_q = mysqli_query($conn, "
            SELECT amount, rent_expected, balance, payment_date 
            FROM partial_payments 
            WHERE tenant_id = $tenant_id 
            ORDER BY payment_date DESC
        ");

        if (mysqli_num_rows($partial_q) > 0):
            while ($p = mysqli_fetch_assoc($partial_q)):
        ?>
        <tr>
            <td><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
            <td><?= number_format($p['amount'], 2) ?></td>
            <td><?= number_format($p['rent_expected'], 2) ?></td>
            <td><?= number_format($p['balance'], 2) ?></td>
            </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="5">No partial payments found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>




    </div>

    <!-- Notifications -->
 <?php
$notif_q = mysqli_query($conn, "SELECT * FROM notifications WHERE tenant_id = $tenant_id ORDER BY created_at DESC");
$unread_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM notifications WHERE tenant_id = $tenant_id AND is_read = 0");
$unread_data = mysqli_fetch_assoc($unread_count);
?>
<div class="notifications">
  <div class="section-title">
    Notifications
    <?php if ($unread_data['total'] > 0): ?>
      <span class="badge"><?= $unread_data['total'] ?></span>
    <?php endif; ?>
  </div>

  <?php if (mysqli_num_rows($notif_q) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($notif_q)): ?>
  <div class="message-row <?= $row['is_read'] == 0 ? 'unread' : '' ?>" id="notif_<?= $row['id'] ?>" onclick="markAsRead(<?= $row['id'] ?>, this)">
    <div class="message-preview">
      <?= $row['is_read'] == 0 ? '<span class="unread-icon">🔔</span>' : '' ?>
      <strong><?= htmlspecialchars($row['title']) ?>:</strong> <?= substr($row['message'], 0, 40) ?>...
      <button class="delete-btn" onclick="event.stopPropagation(); deleteNotification(<?= $row['id'] ?>)">🗑️</button>
    </div>
    <div class="message-full">
      <?= htmlspecialchars($row['message']) ?><br>
      <small><em><?= date("d M Y H:i", strtotime($row['created_at'])) ?></em></small>
    </div>
  </div>
<?php endwhile; ?>

  <?php else: ?>
    <p>No notifications yet.</p>
  <?php endif; ?>
</div>
<script>
function deleteNotification(id) {
  if (confirm("Delete this notification?")) {
    fetch('delete_notification.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'id=' + encodeURIComponent(id)
    })
    .then(res => res.text())
    .then(response => {
      if (response === 'success') {
        const notifElem = document.getElementById('notif_' + id);
        if (notifElem) notifElem.remove();
      } else {
        alert("Failed to delete notification.");
      }
    });
  }
}
</script>


  <!-- JavaScript -->
  <script>
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('open');
    }

    function toggleChat() {
      const chat = document.getElementById('chatPopup');
      chat.style.display = (chat.style.display === 'flex') ? 'none' : 'flex';
    }

    function toggleMessage(row) {
      row.classList.toggle('open');
    }
  </script>
  <script>
function markAsRead(id, row) {
  row.classList.remove('unread');
  fetch("mark_read.php?id=" + id)
    .then(response => response.text())
    .then(data => console.log(data));
  row.classList.toggle("open");
}
</script>

<?php
// Make sure session is started and DB is connected
if (session_status() == PHP_SESSION_NONE) session_start();
include '../config/db_connect.php';
if (!isset($_SESSION['unit']) && $_SESSION['role'] === 'tenant') {
    // Fetch unit from the database using logged-in tenant ID or email
    $tenant_id = $_SESSION['tenant_id'] ?? null;

    if ($tenant_id) {
        $stmt = $conn->prepare("SELECT unit FROM tenants WHERE id = ?");
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $stmt->bind_result($unit);
        if ($stmt->fetch()) {
            $_SESSION['unit'] = $unit;
        }
        $stmt->close();
    }
}

$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$sender_unit = isset($_SESSION['unit']) ? $_SESSION['unit'] : '';
?>
<!-- Chatbot UI -->
<div class="chat-float" onclick="toggleChat()" title="Chat">💬</div>

<div class="chat-popup" id="chatPopup">
  <header>Chat Assistant</header>
  <div class="messages" id="chatMessages"></div>
  <div style="display: flex; border-top: 1px solid #ccc;">
    <input type="text" id="chatInput" placeholder="Type a message..." style="flex: 1; padding: 10px; border: none; outline: none;">
    <button onclick="sendMessage()" style="background: #2c3e50; color: white; border: none; padding: 10px 15px; cursor: pointer;">Send</button>
  </div>
</div>

<script>
function toggleChat() {
  const chat = document.getElementById("chatPopup");
  chat.style.display = chat.style.display === "flex" ? "none" : "flex";
}

function sendMessage() {
  const input = document.getElementById("chatInput");
  const message = input.value.trim();
  if (message === "") return;

  fetch('Chat/chatbot_send.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'message=' + encodeURIComponent(message)
  }).then(() => {
    input.value = '';
    fetchMessages();
  });
}

function fetchMessages() {
  fetch('Chat/chatbot_fetch.php')
    .then(response => response.text())
    .then(data => {
      document.getElementById('chatMessages').innerHTML = data;
      let messages = document.getElementById('chatMessages');
      messages.scrollTop = messages.scrollHeight;
    });
}

// Load messages and keep refreshing every 3 seconds
window.onload = fetchMessages;
setInterval(fetchMessages, 3000);
</script>

</body>

</html>
