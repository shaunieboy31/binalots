<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'binalots';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$stmt = $conn->prepare("INSERT INTO order_details (item, quantity, price) VALUES (?, ?, ?)");
$stmt->bind_param("sid", $item, $qty, $price);

$item = "Test Item";
$qty = 2;
$price = 99.99;

$stmt->execute();
$stmt->close();
$conn->close();

echo "Test insert done!";
?>