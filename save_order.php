<?php
session_start(); // Make sure to start the session to access the logged-in user

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
$stmt = $conn->prepare("INSERT INTO orders (receipt_no, operator, created_at, payment_method) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $receipt_no, $operator, $created_at, $payment_method);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// Insert order details
$stmt = $conn->prepare("INSERT INTO order_details (order_id, item, quantity, price) VALUES (?, ?, ?, ?)");
foreach ($order as $item) {
    $item_name = $item['name'];
    $qty = intval($item['qty']);
    $price = floatval($item['total']);
    $stmt->bind_param("isid", $order_id, $item_name, $qty, $price);
    $stmt->execute();
}
$stmt->close();

echo "Order saved!";
$conn->close();
?>