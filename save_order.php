<?php
// filepath: c:\xampp\htdocs\Binalots\save_order.php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'binalots';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

session_start();
$order = json_decode($_POST['order'], true);

$receipt_no = date('YmdHis');
$operator = isset($_SESSION['username']) ? $_SESSION['username'] : 'Operator';

if ($order && is_array($order)) {
    $stmt = $conn->prepare("INSERT INTO orders (receipt_no, operator) VALUES (?, ?)");
    $stmt->bind_param("ss", $receipt_no, $operator);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    foreach ($order as $item) {
        $total_price = $item['qty'] * $item['price'];
        $stmt = $conn->prepare("INSERT INTO order_details (order_id, item, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isid", $order_id, $item['name'], $item['qty'], $total_price);
        $stmt->execute();
        $stmt->close();
    }
    echo "Order saved!";
} else {
    echo "Invalid order data.";
}

$conn->close();
?>