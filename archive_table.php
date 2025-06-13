<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "binalots";
$conn = new mysqli($servername, $username, $password, $dbname);

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 3;
$offset = ($page - 1) * $per_page;

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

$count_sql = "SELECT COUNT(DISTINCT o.receipt_no) as total FROM archived_orders o $where";
$count_result = $conn->query($count_sql);
$total = $count_result ? $count_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total / $per_page);

$sql = "SELECT o.receipt_no, o.operator, o.created_at
        FROM archived_orders o
        $where
        GROUP BY o.receipt_no
        ORDER BY o.order_id DESC
        LIMIT $per_page OFFSET $offset";
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
            <td>
                <button class='btn btn-info btn-sm' onclick=\"viewArchivedReceipt('".htmlspecialchars($r['receipt_no'])."')\">View</button>
                <button class='btn btn-danger btn-sm ms-2' onclick=\"showManagerModal('".htmlspecialchars($r['receipt_no'])."', false)\" title='Delete Archived Receipt'>Delete</button>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='4' class='text-center'>No archived receipts found.</td></tr>";
}

echo "<tr><td colspan='4' class='text-center'>";
for ($i = 1; $i <= $total_pages; $i++) {
    $active = $i == $page ? "btn-primary" : "btn-outline-primary";
    echo "<button class='btn $active btn-sm mx-1' onclick='goToPage($i)'>$i</button>";
}
echo "</td></tr>";

$conn->close();
?>