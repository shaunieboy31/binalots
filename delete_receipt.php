<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'binalots';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['receipt_no'])) {
    $receipt_no = $conn->real_escape_string($_POST['receipt_no']);
    // Get order_id(s) for this receipt_no
    $result = $conn->query("SELECT order_id FROM orders WHERE receipt_no = '$receipt_no'");
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $order_id = $row['order_id'];
            // Archive order
            $conn->query("INSERT INTO archived_orders SELECT * FROM orders WHERE order_id = $order_id");
            $conn->query("INSERT INTO archived_order_details SELECT * FROM order_details WHERE order_id = $order_id");
            // Delete from main tables
            $conn->query("DELETE FROM order_details WHERE order_id = $order_id");
            $conn->query("DELETE FROM orders WHERE order_id = $order_id");
        }
        echo "Receipt archived and deleted!";
    } else {
        echo "Receipt not found.";
    }
} else {
    echo "No receipt specified.";
}
$conn->close();
?>