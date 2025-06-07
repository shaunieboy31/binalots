<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "binalots";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all orders with their items
$sql = "SELECT o.order_id, o.receipt_no, o.operator, o.created_at, d.item, d.quantity, d.price
        FROM orders o
        JOIN order_details d ON o.order_id = d.order_id
        ORDER BY o.order_id DESC, d.item ASC";
$result = $conn->query($sql);

$orders = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[$row['receipt_no']]['info'] = [
            'operator' => $row['operator'],
            'created_at' => $row['created_at']
        ];
        $orders[$row['receipt_no']]['items'][] = [
            'item' => $row['item'],
            'quantity' => $row['quantity'],
            'price' => $row['price']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color:#284b25; color:white;">
<div class="container mt-5">
    <h2 class="mb-4">Order History</h2>
    <a href="home.php" class="btn btn-secondary mb-3">Back to POS</a>
    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $receipt_no => $order): ?>
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <strong>Receipt No:</strong> <?= htmlspecialchars($receipt_no) ?>
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
        <div class="alert alert-info">No orders found.</div>
    <?php endif; ?>
</div>
</body>
</html>
<?php $conn->close(); ?>