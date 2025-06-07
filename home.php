<?php

session_start();
// Initialize total amount and order data
$totalAmount = isset($_SESSION['total']) ? floatval($_SESSION['total']) : 0.00;
$_SESSION['order'] = isset($_SESSION['order']) ? $_SESSION['order'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>POS System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #284b25;
      color: white;
    }
    .panel {
      background-color: #2e6733;
      padding: 20px;
      border-radius: 10px;
    }
    .btn-green {
      background-color: #4CAF50;
      color: white;
    }
    .btn-green:hover {
      background-color: #45a049;
    }
    .display {
      background-color: #fff;
      color: #000;
      padding: 10px;
      border-radius: 5px;
      text-align: right;
      font-size: 1.5rem;
    }
    .table-container {
      background-color: #2e6733;
      padding: 10px;
      border-radius: 10px;
    }
    .order-table td {
      vertical-align: middle;
    }
    .input-qty {
      width: 50px;
      text-align: center;
    }
    /* Receipt styling */
    #receiptContent {
      font-family: monospace;
      font-size: 15px;
      color: #222;
    }
    #receiptContent table {
      width: 100%;
    }
    #receiptContent th, #receiptContent td {
      padding: 2px 4px;
    }
  </style>
</head>
<body>
<div class="container mt-5">
  <div class="row">
    <div class="col-md-12 text-center mb-3">
      <div class="panel">
        <strong>Receipt No:</strong> <span id="receiptNumber"><?= date('YmdHis') ?></span><br>
        Operator: <strong id="operatorName"><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Operator' ?></strong>
      </div>
    </div>
  </div>

  <!-- Order Table -->
  <div class="row mb-3">
    <div class="col-md-12">
      <div class="table-container">
        <table class="table table-dark table-striped mb-0 order-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Item</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="itemTable">
            <tr id="emptyRow"><td colspan="5" class="text-center">No items yet</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Category Buttons -->
    <div class="col-md-3">
      <div class="d-grid gap-2">
        <button class="btn btn-green" onclick="loadCategory('silog')">SILOG MEALS</button>
        <button class="btn btn-green" onclick="loadCategory('family')">FAMILY MEALS</button>
        <button class="btn btn-green" onclick="loadCategory('sizzling')">SIZZLING PLATES</button>
        <button class="btn btn-green" onclick="loadCategory('beverages')">BEVERAGES</button>
        <button class="btn btn-green" onclick="loadCategory('addons')">ADD-ONS</button>
        <a href="login.php" class="btn btn-secondary">LOG OUT</a>
      </div>
    </div>

    <!-- Best Sellers -->
    <div class="col-md-6">
      <div class="panel text-center">
        <h5>Items</h5>
        <div id="bestSellers" class="row"></div>
      </div>
    </div>

    <!-- Total and Payment -->
    <div class="col-md-3">
      <div class="panel">
        <h5>Total: ₱<span id="total"><?= number_format($totalAmount, 2) ?></span></h5>
        <button class="btn btn-green w-100 mt-3" onclick="showPaymentModal()">Proceed to Payment</button>
        <button class="btn btn-info w-100 mt-3" onclick="window.location.href='history.php'">History</button>
      </div>
    </div>
  </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-black">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalLabel">Payment Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Total:</strong> ₱<span id="modalTotal">0.00</span></p>
        <hr>
        <div>
          <label for="paymentMethod">Select Payment Method:</label>
          <select id="paymentMethod" class="form-select" onchange="changePaymentMethod()">
            <option value="cash">Cash</option>
            <option value="gcash">GCash</option>
            <option value="card">Card</option>
          </select>
        </div>
        <!-- Dynamic inputs will be injected here -->
        <div id="paymentDetails"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-green" onclick="completePayment()">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-black">
      <div class="modal-header">
        <h5 class="modal-title" id="receiptModalLabel">Receipt</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="receiptContent">
        <!-- Receipt content will be injected here -->
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-green" onclick="printReceipt()">Print</button>
      </div>
    </div>
  </div>
</div>

