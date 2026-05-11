<?php
session_start();
require_once __DIR__ . '/../config/db.php';

/* Redirect if not logged in */
if (!isset($_SESSION['user'])) {
    header("Location: login.php?redirect=wishlist.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = (int)$user['id'];


/* Fetch wishlist items (NO quantity) */
$stmt = $mysqli->prepare("
    SELECT 
    p.id,
    p.name,
    p.price,
    p.mrp,
    p.image_url,
    p.eta_minutes,
    c.name AS category_name
FROM wishlist w
JOIN products p ON w.product_id = p.id
LEFT JOIN categories c ON p.category_id = c.id
WHERE w.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$wishlistItems = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BlinkHub – Wishlist</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="wishlist-page">

<div class="page">

<!-- NAVBAR -->
<header class="navbar">
    <div class="nav-left">
        <a href="index.php" class="logo" aria-label="BlinkHub home">
            <span class="logo-dark">Blink</span><span class="logo-yellow">Hub</span>
        </a>
    </div>
            <div class="nav-center">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input 
                        type="text"
                        id="search-input"
                        placeholder="Search for chips, milk, Coke, bread..."
                        autocomplete="off"
                    />
                </div>
            </div>
            <div class="nav-right">
                <?php if ($user): ?>
                    <span class="nav-user">Hi, <?= htmlspecialchars($user['name'] ?? $user['email']) ?></span>
                    <a href="logout.php" class="nav-btn ghost">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-btn ghost">👤 Login</a>
                <?php endif; ?>
                <a href="cart.php" class="nav-btn cart-btn">
                🛒
                <span class="cart-label">Cart</span>
                
            </a>
            </div>
        </header>

<!-- CONTENT -->
<main class="content cart-content wishlist-page">
    <div class="section-header">
        <h2>Your wishlist</h2>
        <span class="section-subtitle">Saved items you love ❤️</span>
    </div>

    <?php if (empty($wishlistItems)): ?>
        <div class="cart-empty">
            <p class="cart-empty-title">Your wishlist is empty.</p>
            <p class="cart-empty-note">Tap the ❤️ icon on products to save them here.</p>
            <a href="index.php" class="cta-btn cart-empty-cta">Browse products</a>
        </div>
    <?php else: ?>

    <div class="product-grid">
        <?php foreach ($wishlistItems as $p): ?>
            <?php
                        $imgUrl = trim($p['image_url'] ?? '');
                        if ($imgUrl !== '') {
                        // remove leading slash if present
                        $imgUrl = ltrim($imgUrl, '/');

                        // always build absolute path from project root
                        $imgUrl = '/BlinkHub-main/public/' . $imgUrl;
                        }
                    ?>

            <article class="product-card">

                <!-- IMAGE -->
                <div class="product-thumb">
                    <?php if ($imgUrl !== ''): ?>
                        <img src="<?= htmlspecialchars($imgUrl) ?>"
                             alt="<?= htmlspecialchars($p['name']) ?>"
                             class="product-img"
                             loading="lazy">
                    <?php else: ?>
                        <div class="product-placeholder">
                            <span><?= htmlspecialchars($p['category_name']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- BODY -->
                <div class="product-body">
                    <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>

                    <p class="product-meta">
                        ETA: <strong><?= (int)$p['eta_minutes'] ?> mins</strong>
                    </p>

                    <!-- PRICE + HEART -->
                    <div class="product-price-row">
                        <span class="price">₹<?= number_format($p['price']) ?></span>
                        <span class="mrp">₹<?= number_format($p['mrp']) ?></span>

                        <button
                            class="wishlist-btn active"
                            data-product-id="<?= (int)$p['id'] ?>"
                            title="Remove from wishlist"
                        >♥</button>
                    </div>

                    <!-- ADD TO CART -->
                    <div class="qty-controls"
                         data-id="<?= (int)$p['id'] ?>"
                         data-name="<?= htmlspecialchars($p['name']) ?>"
                         data-price="<?= (int)$p['price'] ?>">

                        <button
                            type="button"
                            class="wishlist-add-btn"
                            data-id="<?= (int)$p['id'] ?>"
                            data-name="<?= htmlspecialchars($p['name']) ?>"
                            data-price="<?= (float)$p['price'] ?>"
                            >
                                + Add
                        </button>

                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</main>
</div>

<script>
    window.BLINKHUB_IS_LOGGED_IN = <?= $user ? 'true' : 'false' ?>;
</script>

<script src="js/app.js"></script>
<script src="js/app.js"></script>
</body>
</html>


