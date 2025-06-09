<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'binalots';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['delete_all']) && $_POST['delete_all'] == '1') {
    $conn->query("DELETE FROM archived_order_details");
    $conn->query("DELETE FROM archived_orders");
    echo "All archived receipts permanently deleted!";
} elseif (isset($_POST['receipt_no'])) {
    $receipt_no = $conn->real_escape_string($_POST['receipt_no']);
    $result = $conn->query("SELECT order_id FROM archived_orders WHERE receipt_no = '$receipt_no'");
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $order_id = $row['order_id'];
            $conn->query("DELETE FROM archived_order_details WHERE order_id = $order_id");
            $conn->query("DELETE FROM archived_orders WHERE order_id = $order_id");
        }
        echo "Archived receipt permanently deleted!";
    } else {
        echo "Archived receipt not found.";
    }
} else {
    echo "No receipt specified.";
}
$conn->close();
?>