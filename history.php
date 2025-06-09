<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "binalots";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine filter type
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$date_condition = "";

if ($filter === 'today') {
    $date_condition = "WHERE DATE(o.created_at) = CURDATE()";
} elseif ($filter === 'week') {
    $date_condition = "WHERE YEARWEEK(o.created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter === 'month') {
    $date_condition = "WHERE YEAR(o.created_at) = YEAR(CURDATE()) AND MONTH(o.created_at) = MONTH(CURDATE())";
}

// Get filtered orders
$sql = "SELECT o.order_id, o.receipt_no, o.operator, o.created_at, d.item, d.quantity, d.price
        FROM orders o
        JOIN order_details d ON o.order_id = d.order_id
        $date_condition
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

        // Calculate analytics within the loop
        $total_revenue += $row['price'];
        $total_items += $row['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #284b25;
            color: white;
        }
        .btn-info {
            background-color: #4CAF50;
            border: none;
        }
        .btn-info:hover {
            background-color: #45a049;
        }
        .card-header {
            background-color: #2e6733;
        }
        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        .table-dark {
            background-color: #2e6733;
        }
        .table-dark th, .table-dark td {
            color: white;
        }
        .analytics {
            background-color: #2e6733;
            padding: 20px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Order History</h2>
    <div class="text-center mb-4">
        <a href="home.php" class="btn btn-secondary">Back to POS</a>
    </div>
    <div class="text-center mb-4">
        <button class="btn btn-info" onclick="window.location.href='history.php?filter=today'">Today</button>
        <button class="btn btn-info" onclick="window.location.href='history.php?filter=week'">This Week</button>
        <button class="btn btn-info" onclick="window.location.href='history.php?filter=month'">This Month</button>
        <button class="btn btn-info" onclick="window.location.href='history.php'">All</button>
    </div>
    <div class="analytics mb-4">
        <h4>Analytics</h4>
        <p><strong>Total Revenue:</strong> ₱<?= number_format($total_revenue, 2) ?></p>
        <p><strong>Total Items Sold:</strong> <?= $total_items ?></p>
    </div>
    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $receipt_no => $order): ?>
            <div class="card mb-4">
                <div class="card-header text-white">
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
        <div class="alert alert-info text-center">No orders found.</div>
    <?php endif; ?>
</div>
</body>
</html>
<?php $conn->close(); ?>