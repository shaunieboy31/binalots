<?php
session_start();
$totalAmount = isset($_SESSION['total']) ? floatval($_SESSION['total']) : 0.00;

// Fetch products from DB
$products = [];
$conn = new mysqli("localhost", "root", "", "binalots");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$res = $conn->query("SELECT * FROM products");
while($row = $res->fetch_assoc()) {
    $products[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>POS System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #284b25; color: white; }
    .panel { background-color: #2e6733; padding: 20px; border-radius: 10px; }
    .btn-green { background-color: #4CAF50; color: white; }
    .btn-green:hover { background-color: #45a049; }
    .display { background-color: #fff; color: #000; padding: 10px; border-radius: 5px; text-align: right; font-size: 1.5rem; }
    .table-container { background-color: #2e6733; padding: 10px; border-radius: 10px; }
    .order-table td { vertical-align: middle; }
    .input-qty { width: 50px; text-align: center; }
    #receiptContent { font-family: monospace; font-size: 15px; color: #222; }
    #receiptContent table { width: 100%; }
    #receiptContent th, #receiptContent td { padding: 2px 4px; }
    #numpad {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      max-width: 220px;
      margin: 0 auto;
      justify-content: center;
    }
    .numpad-btn {
      width: 60px;
      height: 50px;
      font-size: 1.5rem;
      background: #eee;
      color: #222;
      border: 1px solid #bbb;
      border-radius: 8px;
      text-align: center;
      cursor: pointer;
      user-select: none;
      margin-bottom: 5px;
    }
    .numpad-btn:active {
      background: #ccc;
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
              <th>#</th>
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
        <button class="btn btn-warning" onclick="managerAccess('products_crud.php')">MANAGE PRODUCTS</button>
        <a href="login.php" class="btn btn-secondary">LOG OUT</a>
      </div>
    </div>

    <!-- Items Display -->
    <div class="col-md-6">
      <div class="panel text-center">
        <h5>Items</h5>
        <div id="bestSellers" class="row"></div>
      </div>
    </div>

    <!-- Total and Payment -->
    <div class="col-md-3">
      <div class="panel">
        <h5>Total: ₱<span id="total">0.00</span></h5>
        <button class="btn btn-green w-100 mt-3" onclick="showPaymentModal()">Proceed to Payment</button>
        <button class="btn btn-info w-100 mt-3" onclick="managerAccess('history.php')">History</button>
      </div>
    </div>
  </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalLabel">Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="paymentForm" onsubmit="return processPayment();">
          <div class="mb-3">
            <label for="paymentMethod" class="form-label">Payment Method</label>
            <select class="form-select" id="paymentMethod" required>
              <option value="">Select method</option>
              <option value="Cash">Cash</option>
              <option value="GCash">GCash</option>
              <option value="Card">Card</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="amountTendered" class="form-label">Amount Tendered</label>
            <div id="gcashQR" class="text-center mb-2" style="display:none;">
                <label class="form-label">Scan to Pay with GCash</label><br>
                <img src="assets/gcash_qr.png" alt="GCash QR" style="max-width:180px; border:2px solid #2e6733; border-radius:10px;">
            </div>
            <input type="text" class="form-control" id="amountTendered" required style="background:#fff; color:#222; cursor:pointer;">
            <div id="amountTenderedError" class="text-danger"></div>
            <div id="numpad" class="mt-2"></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Receipt Preview</label>
            <div id="receiptContent" class="border rounded bg-light p-2" style="color:#222;max-height:200px;overflow:auto;"></div>
          </div>
          <div class="mb-3">
            <span id="changeDue" class="fw-bold"></span>
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-success" id="confirmPaymentBtn" disabled>Confirm Payment</button>
            <button type="button" class="btn btn-secondary" onclick="printReceipt()">Print Receipt</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Manager Code Modal -->
<div class="modal fade" id="managerModal" tabindex="-1" aria-labelledby="managerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="managerModalLabel">Manager Access</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="managerCodeForm" onsubmit="return checkManagerCode();">
          <div class="mb-3">
            <label for="managerCodeInput" class="form-label">Enter Manager Code</label>
            <input type="password" class="form-control" id="managerCodeInput" required autofocus>
            <div id="managerCodeError" class="text-danger mt-2" style="display:none;">Incorrect code. Access denied.</div>
          </div>
          <input type="hidden" id="managerTargetUrl">
          <button type="submit" class="btn btn-primary w-100">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  const totalElement = document.getElementById('total');
  const itemTable = document.getElementById('itemTable');
  const bestSellers = document.getElementById('bestSellers');
  let order = [];

  // Dynamically generated categories from PHP
  const categories = {};
  <?php foreach($products as $prod): ?>
    if (!categories['<?= $prod['category'] ?>']) categories['<?= $prod['category'] ?>'] = [];
    categories['<?= $prod['category'] ?>'].push({
      name: "<?= addslashes($prod['name']) ?>",
      price: <?= floatval($prod['price']) ?>
    });
  <?php endforeach; ?>

  function loadCategory(category) {
    const items = categories[category] || [];
    let html = '';
    if (items.length === 0) {
      html = '<div class="col-12 text-center text-muted">No products in this category.</div>';
    } else {
      items.forEach((item, idx) => {
        // Prepare image path (lowercase, no spaces)
        const imgName = item.name.toLowerCase().replace(/\s+/g, '') + '.jpg';
        const imgPath = `assets/${imgName}`;
        html += `
          <div class="col-6 mb-3">
            <button class="w-100 p-0 border-0 product-btn"
              style="
                height:110px;
                background: #218838;
                border-radius: 12px;
                overflow: hidden;
                position: relative;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
              "
              onclick="addToOrder('${category}', ${idx})">
              <img src="${imgPath}" alt="${item.name}" 
                style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:1;"
                onerror="this.style.display='none';"
              >
              <div style="position:relative;z-index:2;width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(0,0,0,0.35);">
                <span style="font-size:1.08rem;font-weight:bold;color:#fff;text-shadow:0 1px 3px #000;">${item.name}</span>
                <span style="font-size:0.98rem;color:#fff;text-shadow:0 1px 3px #000;">₱${item.price.toFixed(2)}</span>
              </div>
            </button>
          </div>
        `;
      });
    }
    bestSellers.innerHTML = html;
  }

  function addToOrder(category, idx) {
    const item = categories[category][idx];
    // Check if item already in order
    const found = order.find(o => o.name === item.name);
    if (found) {
      found.qty += 1;
      found.total = found.qty * found.price;
    } else {
      order.push({ name: item.name, price: item.price, qty: 1, total: item.price });
    }
    updateOrderTable();
  }

  function removeFromOrder(idx) {
    order.splice(idx, 1);
    updateOrderTable();
  }

  function updateOrderTable() {
    let html = '';
    let total = 0;
    if (order.length === 0) {
      html = '<tr id="emptyRow"><td colspan="5" class="text-center">No items yet</td></tr>';
    } else {
      order.forEach((item, idx) => {
        total += item.total;
        html += `
          <tr>
            <td>${idx + 1}</td>
            <td>${item.name}</td>
            <td>
              <button class="btn btn-sm btn-secondary" onclick="changeQty(${idx}, -1)">-</button>
              <span class="mx-2">${item.qty}</span>
              <button class="btn btn-sm btn-secondary" onclick="changeQty(${idx}, 1)">+</button>
            </td>
            <td>₱${item.total.toFixed(2)}</td>
            <td><button class="btn btn-danger btn-sm" onclick="removeFromOrder(${idx})">Remove</button></td>
          </tr>
        `;
      });
    }
    itemTable.innerHTML = html;
    totalElement.textContent = total.toFixed(2);
  }

  function changeQty(idx, delta) {
    order[idx].qty += delta;
    if (order[idx].qty <= 0) {
      order.splice(idx, 1);
    } else {
      order[idx].total = order[idx].qty * order[idx].price;
    }
    updateOrderTable();
  }

  function showPaymentModal() {
    // Generate receipt preview
    let receipt = `<div><strong>Receipt No:</strong> ${document.getElementById('receiptNumber').textContent}<br>`;
    receipt += `<strong>Operator:</strong> ${document.getElementById('operatorName').textContent}<br>`;
    receipt += `<table class="w-100"><thead><tr><th>Item</th><th>Qty</th><th>Price</th></tr></thead><tbody>`;
    order.forEach(item => {
      receipt += `<tr><td>${item.name}</td><td>${item.qty}</td><td>₱${item.total.toFixed(2)}</td></tr>`;
    });
    receipt += `</tbody></table>`;
    receipt += `<div class="mt-2"><strong>Total:</strong> ₱${totalElement.textContent}</div></div>`;
    document.getElementById('receiptContent').innerHTML = receipt;
    document.getElementById('amountTendered').value = '';
    document.getElementById('amountTenderedError').textContent = '';
    document.getElementById('changeDue').textContent = '';
    document.getElementById('paymentMethod').value = '';
    document.getElementById('confirmPaymentBtn').disabled = true;
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
  }

  function processPayment() {
    const amount = parseFloat(document.getElementById('amountTendered').value);
    const total = parseFloat(totalElement.textContent);
    const method = document.getElementById('paymentMethod').value;
    if (isNaN(amount) || amount < total || amount < 0) {
      document.getElementById('changeDue').textContent = "Insufficient or invalid amount!";
      return false;
    }
    const change = amount - total;
    document.getElementById('changeDue').textContent = `Change: ₱${change.toFixed(2)} | Paid by: ${method}`;

    // --- Save order to database via AJAX ---
    fetch('save_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'order=' + encodeURIComponent(JSON.stringify(order)) +
            '&payment_method=' + encodeURIComponent(document.getElementById('paymentMethod').value)
    })
    .then(res => res.text())
    .then(data => {
      // Optionally show a message: data
      setTimeout(() => {
        order = [];
        updateOrderTable();
        var modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
        modal.hide();
      }, 2000);
    });

    return false; // Prevent form submit
  }

  function printReceipt() {
    const receiptContent = document.getElementById('receiptContent').innerHTML;
    const win = window.open('', '', 'width=400,height=600');
    win.document.write(`
      <html>
        <head>
          <title>Receipt</title>
          <style>
            body { font-family: monospace; color: #222; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 2px 4px; border-bottom: 1px solid #ccc; }
            .total { font-weight: bold; }
          </style>
        </head>
        <body>
          ${receiptContent}
        </body>
      </html>
    `);
    win.document.close();
    win.focus();
    win.print();
    win.close();
  }

  // --- Numpad Logic ---
  function renderNumpad() {
    const numpad = document.getElementById('numpad');
    if (!numpad) return;
    const keys = [
      '7','8','9',
      '4','5','6',
      '1','2','3',
      '.','0','←'
    ];
    let html = '';
    keys.forEach(key => {
      html += `<button type="button" class="numpad-btn" onclick="numpadPress('${key}')">${key}</button>`;
    });
    numpad.innerHTML = html;
  }
  function numpadPress(key) {
    const input = document.getElementById('amountTendered');
    if (key === '←') {
      input.value = input.value.slice(0, -1);
    } else if (key === '.') {
      if (!input.value.includes('.')) input.value += '.';
    } else {
      input.value += key;
    }
    validatePaymentAmount();
  }
  document.addEventListener('DOMContentLoaded', renderNumpad);

  // --- Manager Modal Logic ---
  function managerAccess(url) {
    document.getElementById('managerCodeInput').value = '';
    document.getElementById('managerCodeError').style.display = 'none';
    document.getElementById('managerTargetUrl').value = url;
    var modal = new bootstrap.Modal(document.getElementById('managerModal'));
    modal.show();
    setTimeout(() => {
      document.getElementById('managerCodeInput').focus();
    }, 500);
  }

  function checkManagerCode() {
    const code = document.getElementById('managerCodeInput').value;
    const url = document.getElementById('managerTargetUrl').value;
    if (code === "2222") {
      document.getElementById('managerCodeError').style.display = 'none';
      var modal = bootstrap.Modal.getInstance(document.getElementById('managerModal'));
      modal.hide();
      window.location.href = url;
    } else {
      document.getElementById('managerCodeError').style.display = 'block';
    }
    return false; // Prevent form submit
  }

  // --- Payment Amount Validation ---
  function validatePaymentAmount() {
    const amountInput = document.getElementById('amountTendered');
    const amountStr = amountInput.value.trim();
    const amount = parseFloat(amountStr);
    const total = parseFloat(document.getElementById('total').textContent);
    const method = document.getElementById('paymentMethod').value;
    const btn = document.getElementById('confirmPaymentBtn');
    const errorDiv = document.getElementById('amountTenderedError');
    let valid = true;
    let errorMsg = "";

    if (!method) {
      valid = false;
      errorMsg = "";
    } else if (!amountStr || isNaN(amount)) {
      valid = false;
      errorMsg = "Please enter a valid amount.";
    } else if (amount < 0) {
      valid = false;
      errorMsg = "Negative amount is not allowed.";
      amountInput.value = "";
    } else if (amount < total) {
      valid = false;
      errorMsg = "Insufficient amount!";
    }

    btn.disabled = !valid;
    errorDiv.textContent = errorMsg;
  }

  document.getElementById('paymentMethod').addEventListener('change', function() {
    const method = this.value;
    document.getElementById('gcashQR').style.display = (method === 'GCash') ? 'block' : 'none';
    document.getElementById('amountTendered').value = '';
    validatePaymentAmount();
  });
  document.getElementById('amountTendered').addEventListener('input', validatePaymentAmount);

  // Load default category on page load
  window.onload = function() {
    loadCategory('silog');
  };
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>