<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "binalots";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get archived orders
$sql = "SELECT o.order_id, o.receipt_no, o.operator, o.created_at, d.item, d.quantity, d.price
        FROM archived_orders o
        JOIN archived_order_details d ON o.order_id = d.order_id
        ORDER BY o.order_id DESC, d.item ASC";
$result = $conn->query($sql);

$orders = [];
$total_revenue = 0;
$total_items = 0;

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $receipt_no = $row['receipt_no'];
        if (!isset($orders[$receipt_no])) {
            $orders[$receipt_no] = [
                'info' => [
                    'operator' => $row['operator'],
                    'created_at' => $row['created_at']
                ],
                'items' => []
            ];
        }
        $orders[$receipt_no]['items'][] = [
            'item' => $row['item'],
            'quantity' => $row['quantity'],
            'price' => $row['price']
        ];

        $total_revenue += $row['price'];
        $total_items += $row['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archived Receipts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #284b25; color: white; }
        .btn-info { background-color: #4CAF50; border: none; }
        .btn-info:hover { background-color: #45a049; }
        .card-header { background-color: #2e6733; }
        .card { border: none; border-radius: 10px; overflow: hidden; }
        .table-dark { background-color: #2e6733; }
        .table-dark th, .table-dark td { color: white; }
        .analytics { background-color: #2e6733; padding: 20px; border-radius: 10px; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Archived Receipts</h2>
    <div class="text-center mb-4">
        <a href="history.php" class="btn btn-secondary">Back to History</a>
        <button class="btn btn-danger ms-2" onclick="showManagerModal('', true)">Delete All</button>
    </div>
    <div class="analytics mb-4">
        <h4>Analytics</h4>
        <p><strong>Total Archived Revenue:</strong> ₱<?= number_format($total_revenue, 2) ?></p>
        <p><strong>Total Archived Items Sold:</strong> <?= $total_items ?></p>
    </div>
    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $receipt_no => $order): ?>
            <div class="card mb-4">
                <div class="card-header text-white">
                    <strong>Receipt No:</strong> <?= htmlspecialchars($receipt_no) ?>
                    <button class="btn btn-danger btn-sm ms-2" onclick="showManagerModal('<?= htmlspecialchars($receipt_no) ?>', false)" title="Delete Archived Receipt">
                        Delete
                    </button>
                    <span class="float-end"><strong>Operator:</strong> <?= htmlspecialchars($order['info']['operator']) ?> | <strong>Date:</strong> <?= htmlspecialchars($order['info']['created_at']) ?></span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-dark table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $order_total = 0; ?>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['item']) ?></td>
                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                    <td>₱<?= number_format($item['price'], 2) ?></td>
                                </tr>
                                <?php $order_total += $item['price']; ?>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="2" class="text-end"><strong>Order Total:</strong></td>
                                <td><strong>₱<?= number_format($order_total, 2) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">No archived receipts found.</div>
    <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
                    location.reload();
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
                    location.reload();
                });
            }
        }
    } else {
        document.getElementById('managerCodeError').style.display = 'block';
    }
    return false; // Prevent form submit
}
</script>
</body>
</html>
<?php $conn->close(); ?>