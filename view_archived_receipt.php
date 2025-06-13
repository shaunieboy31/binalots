<?php
if (!isset($_GET['receipt_no'])) {
    echo "<div class='alert alert-danger'>No receipt number provided.</div>";
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "binalots";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$receipt_no = $conn->real_escape_string($_GET['receipt_no']);

// Get archived order info
$order = $conn->query("SELECT * FROM archived_orders WHERE receipt_no = '$receipt_no' LIMIT 1")->fetch_assoc();
if (!$order) {
    echo "<div class='alert alert-danger'>Archived receipt not found.</div>";
    exit;
}

// Get archived order details
$details = $conn->query("SELECT * FROM archived_order_details WHERE order_id = {$order['order_id']}");

?>
<h5>Receipt #: <?= htmlspecialchars($order['receipt_no']) ?></h5>
<p>
    <strong>Operator:</strong> <?= htmlspecialchars($order['operator']) ?><br>
    <strong>Date:</strong> <?= htmlspecialchars($order['created_at']) ?><br>
    <strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?>
</p>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Item</th>
            <th>Qty</th>
            <th>Price</th>
        </tr>
    </thead>
    <tbody>
        <?php $total = 0; ?>
        <?php while($row = $details->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['item']) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?></td>
                <td>₱<?= number_format($row['price'], 2) ?></td>
            </tr>
            <?php $total += $row['price']; ?>
        <?php endwhile; ?>
        <tr>
            <td colspan="2" class="text-end"><strong>Total</strong></td>
            <td><strong>₱<?= number_format($total, 2) ?></strong></td>
        </tr>
    </tbody>
</table>
<?php $conn->close(); ?>