<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "binalots";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- AUTO ARCHIVE ORDERS OLDER THAN 3 DAYS ---
function autoArchiveOldOrders($conn) {
    // Select orders older than 3 days
    $orders = $conn->query("SELECT * FROM orders WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)");
    while ($order = $orders->fetch_assoc()) {
        $order_id = $order['order_id'];
        // Insert into archived_orders
        $fields = array_map(function($v){ return "`$v`"; }, array_keys($order));
        $values = array_map(function($v) use ($conn){ return "'".$conn->real_escape_string($v)."'"; }, array_values($order));
        $conn->query("INSERT INTO archived_orders (".implode(",",$fields).") VALUES (".implode(",",$values).")");
        // Insert related order_details
        $details = $conn->query("SELECT * FROM order_details WHERE order_id = $order_id");
        while ($detail = $details->fetch_assoc()) {
            $dfields = array_map(function($v){ return "`$v`"; }, array_keys($detail));
            $dvalues = array_map(function($v) use ($conn){ return "'".$conn->real_escape_string($v)."'"; }, array_values($detail));
            $conn->query("INSERT INTO archived_order_details (".implode(",",$dfields).") VALUES (".implode(",",$dvalues).")");
        }
        // Delete from order_details and orders
        $conn->query("DELETE FROM order_details WHERE order_id = $order_id");
        $conn->query("DELETE FROM orders WHERE order_id = $order_id");
    }
}
autoArchiveOldOrders($conn);

// For dashboard
$best_sellers = [];
$bsql = "SELECT d.item, SUM(d.quantity) as total_sold
         FROM order_details d
         JOIN orders o ON d.order_id = o.order_id
         GROUP BY d.item
         ORDER BY total_sold DESC
         LIMIT 5";
$bresult = $conn->query($bsql);
while($row = $bresult->fetch_assoc()) {
    $best_sellers[] = $row;
}

$sales_data = [];
$ssql = "SELECT DATE(o.created_at) as sale_date, SUM(d.price) as total_sales
         FROM orders o
         JOIN order_details d ON o.order_id = d.order_id
         WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
         GROUP BY sale_date
         ORDER BY sale_date ASC";
$sresult = $conn->query($ssql);
while($row = $sresult->fetch_assoc()) {
    $sales_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #284b25; color: white; }
        .analytics { background-color: #2e6733; padding: 20px; border-radius: 10px; }
        .list-group-item { background: #2e6733; color: #fff; border: none; }
        .list-group-item .badge { font-size: 1rem; }
        .table-dark { background-color: #2e6733; }
        .table-dark th, .table-dark td { color: white; }
        .modal-content { color: #222; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Order Receipts</h2>
    <div class="text-center mb-4">
        <a href="home.php" class="btn btn-secondary">Back to POS</a>
        <a href="archive.php" class="btn btn-secondary">View Archive</a>
    </div>
    <!-- Sort/Range Filter -->
    <form class="row g-2 mb-3" id="filterForm" onsubmit="return false;">
      <div class="col-auto">
        <label for="from" class="col-form-label">From:</label>
      </div>
      <div class="col-auto">
        <input type="date" class="form-control" id="from" name="from">
      </div>
      <div class="col-auto">
        <label for="to" class="col-form-label">To:</label>
      </div>
      <div class="col-auto">
        <input type="date" class="form-control" id="to" name="to">
      </div>
    </form>
    <!-- Dashboard -->
    <div class="analytics mb-4">
        <h4>Dashboard</h4>
        <div class="row">
            <div class="col-md-6 mb-3">
                <canvas id="salesChart" height="120"></canvas>
            </div>
            <div class="col-md-6 mb-3">
                <h5>Top 5 Best Sellers</h5>
                <ol class="list-group list-group-numbered">
                    <?php foreach($best_sellers as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($item['item']) ?>
                            <span class="badge bg-success rounded-pill"><?= $item['total_sold'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </div>
    <!-- Search Receipt No (auto search, no reset/search button) -->
    <form class="row g-2 mb-3" id="searchForm" onsubmit="return false;">
      <div class="col-auto">
        <input type="text" class="form-control" name="search_receipt" id="search_receipt" placeholder="Search Receipt No">
      </div>
    </form>
    <div class="card mb-4">
        <div class="card-header">
            <strong>Receipts</strong>
        </div>
        <div class="card-body p-0">
            <table class="table table-dark table-striped mb-0">
                <thead>
                    <tr>
                        <th>Receipt No</th>
                        <th>Operator</th>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="receipts-table-body">
                    <!-- Table rows will be loaded here by AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="receiptModalLabel">Receipt Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="receiptModalBody">
        <!-- Details will be loaded here -->
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentPage = 1;

function viewReceipt(receiptNo) {
    fetch('view_receipt.php?receipt_no=' + encodeURIComponent(receiptNo))
        .then(res => res.text())
        .then(html => {
            document.getElementById('receiptModalBody').innerHTML = html;
            var modal = new bootstrap.Modal(document.getElementById('receiptModal'));
            modal.show();
        });
}

// AJAX table update with pagination
function updateTable(page = 1) {
    currentPage = page;
    const from = document.getElementById('from').value;
    const to = document.getElementById('to').value;
    const search = document.getElementById('search_receipt').value;
    const params = new URLSearchParams({from, to, search_receipt: search, page});
    fetch('history_table.php?' + params.toString())
        .then(res => res.text())
        .then(html => {
            document.getElementById('receipts-table-body').innerHTML = html;
        });
}

function goToPage(page) {
    updateTable(page);
}

document.getElementById('search_receipt').addEventListener('input', function() {
    document.getElementById('from').value = '';
    document.getElementById('to').value = '';
    updateTable(1);
});

document.getElementById('from').addEventListener('change', function() {
    document.getElementById('search_receipt').value = '';
    updateTable(1);
});
document.getElementById('to').addEventListener('change', function() {
    document.getElementById('search_receipt').value = '';
    updateTable(1);
});

// Initial load
document.addEventListener('DOMContentLoaded', function() {
    updateTable(1);

    // Chart.js Dashboard
    const salesLabels = <?= json_encode(array_column($sales_data, 'sale_date')) ?>;
    const salesTotals = <?= json_encode(array_map('floatval', array_column($sales_data, 'total_sales'))) ?>;
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Total Sales (₱)',
                data: salesTotals,
                backgroundColor: '#4CAF50'
            }]
        },
        options: {
            plugins: {
                legend: {
                    labels: {
                        color: '#fff' // Set legend font color to white
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                }
            }
        }
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>