<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "binalots";
$conn = new mysqli($servername, $username, $password, $dbname);

$filter_sql = [];
if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $from = $_GET['from'];
    $to = $_GET['to'];
    $filter_sql[] = "DATE(o.created_at) BETWEEN '$from' AND '$to'";
} elseif (!empty($_GET['from'])) {
    $from = $_GET['from'];
    $filter_sql[] = "DATE(o.created_at) >= '$from'";
} elseif (!empty($_GET['to'])) {
    $to = $_GET['to'];
    $filter_sql[] = "DATE(o.created_at) <= '$to'";
}
if (!empty($_GET['search_receipt'])) {
    $search = $conn->real_escape_string($_GET['search_receipt']);
    $filter_sql[] = "o.receipt_no LIKE '%$search%'";
}
$where = '';
if ($filter_sql) {
    $where = 'WHERE ' . implode(' AND ', $filter_sql);
}

$sql = "SELECT o.receipt_no, o.operator, o.created_at, o.payment_method
        FROM orders o
        $where
        GROUP BY o.receipt_no
        ORDER BY o.order_id DESC";
$result = $conn->query($sql);

$receipts = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $receipts[] = $row;
    }
}

if (!empty($receipts)) {
    foreach ($receipts as $r) {
        echo "<tr>
            <td>".htmlspecialchars($r['receipt_no'])."</td>
            <td>".htmlspecialchars($r['operator'])."</td>
            <td>".htmlspecialchars($r['created_at'])."</td>
            <td>".htmlspecialchars($r['payment_method'])."</td>
            <td><button class='btn btn-info btn-sm' onclick=\"viewReceipt('".htmlspecialchars($r['receipt_no'])."')\">View</button></td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center text-warning'>No matching receipts found.</td></tr>";
}
$conn->close();
?>