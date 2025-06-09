<?php
$conn = new mysqli("localhost", "root", "", "binalots");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle Add
if (isset($_POST['add'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $category = $conn->real_escape_string($_POST['category']);
    $price = floatval($_POST['price']);
    $desc = $conn->real_escape_string($_POST['description']);
    $conn->query("INSERT INTO products (name, category, price, description) VALUES ('$name', '$category', $price, '$desc')");
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE product_id=$id");
}

// Handle Edit
if (isset($_POST['edit'])) {
    $id = intval($_POST['product_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $category = $conn->real_escape_string($_POST['category']);
    $price = floatval($_POST['price']);
    $desc = $conn->real_escape_string($_POST['description']);
    $conn->query("UPDATE products SET name='$name', category='$category', price=$price, description='$desc' WHERE product_id=$id");
}

// Fetch products
$products = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
$edit_product = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM products WHERE product_id=$id");
    $edit_product = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#284b25;color:white;">
<div class="container mt-5">
    <h2>Product Management</h2>
    <a href="home.php" class="btn btn-secondary mb-3">Back to POS</a>
    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" class="row g-3">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="product_id" value="<?= $edit_product['product_id'] ?>">
                <?php endif; ?>
                <div class="col-md-3">
                    <input type="text" name="name" class="form-control" placeholder="Product Name" required value="<?= $edit_product['name'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="category" class="form-control" placeholder="Category" required value="<?= $edit_product['category'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required value="<?= $edit_product['price'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="description" class="form-control" placeholder="Description" value="<?= $edit_product['description'] ?? '' ?>">
                </div>
                <div class="col-md-1">
                    <?php if ($edit_product): ?>
                        <button type="submit" name="edit" class="btn btn-warning">Update</button>
                    <?php else: ?>
                        <button type="submit" name="add" class="btn btn-success">Add</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Description</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $products->fetch_assoc()): ?>
            <tr>
                <td><?= $row['product_id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td>₱<?= number_format($row['price'],2) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td>
                    <a href="?edit=<?= $row['product_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="?delete=<?= $row['product_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
<?php $conn->close(); ?>