<?php
require_once __DIR__ . '/../../src/auth_admin.php';
require_once __DIR__ . '/../../config/db.php';

$order_id = intval($_GET['id']);

$order = $mysqli->query("
SELECT o.*, u.name
FROM orders o
JOIN users u ON o.user_id = u.id
WHERE o.id = $order_id
")->fetch_assoc();

$items = $mysqli->query("
SELECT oi.*, p.name
FROM order_items oi
JOIN products p ON oi.product_id = p.id
WHERE oi.order_id = $order_id
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Order Details</title>
<link rel="stylesheet" href="../css/admin.css">
</head>

<body class="admin-page">
<div class="admin-shell">

<header class="admin-navbar">
<div class="admin-nav-left">
<a href="index.php" class="admin-logo">
<span class="logo-dark">Blink</span><span class="logo-yellow">Hub</span>
</a>
<span class="logo-badge">Admin Panel</span>
</div>

<div class="admin-nav-right">
<span class="admin-welcome">
Logged in as <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>
</span>
<a href="logout.php" class="admin-btn logout-btn">Logout</a>
</div>
</header>

<div class="admin-body">

<aside class="admin-sidebar">
<div class="sidebar-title">Navigation</div>
<nav class="admin-menu">
<a href="index.php" class="menu-item">Dashboard</a>
<a href="products.php" class="menu-item">Products</a>
<a href="users.php" class="menu-item">Users</a>
<a href="orders.php" class="menu-item active">Orders</a>
</nav>
</aside>

<main class="admin-main">

<h1 class="page-title">Order #<?= $order['id'] ?></h1>

<p><b>Customer Name:</b> <?= htmlspecialchars($order['name']) ?></p>
<p><b>Address:</b> <?= $order['address'] ?></p>
<h3>Products:</h3>

<table class="admin-table">
<tr>
<th>Product</th>
<th>Qty</th>
<th>Price</th>
<th>Total</th>
</tr>

<?php while($item = $items->fetch_assoc()): ?>

<tr>
<td><?= htmlspecialchars($item['name']) ?></td>
<td><?= $item['qty'] ?></td>
<td>₹<?= $item['unit_price'] ?></td>
<td>₹<?= $item['line_total'] ?></td>
</tr>

<?php endwhile; ?>

</table>
<?php
$subtotal = 0;

foreach ($items as $item) {
    $subtotal += $item['line_total'];
}

$gst = $subtotal * 0.05;
$delivery = 15;
?>
<p><strong>Subtotal:</strong> ₹<?= $subtotal ?></p>
<p><strong>GST (5%):</strong> ₹<?= number_format($gst,2) ?></p>
<p><strong>Delivery Fee:</strong> ₹<?= $delivery ?></p>
<p><strong>Total:</strong> ₹<?= $order['total'] ?></p>

<?php
$status_class = "status-confirmed";

if($order['order_status']=="Out for Delivery"){
$status_class="status-out";
}

if($order['order_status']=="Delivered"){
$status_class="status-delivered";
}

$payment_class = ($order['payment_status']=="Paid") ? "payment-paid" : "payment-pending";
?>

<p><b>Payment Method:</b> <?= $order['payment_method'] ?></p>

<p>
<strong>Payment Status:</strong>
<span class="<?= $payment_class ?>">
<?= $order['payment_status'] ?>
</span>
</p>

<h3>Update Order Status</h3>

<form action="update-order-status.php" method="POST">
<input type="hidden" name="order_id" value="<?= $order['id'] ?>">

<select name="status">

<option value="Pending" 
<?= ($order['order_status'] == 'Pending') ? 'selected' : '' ?>>
Pending
</option>

<option value="Out for Delivery" 
<?= ($order['order_status'] == 'Out for Delivery') ? 'selected' : '' ?>>
Out for Delivery
</option>

<option value="Delivered" 
<?= ($order['order_status'] == 'Delivered') ? 'selected' : '' ?>>
Delivered
</option>

</select>

<button class="admin-btn primary">Update</button>

</form>

</main>
</div>
</div>
</body>
</html>