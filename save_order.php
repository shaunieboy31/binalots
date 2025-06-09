<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'binalots';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$order = json_decode($_POST['order'], true);
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

$receipt_no = date('YmdHis');
$operator = isset($_SESSION['username']) ? $_SESSION['username'] : 'Operator';
$created_at = date('Y-m-d H:i:s');

// Insert into orders
$conn->query("INSERT INTO orders (receipt_no, operator, created_at, payment_method) VALUES ('$receipt_no', '$operator', '$created_at', '$payment_method')");
$order_id = $conn->insert_id;

// Insert order details
foreach ($order as $item) {
    $item_name = $conn->real_escape_string($item['name']);
    $qty = intval($item['qty']);
    $price = floatval($item['total']);
    $conn->query("INSERT INTO order_details (order_id, item, quantity, price) VALUES ($order_id, '$item_name', $qty, $price)");
}

echo "Order saved!";
$conn->close();
?>