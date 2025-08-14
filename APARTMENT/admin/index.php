<?php

session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../reg.php");
    exit();
}

include '../config/db_connect.php'; // adjust path if needed

$unanswered_count = 0;

$query = "
    SELECT COUNT(*) AS total 
    FROM chat_messages 
    WHERE sender_type = 'tenant' AND is_answered = 0
";

$result = mysqli_query($conn, $query);
if ($result) {
    $data = mysqli_fetch_assoc($result);
    $unanswered_count = $data['total'];
}




// Load chatbot session data
$user_role = $_SESSION['role'] ?? '';
$sender_unit = $_SESSION['unit'] ?? '';
$replying_to = $_SESSION['chat_target_unit'] ?? 'None';
$quoted_message = $_SESSION['quoted_message'] ?? '';



// Total tenants
$total_tenants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM tenants WHERE status='Active'"))['total'];

// Overdue tenants
$overdue_tenants = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM tenants
    LEFT JOIN (
        SELECT tenant_id FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '".date('Y-m')."' AND status = 'Paid'
    ) AS p ON tenants.id = p.tenant_id
    WHERE tenants.status='Active' AND p.tenant_id IS NULL
"))['total'];

// Units and Occupancy
$total_units = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM houses"))['total'];
$occupied_units = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(DISTINCT unit) AS total FROM tenants 
    WHERE status='Active' AND unit IN (SELECT unit FROM houses)
"))['total'];
$vacant_units = $total_units - $occupied_units;
$occupancy_rate = $total_units > 0 ? round(($occupied_units / $total_units) * 100) : 0;

// Rent Collected Current Month
$total_rent = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(amount) AS total FROM payments 
    WHERE status='Paid' AND DATE_FORMAT(payment_date, '%Y-%m') = '".date('Y-m')."'
"))['total'];
$total_rent = $total_rent ? $total_rent : 0;

// Rent & Overdue Last 6 Months
$months = [];
$rent_data = [];
$overdue_data = [];
$expense_data = [];

for ($i = 5; $i >= 0; $i--) {
    $month_label = date('M', strtotime("-$i months"));
    $month_value = date('Y-m', strtotime("-$i months"));
    $months[] = $month_label;

    $rent = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT SUM(amount) AS total FROM payments 
        WHERE status='Paid' AND DATE_FORMAT(payment_date, '%Y-%m') = '$month_value'
    "))['total'];
    $rent_data[] = $rent ? (int)$rent : 0;

    $overdue = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) AS total FROM tenants
        LEFT JOIN (
            SELECT tenant_id FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$month_value' AND status = 'Paid'
        ) AS p ON tenants.id = p.tenant_id
        WHERE tenants.status='Active' AND p.tenant_id IS NULL
    "))['total'];
    $overdue_data[] = (int)$overdue;

    $expense = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT SUM(expense_amount) AS total FROM completed_requests 
        WHERE DATE_FORMAT(completion_date, '%Y-%m') = '$month_value'
    "))['total'];
    $expense_data[] = $expense ? (int)$expense : 0;
}

// Occupancy by House Type
$type_query = "
SELECT 
    house_types.type_name,
    COUNT(houses.unit) AS total_units,
    SUM(CASE WHEN tenants.status = 'Active' THEN 1 ELSE 0 END) AS occupied_units
FROM houses
JOIN house_types ON houses.type_id = house_types.id
LEFT JOIN tenants ON tenants.unit = houses.unit
GROUP BY house_types.type_name
";
$type_result = mysqli_query($conn, $type_query);

$housetypeLabels = [];
$housetypeData = [];

while ($row = mysqli_fetch_assoc($type_result)) {
    $housetypeLabels[] = $row['type_name'];
    $total_units = $row['total_units'];
    $occupied_units_type = $row['occupied_units'];
    $rate = $total_units > 0 ? round(($occupied_units_type / $total_units) * 100) : 0;
    $housetypeData[] = $rate;
}

