<?php
require_once __DIR__ . '/../../src/auth_admin.php';
require_once __DIR__ . '/../../config/db.php';

$order_id = intval($_POST['order_id']);
$status = $_POST['status'];

/* If delivered → payment becomes paid */
if ($status == "Delivered") {
    $payment_status = "Paid";
} else {
    $payment_status = "Pending";
}

$stmt = $mysqli->prepare("UPDATE orders SET order_status=?, payment_status=? WHERE id=?");
$stmt->bind_param("ssi", $status, $payment_status, $order_id);
$stmt->execute();

header("Location: order-view.php?id=".$order_id);
exit;