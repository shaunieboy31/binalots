<?php
$conn = new mysqli("localhost", "root", "", "binalots");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Allowed categories for strict structure
$allowed_categories = [
    'silog' => 'SILOG MEALS',
    'family' => 'FAMILY MEALS',
    'sizzling' => 'SIZZLING PLATES',
    'beverages' => 'BEVERAGES',
    'addons' => 'ADD-ONS',
    'special' => 'SPECIAL DEALS'
];

// Pagination setup
$per_page = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// Sorting setup
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'product_id';
$sort_dir = isset($_GET['sort_dir']) && strtolower($_GET['sort_dir']) === 'asc' ? 'ASC' : 'DESC';
$allowed_sort = ['product_id', 'name', 'category', 'price'];
if (!in_array($sort_by, $allowed_sort)) $sort_by = 'product_id';

// Handle Import CSV
if (isset($_POST['import']) && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        $header = fgetcsv($handle);
        $special_found = false;
        $rows = [];
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $name = $conn->real_escape_string($data[0]);
            $category = strtolower(trim($data[1]));
            $price = floatval($data[2]);
            $desc = $conn->real_escape_string($data[3]);
            if (array_key_exists($category, $allowed_categories)) {
                if ($category === 'special') $special_found = true;
                $rows[] = [$name, $category, $price, $desc];
            }
        }
        fclose($handle);

        // Delete all existing special deals ONCE if any special found
        if ($special_found) {
            $conn->query("DELETE FROM products WHERE category='special'");
        }

        // Insert all products
        foreach ($rows as $row) {
            list($name, $category, $price, $desc) = $row;
            $conn->query("INSERT INTO products (name, category, price, description) VALUES ('$name', '$category', $price, '$desc')");
        }

        header("Location: home.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Failed to open the file.</div>";
    }
}

// Handle Add
if (isset($_POST['add'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $category = strtolower(trim($conn->real_escape_string($_POST['category'])));
    $price = floatval($_POST['price']);
    $desc = $conn->real_escape_string($_POST['description']);
    if (array_key_exists($category, $allowed_categories)) {
        $conn->query("INSERT INTO products (name, category, price, description) VALUES ('$name', '$category', $price, '$desc')");
        header("Location: home.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Invalid category selected.</div>";
    }
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
    $category = strtolower(trim($conn->real_escape_string($_POST['category'])));
    $price = floatval($_POST['price']);
    $desc = $conn->real_escape_string($_POST['description']);
    if (array_key_exists($category, $allowed_categories)) {
        $conn->query("UPDATE products SET name='$name', category='$category', price=$price, description='$desc' WHERE product_id=$id");
    } else {
        echo "<div class='alert alert-danger'>Invalid category selected.</div>";
    }
}

// Pagination: get total count
$count_result = $conn->query("SELECT COUNT(*) as total FROM products");
$total = $count_result ? $count_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total / $per_page);

// Fetch products for current page and sort
$products = $conn->query("SELECT * FROM products ORDER BY $sort_by $sort_dir LIMIT $per_page OFFSET $offset");
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
    <!-- Import CSV Form -->
    <form method="POST" enctype="multipart/form-data" class="mb-3">
        <input type="file" name="excel_file" accept=".csv" required>
        <button type="submit" name="import" class="btn btn-info">Import CSV</button>
        <span class="text-light ms-2"> Enter Special Deals | Category must be: special | Format : name,category,price,description</span>
    </form>
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
                    <select name="category" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach($allowed_categories as $cat_val => $cat_label): ?>
                            <option value="<?= $cat_val ?>" <?= (isset($edit_product['category']) && $edit_product['category']==$cat_val) ? 'selected' : '' ?>>
                                <?= $cat_label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                <th><a href="?sort_by=product_id&sort_dir=<?= $sort_by=='product_id'&&$sort_dir=='ASC'?'desc':'asc' ?>">ID</a></th>
                <th><a href="?sort_by=name&sort_dir=<?= $sort_by=='name'&&$sort_dir=='ASC'?'desc':'asc' ?>">Name</a></th>
                <th><a href="?sort_by=category&sort_dir=<?= $sort_by=='category'&&$sort_dir=='ASC'?'desc':'asc' ?>">Category</a></th>
                <th><a href="?sort_by=price&sort_dir=<?= $sort_by=='price'&&$sort_dir=='ASC'?'desc':'asc' ?>">Price</a></th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $products->fetch_assoc()): ?>
            <tr>
                <td><?= $row['product_id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= isset($allowed_categories[$row['category']]) ? $allowed_categories[$row['category']] : htmlspecialchars($row['category']) ?></td>
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
    <!-- Pagination Links -->
    <nav>
      <ul class="pagination justify-content-center">
        <?php for($i=1; $i<=$total_pages; $i++): ?>
          <li class="page-item <?= ($i==$page)?'active':'' ?>">
            <a class="page-link" href="?page=<?= $i ?>&sort_by=<?= $sort_by ?>&sort_dir=<?= strtolower($sort_dir) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
</div>
</body>
</html>
<?php
$conn->close();
?>