<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) {
    echo 'login';
    exit;
}

$user_id = $_SESSION['user']['id'];
$product_id = (int)($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    exit;
}

// check if already in wishlist
$check = $mysqli->prepare(
    "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?"
);
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // remove
    $del = $mysqli->prepare(
        "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?"
    );
    $del->bind_param("ii", $user_id, $product_id);
    $del->execute();
    echo 'removed';
} else {
    // add
    $add = $mysqli->prepare(
        "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)"
    );
    $add->bind_param("ii", $user_id, $product_id);
    $add->execute();
    echo 'added';
}
