<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$user = $_SESSION['user'] ?? null;

if (!$user) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$user['id'];

/* GET ORDERS OF USER */
$sql = "
SELECT 
    o.id,
    o.total,
    o.order_status,
    o.created_at,
    o.payment_method,
    o.payment_status,
    o.address
FROM orders o
WHERE o.user_id = $user_id
ORDER BY o.created_at DESC
";

$result = $mysqli->query($sql);
$orders = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {

        $order_id = $row['id'];

        /* GET PRODUCTS FOR EACH ORDER */
        $items_sql = "
        SELECT 
            oi.qty,
            oi.unit_price,
            p.name
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = $order_id
        ";

        $items_result = $mysqli->query($items_sql);
        $items = [];

        if ($items_result) {
            while ($item = $items_result->fetch_assoc()) {
                $items[] = $item;
            }
        }

        $row['items'] = $items;
        $orders[] = $row;
    }
}

// CANCEL ORDER
if(isset($_GET['cancel_id'])){

$order_id = intval($_GET['cancel_id']);

/* verify order belongs to user */
$stmt = $mysqli->prepare("SELECT id FROM orders WHERE id=? AND user_id=?");
$stmt->bind_param("ii",$order_id,$user_id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows > 0){

$update = $mysqli->prepare("UPDATE orders SET order_status='Cancelled', payment_status='Cancelled' WHERE id=?");
$update->bind_param("i",$order_id);
$update->execute();

header("Location: my-orders.php?toast=cancelled");
exit;
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Orders - BlinkHub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="page">
<header class="navbar">
<div class="nav-left">
<a href="index.php" class="logo">
<span class="logo-dark">Blink</span>
<span class="logo-yellow">Hub</span>
</a>
</div>

<div class="nav-center">
<div class="search-box">
<span class="search-icon">🔍</span>
<input type="text" placeholder="Search products">
</div>
</div>

<div class="nav-right">

<span class="nav-user">
Hi, <?= htmlspecialchars($user['name'] ?? $user['email']) ?>
</span>

<a href="logout.php" class="nav-btn ghost">Logout</a>

<a href="wishlist.php" class="nav-btn ghost">
🤍 Wishlist
</a>

<a href="cart.php" class="nav-btn cart-btn">
🛒 Cart
</a>

</div>
</header>

<div class="orders-container">
<?php if(isset($_GET['cancel'])): ?>

<div class="cancel-message">
Order Cancelled Successfully
</div>

<?php endif; ?>
<h2 class="orders-title">My Orders</h2>

<?php if(empty($orders)): ?>
<p style="color:white;">You haven't placed any orders yet.</p>

<?php else: ?>

<?php foreach($orders as $order): ?>
<?php
$subtotal = $order['total'] / 1.05 - 15; 
$gst = $subtotal * 0.05;
$delivery = 15;
?>

<?php
$status_class = "status-confirmed";

if($order['order_status'] == "Out for Delivery"){
$status_class = "status-out";
}

if($order['order_status'] == "Delivered"){
$status_class = "status-delivered";
}

$payment_class = ($order['payment_status'] == "Paid") ? "payment-paid" : "payment-pending";
?>

<div class="order-card">

<div class="order-top">
<span>Order ID: #<?= $order['id'] ?></span>
<span><?= date("d M Y, h:i A", strtotime($order['created_at'])) ?></span>
</div>

<ul class="order-products">

<?php foreach($order['items'] as $item): ?>

<li>
<?= $item['qty'] ?> × <?= htmlspecialchars($item['name']) ?>
(₹<?= $item['unit_price'] ?>)
</li>

<?php endforeach; ?>

</ul>

<div class="order-info">

<p><strong>Subtotal:</strong> ₹<?= number_format($subtotal,2) ?></p>
<p><strong>GST (5%):</strong> ₹<?= number_format($gst,2) ?></p>
<p><strong>Delivery Fee:</strong> ₹<?= $delivery ?></p>
<p><strong>Total:</strong> ₹<?= number_format($order['total'],2) ?></p>
<p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
<p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>

<p>
<strong>Payment Status:</strong> 
<span class="<?= $payment_class ?>">
<?= htmlspecialchars($order['payment_status']) ?>
</span>
</p>

<p class="status <?= $status_class ?>">
Order Status: <?= htmlspecialchars($order['order_status'] ?: 'Confirmed') ?>
</p>
<?php if($order['order_status'] != 'Delivered' && $order['order_status'] != 'Cancelled'): ?>

<a href="my-orders.php?cancel_id=<?= $order['id'] ?>" 
class="cancel-btn">
Cancel Order
</a>

<?php endif; ?>
</div>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>

</div>
<?php if(isset($_GET['toast']) && $_GET['toast']=="cancelled"): ?>

<script>
showToast(`Cancelled order`);
</script>

<?php endif; ?>
</body>
</html>