// Generate Expected Payments
if (isset($_POST['generate_payments'])) {
    $current_month = date('Y-m');
    $success_count = 0;
    $fail_count = 0;

    $tenant_query = mysqli_query($conn, "SELECT id, unit FROM tenants WHERE status = 'Active'");

    while ($tenant = mysqli_fetch_assoc($tenant_query)) {
        $tenant_id = $tenant['id'];
        $unit = $tenant['unit'];

        $rent_query = mysqli_query($conn, "SELECT rent FROM houses WHERE unit = '$unit'");
        if ($rent_row = mysqli_fetch_assoc($rent_query)) {
            $rent_amount = $rent_row['rent'];
        } else {
            $fail_count++;
            continue;
        }

        $check_query = mysqli_query($conn, "
            SELECT id FROM payments 
            WHERE tenant_id = $tenant_id 
            AND DATE_FORMAT(payment_date, '%Y-%m') = '$current_month'
        ");

        if (mysqli_num_rows($check_query) == 0) {
            $insert_query = mysqli_query($conn, "
                INSERT INTO payments (tenant_id, amount, status, payment_date)
                VALUES ($tenant_id, $rent_amount, 'Pending', CURDATE())
            ");
            if ($insert_query) {
                $success_count++;
            } else {
                $fail_count++;
            }
        }
    }

    $payment_message = "✅ $success_count payments generated. ⚠️ $fail_count failed.";
}




$occupancy_query = "
    SELECT 
        a.apartment_name,
        COUNT(h.unit) AS total_units,
        SUM(CASE WHEN h.status = 'Occupied' THEN 1 ELSE 0 END) AS occupied_units
    FROM apartment a
    LEFT JOIN houses h ON a.apartment_id = h.apartment_id
    GROUP BY a.apartment_name
";
$occupancy_result = mysqli_query($conn, $occupancy_query);

$labels = [];
$data = [];

while ($row = mysqli_fetch_assoc($occupancy_result)) {
    $labels[] = $row['apartment_name'];
    $percentage = $row['total_units'] > 0 
        ? round(($row['occupied_units'] / $row['total_units']) * 100, 2) 
        : 0;
    $data[] = $percentage;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AptManager | Dashboard</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
/* Existing CSS */
.top-header { padding: 10px 50px; }
.header-row { display: flex; justify-content: space-between; align-items: center; }
.generate-btn {
  background: #28a745; color: white; border: none; font-size: 1.5rem;
  border-radius: 50%; width: 36px; height: 36px; cursor: pointer; position: relative;
}
.generate-btn:hover { background: #218838; }
.popup-text {
  visibility: hidden; background-color: #333; color: #fff; padding: 5px 8px;
  border-radius: 4px; position: absolute; top: 40px; left: -20px;
  white-space: nowrap; font-size: 0.8rem; z-index: 100;
}
.generate-btn:hover .popup-text { visibility: visible; }
.top-header p { color: gray; }
.stat-cards { display: flex; flex-wrap: wrap; gap: 20px; margin: 20px 0; }
.stat-card {
  flex: 1; background: linear-gradient(135deg, #3498db, #2ecc71);
  color: white; padding: 20px; border-radius: 8px; font-size: 1.2rem;
  min-width: 200px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s;
}
.stat-card:hover { transform: translateY(-5px); }
.charts-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}
.chart-box {
  background: white; padding: 20px; border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.05);
}
footer { margin-top: 20px; text-align: center; font-size: 0.9rem; color: gray; }
</style>
</head>
<body>

<div class="wrapper">
<aside class="sidebar" id="sidebar">
  <h2>🏠 AptManager</h2>
  <ul>
  <li><a href="../admin/index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-building"></i> Manage Houses</a>
    <ul class="submenu">
      <li><a href="Housing/apartment.php"><i class="bi bi-houses-fill"></i> Apartments</a></li>
      <li><a href="Housing/housing.php"><i class="bi bi-houses-fill"></i> All Houses</a></li>
      <li><a href="Housing/vacant.php"><i class="bi bi-door-open"></i> Vacant</a></li>
      <li><a href="Housing/occupied.php"><i class="bi bi-person-check-fill"></i> Occupied</a></li>
      <li><a href="Housing/house_type.php"><i class="bi bi-grid-1x2-fill"></i> House Type</a></li>
      <li><a href="Housing/add_house.php"></i> ➕ Add House</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-person-circle"></i> Manage Tenants</a>
    <ul class="submenu">
      <li><a href="Tenant/t_tenant.php"><i class="bi bi-people-fill"></i> All Tenants</a></li>
      <li><a href="Tenant/former_tenant.php"><i class="bi bi-box-arrow-up-right"></i> Former Tenants</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-cash-stack"></i> Payments</a>
    <ul class="submenu">
      <li><a href="Payment/payment.php"><i class="bi bi-currency-dollar"></i> All Payments</a></li>
      <li><a href="Payment/overdue.php"><i class="bi bi-alarm-fill"></i> Overdue Payments</a></li>
     <li><a href="Payment/prep_report.php"><i class="bi bi-bar-chart-line-fill"></i> Prepayments</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#" class="dropdown-toggle"><i class="bi bi-tools"></i> Maintenance</a>
    <ul class="submenu">
      <li><a href="Maintenance/maintenance.php"><i class="bi bi-plus-square-fill"></i> New Request</a></li>
      <li><a href="Maintenance/completed.php"><i class="bi bi-check2-circle"></i> Completed Requests</a></li>
    </ul>
  </li>

  <li><a href="notification.php"><i class="bi bi-bell-fill"></i> Send Notification</a></li>
  <li><a href="Include/users.php"><i class="bi bi-person-gear"></i> Manage Users</a></li>
  <li><a href="Include/staff.php"><i class="bi bi-person-badge-fill"></i> Manage Staff</a></li>
  <li><a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Log Out</a></li>
</ul>

</aside>

<main class="main">
  <header class="top-header">
    <div class="header-row">
      <h1>Welcome Admin, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
      <form method="POST">
        <button type="submit" name="generate_payments" class="generate-btn" title="Generate New Month Payment">
          ➕ <span class="popup-text">Generate Page</span>
        </button>
      </form>
    </div>
    <p>Real-time summary for Smart Apartment Manager</p>
  </header>

<?php if (isset($payment_message)): ?>
<div style="background:#dff0d8;padding:10px;border-radius:5px;margin-bottom:15px;">
  <?= $payment_message ?>
</div>
<?php endif; ?>

<section class="stat-cards">
  <div class="stat-card">👥 Total Tenants: <strong><?= $total_tenants ?></strong></div>
  <div class="stat-card">🚫 Overdue Tenants: <strong><?= $overdue_tenants ?></strong></div>
  <div class="stat-card">🏠 Occupancy Rate: <strong><?= $occupancy_rate ?>%</strong></div>
  <div class="stat-card">💰 Total (<?= date('F') ?>): <strong>Ksh <?= number_format($total_rent, 2) ?></strong></div>
</section>

<section class="charts-grid">
  <div class="chart-box">
    <h3>Occupancy Rate</h3>
    <canvas id="occupancyChart"></canvas>
  </div>
  <div class="chart-box">
    <h3>Monthly Rent Collection</h3>
    <canvas id="rentChart"></canvas>
  </div>



  <div class="chart-box">
     <h3>Occupancy By Apartment</h3>
    <canvas id="apartmentOccupancyChart"></canvas>
</div>




  <div class="chart-box">
    <h3>Overdue Tenants Trend</h3>
    <canvas id="overdueChart"></canvas>
  </div>
  <div class="chart-box">
    <h3>Occupancy Rate by House Type</h3>
    <canvas id="occupancyTypeChart"></canvas>
  </div>
  <div class="chart-box">
    <h3>Monthly Expenses</h3>
    <canvas id="expenseChart"></canvas>
  </div>
</section>
<section class="lease-table">
  <h3>Apartment Management Staff</h3>
  <table>
    <tr><th>Name</th><th>Role</th><th>Contact</th></tr>
    <?php
    $staff_query = mysqli_query($conn, "SELECT * FROM staff ORDER BY staff_id DESC");
    if (mysqli_num_rows($staff_query) > 0) {
        while ($staff = mysqli_fetch_assoc($staff_query)) {
            echo "<tr>
                    <td>" . htmlspecialchars($staff['name']) . "</td>
                    <td>" . htmlspecialchars($staff['role']) . "</td>
                    <td>" . htmlspecialchars($staff['contact']) . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='4' style='text-align:center;'>No staff records found.</td></tr>";
    }
    ?>
    
  </table>
</section>


<footer>&copy; 2025 AptManager | All rights reserved.</footer>
</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const rentLabels = <?= json_encode($months) ?>;
const rentData = <?= json_encode($rent_data) ?>;
const overdueData = <?= json_encode($overdue_data) ?>;
const housetypeLabels = <?= json_encode($housetypeLabels) ?>;
const housetypeData = <?= json_encode($housetypeData) ?>;
const expenseData = <?= json_encode($expense_data) ?>;
const labels = <?= json_encode($labels) ?>;
const data = <?= json_encode($data) ?>;

new Chart(document.getElementById('occupancyChart'), {
  type: 'doughnut',
  data: { labels: ['Occupied', 'Vacant'], datasets: [{ data: [<?= $occupied_units ?>, <?= $vacant_units ?>], backgroundColor: ['#2ecc71', '#e74c3c'] }] },
  options: { responsive: true, cutout: '70%' }
});

new Chart(document.getElementById('rentChart'), {
  type: 'bar',
  data: { labels: rentLabels, datasets: [{ label: 'Ksh Collected', data: rentData, backgroundColor: '#3498db' }] },
  options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('overdueChart'), {
  type: 'line',
  data: { labels: rentLabels, datasets: [{ label: 'Overdue Tenants', data: overdueData, backgroundColor: 'rgba(231, 76, 60, 0.2)', borderColor: '#e74c3c', fill: true, tension: 0.4 }] },
  options: { responsive: true }
});

new Chart(document.getElementById('occupancyTypeChart'), {
  type: 'line',
  data: { labels: housetypeLabels, datasets: [{ label: 'Occupancy Rate (%)', data: housetypeData, backgroundColor: '#3498db' }] },
  options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } } }
});

new Chart(document.getElementById('expenseChart'), {
  type: 'line',
  data: { labels: rentLabels, datasets: [{ label: 'Total Expenses (KES)', data: expenseData, backgroundColor: 'rgba(243, 156, 18, 0.2)', borderColor: '#f39c12', fill: true, tension: 0.4 }] },
  options: { responsive: true }
});

new Chart(document.getElementById('apartmentOccupancyChart'), {
  type: 'pie',
  data: {
    labels: <?= json_encode($labels) ?>,
    datasets: [{
      data: <?= json_encode($data) ?>,
      backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f1c40f', '#9b59b6']
    }]
  },
  options: { responsive: true }
});
</script>

<script src="assets/js/script.js"></script>



<!-- Your original admin dashboard layout and styles remain here -->

<!-- ✅ FLOATING CHAT ICON -->



<div class="chat-float" onclick="toggleChat()" title="Chat">
    💬
    <?php if ($unanswered_count > 0): ?>
        <span class="chat-badge"><?= $unanswered_count ?></span>
    <?php endif; ?>
</div>




<!-- ✅ CHAT POPUP -->
<div class="chat-popup" id="chatPopup">
  <header>
      <?php if ($user_role === 'admin'): ?>
  <form method="POST" action="Chat/delete_all_messages.php" onsubmit="return confirm('Are you sure you want to delete all messages?');" style="text-align:right; padding:5px 10px;">
    <button type="submit" style="background:#fff;color:white;border:none;padding:5px 10px;border-radius:5px;cursor:pointer;">🗑️</button>
  </form>
<?php endif; ?>
  Chat Assistant</header>

  <div class="replying-indicator" id="replyingTo">
    Replying to: <strong><?= htmlspecialchars($replying_to) ?></strong>
  </div>

  <?php if (!empty($quoted_message)): ?>
    <div class="quoted-box">
      <div class="quoted-sender"><?= htmlspecialchars($replying_to) ?></div>
      <div class="quoted-text"><?= htmlspecialchars($quoted_message) ?></div>
    </div>
  <?php endif; ?>

  <div class="messages" id="chatMessages" onclick="setReplyTarget(event)"></div>

  <div class="footer">
    <input type="text" id="chatInput" placeholder="Type a message...">
    <button onclick="sendMessage()">Send</button>
  </div>
</div>

<!-- ✅ CHAT STYLES -->
<style>
  .chat-float {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #2c3e50;
    color: white;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    z-index: 999;
  }

  .chat-popup {
    position: fixed;
    bottom: 100px;
    right: 30px;
    background: white;
    width: 340px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: none;
    flex-direction: column;
    overflow: hidden;
    z-index: 998;
    font-family: Arial, sans-serif;
  }

  .chat-popup header {
    background: #2c3e50;
    color: white;
    padding: 12px;
    font-weight: bold;
    font-size: 15px;
  }

  .chat-popup .messages {
    padding: 10px;
    height: 250px;
    overflow-y: auto;
    font-size: 14px;
  }

  .chat-popup .footer {
    display: flex;
    border-top: 1px solid #ccc;
  }

  .chat-popup .footer input {
    flex: 1;
    padding: 10px;
    border: none;
    outline: none;
  }

  .chat-popup .footer button {
    background: #2c3e50;
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
  }

  .replying-indicator {
    font-size: 12px;
    color: green;
    padding: 5px 10px;
    background: #f5f5f5;
    border-top: 1px solid #ddd;
    font-style: italic;
  }

  .chat-message {
    max-width: 65%;
    margin: 8px;
    padding: 10px 12px;
    border-radius: 10px;
    position: relative;
    clear: both;
  }

  .chat-left {
    float: left;
    background-color: #fff;
    border: 1px solid #ddd;
  }

  .chat-right {
    float: right;
    background-color: #dcf8c6;
    border: 1px solid #cbe2b0;
  }

  .quoted-box {
    background-color: #f0f0f0;
    border-left: 3px solid #34b7f1;
    padding: 6px 10px;
    margin-bottom: 6px;
    font-size: 0.9em;
    border-radius: 5px;
  }

  .quoted-text {
    color: #555;
  }

  
 

.chat-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    background: red;
    color: white;
    font-size: 12px;
    padding: 2px 6px;
    border-radius: 50%;
    font-weight: bold;
}

</style>

<!-- ✅ CHAT SCRIPT -->
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
      const msgBox = document.getElementById('chatMessages');
      const isAtBottom = msgBox.scrollTop + msgBox.clientHeight >= msgBox.scrollHeight - 10;
      msgBox.innerHTML = data;
      if (isAtBottom) msgBox.scrollTop = msgBox.scrollHeight;
    });
}

function setReplyTarget(event) {
  const unit = event.target.getAttribute('data-unit');
  const message = event.target.getAttribute('data-message');

  if (unit && message) {
    fetch('Chat/set_chat_target.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'target=' + encodeURIComponent(unit)
    });

    fetch('Chat/set_quoted_message.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'quoted=' + encodeURIComponent(message)
    });

    document.getElementById('replyingTo').innerHTML = 'Replying to: <strong>' + unit + '</strong>';
  }
}

setInterval(fetchMessages, 3000);
window.onload = fetchMessages;
</script>

</body>
</html>