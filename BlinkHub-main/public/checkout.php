<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$user = $_SESSION['user'] ?? null;

if (!$user) {
    header("Location: login.php");
    exit;
}

$uid = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $address = $_POST['address'];
    $payment = $_POST['payment'];
    $cart = json_decode($_POST['cart_data'], true);

    if (!$cart || count($cart) == 0) {
        echo "Cart empty";
        exit;
    }

    $subtotal = 0;

foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['qty'];
}

$gst_rate = 0.05; // 5% GST
$gst = $subtotal * $gst_rate;

$delivery = 15;

$total = $subtotal + $gst + $delivery;

    $payment_status = $payment == "upi" ? "Paid" : "Pending";
    $order_status = "";

    // INSERT ORDER
    $stmt = $mysqli->prepare("
        INSERT INTO orders (user_id,address,total,payment_method,payment_status,order_status)
        VALUES (?,?,?,?,?,?)
    ");

    $stmt->bind_param("isdsss",$uid,$address,$total,$payment,$payment_status,$order_status);
    $stmt->execute();

    $order_id = $stmt->insert_id;

    // INSERT ORDER ITEMS
    foreach ($cart as $item) {

        $line_total = $item['price'] * $item['qty'];

        $stmt2 = $mysqli->prepare("
            INSERT INTO order_items (order_id,product_id,qty,unit_price,line_total)
            VALUES (?,?,?,?,?)
        ");

        $stmt2->bind_param(
            "iiidd",
            $order_id,
            $item['id'],
            $item['qty'],
            $item['price'],
            $line_total
        );

        $stmt2->execute();
    }

    echo "<script>
        const CART_KEY = 'blinkhub_cart_' + $uid;
        localStorage.removeItem(CART_KEY);
        window.location='order-success.php';
        </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>BlinkHub – Checkout</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/style.css">
</head>

<body class="cart-page">

<div class="page">

<header class="navbar">
<div class="nav-left">
<a href="index.php" class="logo">
<span class="logo-dark">Blink</span><span class="logo-yellow">Hub</span>
</a>
</div>

<div class="nav-center">
<div class="search-box">
<span class="search-icon">🔍</span>
<input type="text" placeholder="Search for chips, milk, Coke, bread...">
</div>
</div>

<div class="nav-right">

<?php if ($user): ?>
<span class="nav-user">Hi, <?= htmlspecialchars($user['name'] ?? $user['email']) ?></span>
<a href="logout.php" class="nav-btn ghost">Logout</a>
<?php else: ?>
<a href="login.php" class="nav-btn ghost">👤 Login</a>
<?php endif; ?>

<a href="cart.php" class="nav-btn cart-btn">🛒 Cart</a>

</div>
</header>


<form method="POST" id="checkout-form">

<input type="hidden" name="cart_data" id="cart_data">

<div class="checkout-container">

<!-- LEFT SIDE -->
<div class="checkout-left">

<h2>Checkout</h2>

<div class="delivery-box">
<h3>Deliver To</h3>
<textarea name="address" required placeholder="Enter your full delivery address"></textarea>
</div>

<div class="payment-box">
<h3>Payment Method</h3>

<label class="payment-option">
<input type="radio" name="payment" value="cod" checked>
Cash on Delivery
</label>

<label class="payment-option">
<input type="radio" name="payment" value="upi" id="upi-option">
UPI
</label>

<div id="upi-field" class="upi-box">
<input type="text" placeholder="Enter your UPI ID">
</div>

</div>

</div>


<!-- RIGHT SIDE -->
<div class="checkout-right">

<h3>Order Summary</h3>

<div id="summary-items"></div>

<hr>

<div class="summary-line">
<span>Subtotal</span>
<span id="summary-subtotal">₹0</span>
</div>

<div class="summary-line">
<span>GST (5%)</span>
<span id="summary-gst">₹0</span>
</div>

<div class="summary-line">
<span>Delivery Fee</span>
<span id="summary-delivery">₹15</span>
</div>

<div class="summary-total">
<span>Total</span>
<span id="summary-total">₹0</span>
</div>

<button class="pay-btn" type="submit">Place Order</button>

</div>

</div>

</form>

</div>

<script>
window.BLINKHUB_USER_ID = <?= $user['id'] ?>;
window.BLINKHUB_IS_LOGGED_IN = true;
</script>
<script>

const CART_KEY = window.BLINKHUB_USER_ID
    ? "blinkhub_cart_" + window.BLINKHUB_USER_ID
    : "blinkhub_cart_guest";

function loadSummary() {

const cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];

const container = document.getElementById("summary-items");
const subtotalEl = document.getElementById("summary-subtotal");
const gstEl = document.getElementById("summary-gst");
const deliveryEl = document.getElementById("summary-delivery");
const totalEl = document.getElementById("summary-total");

if(cart.length === 0){
container.innerHTML = "<p>Your cart is empty</p>";
return;
}

let subtotal = 0;
container.innerHTML = "";

cart.forEach(item => {

const lineTotal = item.price * item.qty;
subtotal += lineTotal;

container.innerHTML += `
<div class="summary-item">
<span>${item.qty}x ${item.name}</span>
<span>₹${lineTotal}</span>
</div>
`;

});

const gst = subtotal * 0.05;
const delivery = 15;

subtotalEl.textContent = "₹" + subtotal;
gstEl.textContent = "₹" + gst.toFixed(2);
deliveryEl.textContent = "₹" + delivery;
totalEl.textContent = "₹" + (subtotal + gst + delivery).toFixed(2);

}

loadSummary();

</script>

<script>
window.BLINKHUB_USER_ID = <?= $user['id'] ?>;
window.BLINKHUB_IS_LOGGED_IN = true;
</script>
<script>

document.getElementById("checkout-form").addEventListener("submit", function(){

const CART_KEY = window.BLINKHUB_USER_ID
    ? "blinkhub_cart_" + window.BLINKHUB_USER_ID
    : "blinkhub_cart_guest";

const cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];

document.getElementById("cart_data").value = JSON.stringify(cart);

});

</script>

<script>
window.BLINKHUB_USER_ID = <?= $user['id'] ?>;
window.BLINKHUB_IS_LOGGED_IN = true;
</script>
<script>

const upiOption = document.getElementById("upi-option");
const upiField = document.getElementById("upi-field");

document.querySelectorAll('input[name="payment"]').forEach(radio => {

radio.addEventListener("change", function(){

if(this.value === "upi"){
upiField.style.display = "block";
}
else{
upiField.style.display = "none";
}

});

});

</script>
<script>
window.BLINKHUB_USER_ID = <?= $user ? $user['id'] : 'null' ?>;
</script>
</body>
</html>