<script>
  const totalElement = document.getElementById('total');
  const itemTable = document.getElementById('itemTable');
  const bestSellers = document.getElementById('bestSellers');
  let total = 0;
  let order = []; // Always start with an empty order array

  const categories = {
    silog: [
      { name: "Tapsilog", price: 100 },
      { name: "Tosilog", price: 90 },
      { name: "Longsilog", price: 80 },
      { name: "Bangsilog", price: 110 },
    ],
    family: [
      { name: "Family Feast 1", price: 500 },
      { name: "Family Feast 2", price: 600 },
      { name: "Family Feast 3", price: 550 },
      { name: "Family Feast 4", price: 650 },
    ],
    sizzling: [
      { name: "Sizzling Pork", price: 150 },
      { name: "Sizzling Beef", price: 200 },
      { name: "Sizzling Chicken", price: 180 },
      { name: "Sizzling Fish", price: 160 },
    ],
    beverages: [
      { name: "Iced Tea", price: 40 },
      { name: "Soft Drink", price: 30 },
      { name: "Fruit Juice", price: 50 },
      { name: "Water", price: 20 },
    ],
    addons: [
      { name: "Egg", price: 20 },
      { name: "Rice", price: 10 },
      { name: "Sauce", price: 5 },
    ],
  };

  // Load category items
  function loadCategory(category) {
    bestSellers.innerHTML = '';
    categories[category].forEach(item => {
      const itemDiv = document.createElement('div');
      itemDiv.classList.add('col-6', 'mb-2');
      itemDiv.innerHTML = `<button class="btn btn-dark w-100" onclick="setQtyAndAddItem('${item.name}', ${item.price})">${item.name} - ₱${item.price}</button>`;
      bestSellers.appendChild(itemDiv);
    });
  }

  // Add or increment item in the order array
 function setQtyAndAddItem(name, price) {
  let itemFound = false;
  order.forEach((item, index) => {
    if (item.name === name) {
      itemFound = true;
      order[index].qty += 1;
    }
  });
  if (!itemFound) {
    order.push({ name, price, qty: 1 });
  }
  updateTable();
  updateTotal();
}

  // Update the order table with items
  function updateTable() {
    itemTable.innerHTML = '';
    order.forEach((item, index) => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>#${index + 1}</td>
        <td>${item.name}</td>
        <td><input type="number" class="form-control input-qty" value="${item.qty}" min="1" onchange="updateItemQty(${index}, this.value)" /></td>
        <td>₱${(item.price * item.qty).toFixed(2)}</td>
        <td>
          <button class="btn btn-danger" onclick="deleteItem(${index})">Delete</button>
        </td>
      `;
      itemTable.appendChild(row);
    });
    if (order.length === 0) {
      itemTable.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center">No items yet</td></tr>';
    }
  }

  // Update total price
  function updateTotal() {
    total = order.reduce((sum, item) => sum + item.price * item.qty, 0);
    totalElement.textContent = total.toFixed(2);
    document.getElementById('modalTotal').textContent = total.toFixed(2);  // Sync modal total
  }

  // Show payment modal
  function showPaymentModal() {
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    paymentModal.show();
    changePaymentMethod();
  }

  // Function to handle changes in the payment method
  function changePaymentMethod() {
    const paymentMethod = document.getElementById('paymentMethod').value;
    const paymentDetails = document.getElementById('paymentDetails');

    let inputHtml = '';

    if (paymentMethod === 'cash') {
      inputHtml = `
        <label for="cashAmount">Enter Cash Amount:</label>
        <input type="number" id="cashAmount" class="form-control" placeholder="Enter amount" />
      `;
    } else if (paymentMethod === 'gcash') {
      inputHtml = `
        <label for="gcashReference">Enter GCash Reference Number:</label>
        <input type="text" id="gcashReference" class="form-control" placeholder="Enter reference number" />
      `;
    } else if (paymentMethod === 'card') {
      inputHtml = `
        <label for="cardNumber">Enter Card Number:</label>
        <input type="text" id="cardNumber" class="form-control" placeholder="Enter card number" />
      `;
    }

    paymentDetails.innerHTML = inputHtml;
  }

  // Complete the payment and show receipt
  function completePayment() {
    const paymentMethod = document.getElementById('paymentMethod').value;
    let paymentDetailsText = '';
    let change = 0;

    if (paymentMethod === 'cash') {
      const cashAmount = parseFloat(document.getElementById('cashAmount').value);
      if (isNaN(cashAmount) || cashAmount < total) {
        alert('Please enter a valid cash amount.');
        return;
      }
      change = cashAmount - total;
      paymentDetailsText = `Payment Method: Cash<br>Cash Given: ₱${cashAmount.toFixed(2)}<br>Change: ₱${change.toFixed(2)}`;
    } else if (paymentMethod === 'gcash') {
      const gcashReference = document.getElementById('gcashReference').value;
      if (!gcashReference) {
        alert('Please enter a valid GCash reference number.');
        return;
      }
      paymentDetailsText = `Payment Method: GCash<br>Reference: ${gcashReference}`;
    } else if (paymentMethod === 'card') {
      const cardNumber = document.getElementById('cardNumber').value;
      if (!cardNumber) {
        alert('Please enter a valid card number.');
        return;
      }
      paymentDetailsText = `Payment Method: Card<br>Card Number: ****${cardNumber.slice(-4)}`;
    }

    // Save order to database
    fetch('save_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `order=${JSON.stringify(order)}`
    })
    .then(response => response.text())
    .then(data => console.log(data))
    .catch(error => console.error('Error:', error));

    // Prepare receipt content
    let receipt = `<div style="text-align:center;"><strong>Binalots POS Receipt</strong><br>`;
    receipt += `Receipt No: <?= date('YmdHis') ?><br>`;
    receipt += `Operator: <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Operator' ?><br>`;
    receipt += `--------------------------------<br></div>`;
    receipt += `<table style="width:100%;font-size:14px;">`;
    receipt += `<tr><th style="text-align:left;">Item</th><th>Qty</th><th style="text-align:right;">Price</th></tr>`;
    order.forEach(item => {
      receipt += `<tr><td>${item.name}</td><td style="text-align:center;">${item.qty}</td><td style="text-align:right;">₱${(item.price * item.qty).toFixed(2)}</td></tr>`;
    });
    receipt += `</table>`;
    receipt += `<div style="text-align:right;">--------------------------------<br>`;
    receipt += `<strong>Total: ₱${total.toFixed(2)}</strong><br>`;
    receipt += `${paymentDetailsText}</div>`;

    document.getElementById('receiptContent').innerHTML = receipt;

    // Show receipt modal
    const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    receiptModal.show();

    // Clear the order and update total
    order = [];
    updateTable();
    updateTotal();
    const paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
    paymentModal.hide();
  }

  // Print receipt
  function printReceipt() {
    const printContents = document.getElementById('receiptContent').innerHTML;
    const originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    window.location.reload();
  }

  // Update item quantity
  function updateItemQty(index, newQty) {
    const newQuantity = parseInt(newQty);
    if (newQuantity > 0) {
      order[index].qty = newQuantity;
      updateTable();
      updateTotal();
    }
  }

  // Delete item from order
  function deleteItem(index) {
    order.splice(index, 1);
    updateTable();
    updateTotal();
  }

  // Initialize category view
  loadCategory('silog');
  updateTable();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>