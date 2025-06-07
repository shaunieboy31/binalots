<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory and Purchase History</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #284b25;
            color: white;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 20px;
        }

        .section-header {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
        }

        .inventory-panel,
        .history-panel {
            background-color: #a7a7a7;
            border-radius: 10px;
            padding: 20px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .search-bar input {
            flex-grow: 1;
            margin-right: 10px;
        }

        .out-of-stock {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }

        .in-stock {
            color: green;
            font-weight: bold;
            margin-top: 10px;
        }

        table {
            width: 100%;
        }

        table th,
        table td {
            padding: 8px;
            text-align: left;
            cursor: pointer;
        }

        table th.sortable:hover {
            background-color: #525252;
            color: white;
        }

        .history-panel th {
            text-align: center;
        }

        @media (max-width: 768px) {
            .section-header {
                font-size: 1.2rem;
            }

            .search-bar {
                flex-wrap: wrap;
            }

            .search-bar input {
                margin-right: 0;
                margin-bottom: 10px;
                width: 100%;
            }

            .search-bar button {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .inventory-panel,
            .history-panel {
                padding: 15px;
            }

            table th,
            table td {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row gy-4">
            <!-- Inventory Section -->
            <div class="col-lg-6">
                <div class="inventory-panel">
                    <h3 class="section-header">Inventory</h3>
                    <div class="search-bar">
                        <input type="text" id="searchInventory" class="form-control" placeholder="Search inventory..." value="">
                        <button class="btn btn-light" onclick="searchInventory()">Search</button>
                    </div>
                    <form id="inventoryForm">
                        <p>
                            <strong>Product Code:</strong>
                            <input type="text" id="productCode" class="form-control" value="" readonly>
                        </p>
                        <p>
                            <strong>Description:</strong>
                            <input type="text" id="description" class="form-control" value="" readonly>
                        </p>
                        <p>
                            <strong>Group:</strong>
                            <input type="text" id="group" class="form-control" value="" readonly>
                        </p>
                        <p>
                            <strong>Product:</strong>
                            <input type="text" id="product" class="form-control" value="" readonly>
                        </p>
                        <p>
                            <strong>QTY per pack:</strong>
                            <input type="number" id="qtyPerPack" class="form-control" value="" readonly>
                        </p>
                        <p>
                            <strong>KG per pack:</strong>
                            <input type="text" id="kgPerPack" class="form-control" value="" readonly>
                        </p>
                        <p id="stockStatus" class="out-of-stock"></p>
                    </form>
                </div>
            </div>

            <!-- Purchase History Section -->
            <div class="col-lg-6">
                <div class="history-panel">
                    <h3 class="section-header">Purchase History</h3>
                    <div class="search-bar">
                        <input type="text" id="searchHistory" class="form-control" placeholder="Search history...">
                        <button class="btn btn-light" onclick="searchHistory()">Search</button>
                    </div>
                    <table class="table table-striped text-white">
                        <thead>
                            <tr>
                                <th class="sortable" onclick="sortTable(0)">Receipt No.</th>
                                <th class="sortable" onclick="sortTable(1)">Date</th>
                                <th class="sortable" onclick="sortTable(2)">Items/Products</th>
                                <th class="sortable" onclick="sortTable(3)">QTY</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseHistoryTable">
                            <tr>
                                <td>1001</td>
                                <td>10/02/24</td>
                                <td>Tapsilog</td>
                                <td>0</td>
                                <td>₱70.00</td>
                            </tr>
                            <tr>
                                <td>1002</td>
                                <td>10/02/24</td>
                                <td>Bangus silog</td>
                                <td>3</td>
                                <td>₱135.00</td>
                            </tr>
                            <tr>
                                <td>1003</td>
                                <td>10/02/24</td>
                                <td>Sizzling Sisig</td>
                                <td>3</td>
                                <td>₱140.00</td>
                            </tr>
                            <tr>
                                <td>1004</td>
                                <td>10/03/24</td>
                                <td>Adobo</td>
                                <td>2</td>
                                <td>₱150.00</td>
                            </tr>
                            <tr>
                                <td>1005</td>
                                <td>10/03/24</td>
                                <td>Longsilog</td>
                                <td>5</td>
                                <td>₱200.00</td>
                            </tr>
                            <tr>
                                <td>1006</td>
                                <td>10/04/24</td>
                                <td>Bulalo</td>
                                <td>4</td>
                                <td>₱250.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Product Data
        const products = {
            tapsilog: {
                code: "Tapsilog",
                description: "Tapa-Sinangag-Itlog",
                group: "Silog",
                product: "Tapa",
                qtyPerPack: 1,
                kgPerPack: "1 kg",
                inStock: false
            },
            bangsilog: {
                code: "Bangsilog",
                description: "Bangus-Sinangag-Itlog",
                group: "Silog",
                product: "Bangus",
                qtyPerPack: 2,
                kgPerPack: "2 kg",
                inStock: true
            }
        };

        // Search Inventory Function
        function searchInventory() {
            const query = document.getElementById("searchInventory").value.toLowerCase();
            const product = products[query];

            if (product) {
                document.getElementById("productCode").value = product.code;
                document.getElementById("description").value = product.description;
                document.getElementById("group").value = product.group;
                document.getElementById("product").value = product.product;
                document.getElementById("qtyPerPack").value = product.qtyPerPack;
                document.getElementById("kgPerPack").value = product.kgPerPack;

                const stockStatus = document.getElementById("stockStatus");
                if (product.inStock) {
                    stockStatus.textContent = "IN STOCK!";
                    stockStatus.className = "in-stock";
                } else {
                    stockStatus.textContent = "OUT OF STOCK!";
                    stockStatus.className = "out-of-stock";
                }
            } else {
                alert("Product not found!");
            }
        }

        // Search History Functionality
        function searchHistory() {
            const query = document.getElementById("searchHistory").value.toLowerCase();
            const table = document.getElementById("purchaseHistoryTable");
            const rows = table.getElementsByTagName("tr");

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName("td");
                let match = false;

                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(query)) {
                        match = true;
                        break;
                    }
                }
                rows[i].style.display = match ? "" : "none";
            }
        }

        // Sort Table Function
        function sortTable(columnIndex) {
            const table = document.getElementById("purchaseHistoryTable");
            const rows = Array.from(table.getElementsByTagName("tr"));
            const isNumeric = columnIndex === 0 || columnIndex === 3; // Numeric columns: Receipt No. and QTY

            rows.sort((a, b) => {
                const aText = a.cells[columnIndex].textContent.trim();
                const bText = b.cells[columnIndex].textContent.trim();

                if (isNumeric) {
                    return parseFloat(aText) - parseFloat(bText);
                } else {
                    return aText.localeCompare(bText);
                }
            });

            rows.forEach(row => table.appendChild(row));
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
