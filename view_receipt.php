<?php
if (!isset($_GET['receipt_no'])) {
    echo "<div class='alert alert-danger'>No receipt number provided.</div>";
    exit;
}

$receipt_no = $_GET['receipt_no'];

$conn = new mysqli("localhost", "root", "", "binalots");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get order info
$sql = "SELECT o.operator, o.created_at, o.payment_method
        FROM orders o
        WHERE o.receipt_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $receipt_no);
$stmt->execute();
$stmt->bind_result($operator, $created_at, $payment_method);
if (!$stmt->fetch()) {
    echo "<div class='alert alert-danger'>Receipt not found.</div>";
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Get order items
$sql = "SELECT d.item, d.quantity, d.price
        FROM orders o
        JOIN order_details d ON o.order_id = d.order_id
        WHERE o.receipt_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $receipt_no);
$stmt->execute();
$result = $stmt->get_result();

echo "<div>";
echo "<strong>Receipt No:</strong> " . htmlspecialchars($receipt_no) . "<br>";
echo "<strong>Operator:</strong> " . htmlspecialchars($operator) . "<br>";
echo "<strong>Date:</strong> " . htmlspecialchars($created_at) . "<br>";
echo "<strong>Payment Method:</strong> " . htmlspecialchars($payment_method) . "<br>";
echo "<hr>";
echo "<table class='table table-bordered'>";
echo "<thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead><tbody>";
$total = 0;
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['item']) . "</td>";
    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
    echo "<td>₱" . number_format($row['price'], 2) . "</td>";
    echo "</tr>";
    $total += $row['price'];
}
echo "<tr><td colspan='2' class='text-end'><strong>Order Total:</strong></td><td><strong>₱" . number_format($total, 2) . "</strong></td></tr>";
echo "</tbody></table>";
echo "</div>";

$stmt->close();
$conn->close();
?>