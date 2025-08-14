// Occupancy Rate Chart
new Chart(document.getElementById('occupancyChart'), {
  type: 'pie',
  data: {
    labels: ['Occupied', 'Vacant'],
    datasets: [{
      data: [10, 2],
      backgroundColor: ['#2ecc71', '#e74c3c']
    }]
  }
});

// Rent Collection Chart
new Chart(document.getElementById('rentChart'), {
  type: 'line',
  data: {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
    datasets: [{
      label: 'KES Collected',
      data: [20000, 25000, 30000, 27000, 32000],
      borderColor: '#3498db',
      fill: true,
      tension: 0.4
    }]
  }
});

// Payment Breakdown
new Chart(document.getElementById('paymentChart'), {
  type: 'doughnut',
  data: {
    labels: ['Paid', 'Partial', 'Unpaid'],
    datasets: [{
      data: [8, 2, 2],
      backgroundColor: ['#27ae60', '#f1c40f', '#c0392b']
    }]
  }
});

// Maintenance Status
new Chart(document.getElementById('maintenanceChart'), {
  type: 'pie',
  data: {
    labels: ['Open', 'Resolved'],
    datasets: [{
      data: [3, 5],
      backgroundColor: ['#e67e22', '#2ecc71']
    }]
  }
});

// Tenant Satisfaction Chart

