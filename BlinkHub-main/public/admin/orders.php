<?php
require_once __DIR__ . '/../../src/auth_admin.php';
require_once __DIR__ . '/../../config/db.php';

$orders = $mysqli->query("
SELECT o.*, u.name AS user_name
FROM orders o
JOIN users u ON o.user_id = u.id
ORDER BY o.id DESC
");

/* DELETE ORDER */

if(isset($_GET['delete_id'])){

$order_id = intval($_GET['delete_id']);

/* delete order items first */
$stmt = $mysqli->prepare("DELETE FROM order_items WHERE order_id=?");
$stmt->bind_param("i",$order_id);
$stmt->execute();

/* delete order */
$stmt2 = $mysqli->prepare("DELETE FROM orders WHERE id=?");
$stmt2->bind_param("i",$order_id);
$stmt2->execute();

/* reload page */
header("Location: orders.php");
exit;

}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Orders – BlinkHub Admin</title>
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

<h1 class="page-title">Manage Orders</h1>

<div class="admin-card">

<table class="admin-table">
<thead>
<tr>
<th>Order ID</th>
<th>Customer</th>
<th>Order Status</th>
<th>Payment Status</th>
<th>Action</th>
<th>Delete</th>
</tr>
</thead>

<tbody>
<?php while($row = $orders->fetch_assoc()): ?>

<tr>

<td>#<?= $row['id'] ?></td>

<td><?= htmlspecialchars($row['user_name']) ?></td>

<?php
$status_class = "status-confirmed";

if($row['order_status']=="Out for Delivery"){
$status_class = "status-out";
}

if($row['order_status']=="Delivered"){
$status_class = "status-delivered";
}

$payment_class = ($row['payment_status']=="Paid") ? "payment-paid" : "payment-pending";
?>

<td>
<span class="status-badge <?= $status_class ?>">
<?= $row['order_status'] ?>
</span>
</td>

<td class="<?= $payment_class ?>">
<?= $row['payment_status'] ?>
</td>

<td>
<a class="admin-btn ghost" href="order-view.php?id=<?= $row['id'] ?>">
View
</a>
</td>

<td>
<a class="delete-btn"
href="orders.php?delete_id=<?= $row['id'] ?>"
onclick="return confirm('Delete this order?')">
Delete
</a>
</td>

</tr>

<?php endwhile; ?>
</tbody>

</table>

</div>

</main>