<?php
$conn = new mysqli("localhost", "root", "", "binalots");
if ($conn->connect_error) die("Connection failed");
$name = isset($_GET['name']) ? $conn->real_escape_string($_GET['name']) : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0; // For edit, exclude current id
if ($name) {
    $sql = "SELECT COUNT(*) as cnt FROM products WHERE name='$name'";
    if ($id) $sql .= " AND product_id!=$id";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    echo $row['cnt'] > 0 ? "exists" : "ok";
} else {
    echo "ok";
}
$conn->close();
?>