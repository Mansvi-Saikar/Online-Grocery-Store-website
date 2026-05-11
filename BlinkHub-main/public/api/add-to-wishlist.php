<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user'])) {
    echo 'login';
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$product_id = (int)($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    exit;
}

/* 1️⃣ Check if product already in wishlist */
$check = $mysqli->prepare(
    "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?"
);
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$check->store_result();

/* 2️⃣ If exists → REMOVE */
if ($check->num_rows > 0) {
    $check->close();

    $del = $mysqli->prepare(
        "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?"
    );
    $del->bind_param("ii", $user_id, $product_id);
    $del->execute();
    $del->close();

    echo "removed";
    exit;
}

/* 3️⃣ Else → ADD */
$check->close();

$add = $mysqli->prepare(
    "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)"
);
$add->bind_param("ii", $user_id, $product_id);
$add->execute();
$add->close();

echo "added";





