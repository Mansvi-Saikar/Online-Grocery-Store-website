<?php
require_once __DIR__ . '/../../src/auth_admin.php';
require_once __DIR__ . '/../../config/db.php';

$publicRoot   = dirname(__DIR__);          // /public
$uploadDirFs  = $publicRoot . '/images';   // filesystem
$uploadDirUrl = '/images';                 // browser URL
$imageUrl    = null;


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name        = trim($_POST['name'] ?? '');
$category_id = (int)($_POST['category_id'] ?? 0);
$price       = (int)($_POST['price'] ?? 0);
$mrp         = (int)($_POST['mrp'] ?? 0);
$tag         = trim($_POST['tag'] ?? '');
$eta         = (int)($_POST['eta_minutes'] ?? 10);
$tags        = trim($_POST['tags'] ?? '');
$is_active   = isset($_POST['is_active']) ? 1 : 0;


if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {

    if (!is_dir($uploadDirFs)) {
        mkdir($uploadDirFs, 0775, true);
    }

    $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];

    if (in_array($ext, $allowed, true)) {
        $filename = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetFs = $uploadDirFs . '/' . $filename;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFs)) {
            $imageUrl = $uploadDirUrl . '/' . $filename;
        }
    }
}



if ($name === '' || $category_id <= 0 || $price <= 0 || $mrp <= 0) {
    // keep it simple: just go back to products list
    header('Location: products.php');
    exit;
}

if ($id > 0) {

    // UPDATE PRODUCT
    if ($imageUrl !== null) {

        // update WITH image
        $stmt = $mysqli->prepare("
            UPDATE products 
            SET 
                name = ?, 
                category_id = ?, 
                price = ?, 
                mrp = ?, 
                tag = ?, 
                eta_minutes = ?, 
                tags = ?, 
                is_active = ?, 
                image_url = ?
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            'siiisissii',
            $name,
            $category_id,
            $price,
            $mrp,
            $tag,
            $eta,
            $tags,
            $is_active,
            $imageUrl,
            $id
        );

        $stmt->execute();
        $stmt->close();

    } else {

        // update WITHOUT image (keep old image)
        $stmt = $mysqli->prepare("
            UPDATE products 
            SET 
                name = ?, 
                category_id = ?, 
                price = ?, 
                mrp = ?, 
                tag = ?, 
                eta_minutes = ?, 
                tags = ?, 
                is_active = ?
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            'siiisissi',
            $name,
            $category_id,
            $price,
            $mrp,
            $tag,
            $eta,
            $tags,
            $is_active,
            $id
        );

        $stmt->execute();
        $stmt->close();
    }

} else {
    // insert
    $sql = "INSERT INTO products 
    (name, category_id, price, mrp, tag, eta_minutes, tags, is_active, image_url)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt->bind_param(
        'siiisisi',
        $name,
        $category_id,
        $price,
        $mrp,
        $tag,
        $eta,
        $tags,
        $is_active,
        $imageUrl
    );
    $stmt->execute();
    $stmt->close();
}

header('Location: products.php');
exit;
