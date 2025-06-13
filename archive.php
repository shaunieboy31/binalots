<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "binalots";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// For dashboard (only sales chart)
$sales_data = [];
$ssql = "SELECT DATE(o.created_at) as sale_date, SUM(d.price) as total_sales
         FROM archived_orders o
         JOIN archived_order_details d ON o.order_id = d.order_id
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
    <title>Archived Receipts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    body { background-color: #284b25; color: white; }
    .analytics { background-color: #2e6733; padding: 20px 15px; border-radius: 5px; margin-bottom: 20px; }
    .analytics h4 { font-size: 1.2rem; margin-bottom: 10px; }
    .dashboard-chart-container { max-width: 700px; margin: 0 auto; }
    .table-dark { background-color: #2e6733; }
    .table-dark th, .table-dark td { color: white; }
    .modal-content { color: #222; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Archived Receipts</h2>
    <div class="text-center mb-4">
        <a href="history.php" class="btn btn-secondary">Back to History</a>
        <button class="btn btn-danger ms-2" onclick="showManagerModal('', true)">Delete All</button>
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
    <!-- Search Receipt No (auto search, no reset/search button) -->
    <form class="row g-2 mb-3" id="searchForm" onsubmit="return false;">
      <div class="col-auto" style="flex:1;">
        <input type="text" class="form-control w-100" name="search_receipt" id="search_receipt" placeholder="Search Receipt No">
      </div>
    </form>
    <div class="analytics mb-4">
        <h4 class="text-center">Dashboard</h4>
        <div class="dashboard-chart-container">
            <canvas id="salesChart" height="90"></canvas>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <strong>Archived Receipts</strong>
        </div>
        <div class="card-body p-0">
            <table class="table table-dark table-striped mb-0">
                <thead>
                    <tr>
                        <th>Receipt No</th>
                        <th>Operator</th>
                        <th>Date</th>
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

<!-- Manager Code Modal -->
<div class="modal fade" id="managerModal" tabindex="-1" aria-labelledby="managerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="managerModalLabel">Manager Access</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="managerCodeForm" onsubmit="return checkManagerCode();">
          <div class="mb-3">
            <label for="managerCodeInput" class="form-label">Enter Manager Code</label>
            <input type="password" class="form-control" id="managerCodeInput" required autofocus>
            <div id="managerCodeError" class="text-danger mt-2" style="display:none;">Incorrect code. Access denied.</div>
          </div>
          <input type="hidden" id="deleteReceiptNo">
          <input type="hidden" id="deleteAllFlag">
          <button type="submit" class="btn btn-primary w-100">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Archived Receipt Modal -->
<div class="modal fade" id="archivedReceiptModal" tabindex="-1" aria-labelledby="archivedReceiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="archivedReceiptModalLabel">Archived Receipt Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="archivedReceiptModalBody">
        <!-- Details will be loaded here -->
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentPage = 1;

function showManagerModal(receiptNo, deleteAll = false) {
    document.getElementById('managerCodeInput').value = '';
    document.getElementById('managerCodeError').style.display = 'none';
    document.getElementById('deleteReceiptNo').value = receiptNo || '';
    document.getElementById('deleteAllFlag').value = deleteAll ? '1' : '';
    var modal = new bootstrap.Modal(document.getElementById('managerModal'));
    modal.show();
    setTimeout(() => {
      document.getElementById('managerCodeInput').focus();
    }, 500);
}

function checkManagerCode() {
    const code = document.getElementById('managerCodeInput').value;
    const receiptNo = document.getElementById('deleteReceiptNo').value;
    const deleteAll = document.getElementById('deleteAllFlag').value === '1';
    if (code === "2222") {
        document.getElementById('managerCodeError').style.display = 'none';
        var modal = bootstrap.Modal.getInstance(document.getElementById('managerModal'));
        modal.hide();
        // Proceed to delete
        if(deleteAll) {
            if(confirm("Are you sure you want to permanently delete ALL archived receipts?")) {
                fetch('delete_archived_receipt.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'delete_all=1'
                })
                .then(res => res.text())
                .then(data => {
                    alert(data);
                    updateTable(1);
                });
            }
        } else if(receiptNo) {
            if(confirm("Are you sure you want to permanently delete this archived receipt?")) {
                fetch('delete_archived_receipt.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'receipt_no=' + encodeURIComponent(receiptNo)
                })
                .then(res => res.text())
                .then(data => {
                    alert(data);
                    updateTable(currentPage);
                });
            }
        }
    } else {
        document.getElementById('managerCodeError').style.display = 'block';
    }
    return false; // Prevent form submit
}

function viewArchivedReceipt(receiptNo) {
    fetch('view_archived_receipt.php?receipt_no=' + encodeURIComponent(receiptNo))
        .then(res => res.text())
        .then(html => {
            document.getElementById('archivedReceiptModalBody').innerHTML = html;
            var modal = new bootstrap.Modal(document.getElementById('archivedReceiptModal'));
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
    fetch('archive_table.php?' + params.toString())
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
                        color: '#fff'
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