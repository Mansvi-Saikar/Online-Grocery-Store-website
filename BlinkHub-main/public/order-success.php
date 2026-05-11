<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Placed - BlinkHub</title>

<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
    background: radial-gradient(circle at center, #1c1f26, #0f1115);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Card */
.success-card {
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 50px 40px;
    width: 420px;
    text-align: center;
    box-shadow: 0 0 40px rgba(0,0,0,0.6);
}



/* Tick Circle */
.check-circle {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: #a3e635;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 auto 25px;
    box-shadow: 0 0 25px #a3e635;
}

.check-circle i {
    font-size: 50px;
    color: black;
}

/* Text */
h1 {
    margin: 10px 0;
    font-size: 28px;
}

p {
    color: #ccc;
    margin-bottom: 35px;
}

/* Button */
.btn {
    display: inline-block;
    background: #a3e635;
    color: black;
    padding: 14px 40px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s ease;
}

.btn:hover {
    background: #84cc16;
    transform: scale(1.05);
}

/* Thank You */
.thank-you {
    margin-top: 20px;
    font-size: 14px;
    color: #aaa;
}
</style>

<!-- Font Awesome for tick icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</head>
<body>

<div class="success-card">

    

    <div class="check-circle">
        <i class="fa-solid fa-check"></i>
    </div>

    <h1>Order Placed!</h1>
    <p>Your order has been placed successfully.</p>

    <a href="index.php" class="btn">Continue Shopping</a>

    <div class="thank-you">
        Thank you for shopping with us!
    </div>

</div>

</body>
</html>