<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$user = $_SESSION['user'] ?? null;

// Load categories
$categories = [];
$catResult = $mysqli->query("SELECT name FROM categories ORDER BY sort_order, name");
if ($catResult) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row['name'];
    }
    $catResult->free();
}

// Load products (NOW INCLUDING image_url)
$products = [];
$sql = "
    SELECT 
        p.id,
        p.name,
        p.price,
        p.mrp,
        p.eta_minutes,
        p.tags,
        p.image_url,
        p.stock,
        c.name AS category_name
    FROM products p
    JOIN categories c ON c.id = p.category_id
    WHERE p.is_active = 1
    ORDER BY p.id
";
$prodResult = $mysqli->query($sql);
if ($prodResult) {
    while ($row = $prodResult->fetch_assoc()) {
        $products[] = $row;
    }
    $prodResult->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BlinkHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="page">
    <!-- Top navbar -->
    <header class="navbar">
        <div class="nav-left">
            <a href="index.php" class="logo" aria-label="BlinkHub home">
                <span class="logo-dark">Blink</span><span class="logo-yellow">Hub</span>
            </a>
            <a href="my-orders.php" style="text-decoration:none; color:inherit;">
            <div class="location-pill">
                <span class="loc-main">📦My</span>
                <span class="loc-main">Orders</span>
                
            </div>
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

            <!-- NEW: Wishlist button -->
            <a href="wishlist.php" class="nav-btn ghost wishlist-btn">
                🤍 Wishlist
            </a>

            <a href="cart.php" class="nav-btn cart-btn">
                🛒
                <span class="cart-label">Cart</span>
                <span class="cart-count-badge" id="cart-count">0</span>
            </a>

            <!-- Address modal -->
            
        </div>
    </header>

    <!-- Category strip -->
    <section class="category-strip">
        <?php if ($categories): ?>
            <!-- Default "All" filter -->
            <button class="category-pill active" data-category="">All</button>

            <?php foreach ($categories as $cat): ?>
                <button 
                    class="category-pill" 
                    data-category="<?= htmlspecialchars($cat) ?>">
                    <?= htmlspecialchars($cat) ?>
                </button>
            <?php endforeach; ?>
        <?php else: ?>
            <span class="section-subtitle">No categories found in DB.</span>
        <?php endif; ?>
    </section>

    <!-- Banner -->

    <div class="banner" id="banner">
        <img src="images/banner.jpeg" alt="Fresh Grocery Banner">
        <a href="#snacks" class="shop-now" arial-label="Shop now">Shop now</a>
    </div>

    <!-- Product grid -->

    <main class="content">
        <div class="section-header" id="snacks">
            <h2>Snacks & essentials near you</h2>
        </div>

        <?php if (!$products): ?>
            <p class="section-subtitle">No products found. Check your DB seed.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $index => $p): ?>
                    <?php
                        $imgUrl = trim($p['image_url'] ?? '');
                        if ($imgUrl !== '') {
                        // remove leading slash if present
                        $imgUrl = ltrim($imgUrl, '/');

                        // always build absolute path from project root
                        $imgUrl = '/BlinkHub-main/public/' . $imgUrl;
                        }
                    ?>
                    <article class="product-card"
                        data-index="<?= (int)$index ?>"
                        data-name="<?= htmlspecialchars($p['name']) ?>"
                        data-category="<?= htmlspecialchars($p['category_name']) ?>"
                        data-tags="<?= htmlspecialchars($p['tags']) ?>">

                        <div class="product-thumb">
                            
                            <?php if (!empty($p['tag'])): ?>
                                <div class="product-chip"><?= htmlspecialchars($p['tag']) ?></div>
                            <?php endif; ?>

                            <?php if ($imgUrl !== ''): ?>
                                <img
                                    src="<?= htmlspecialchars($imgUrl) ?>"
                                    alt="<?= htmlspecialchars($p['name']) ?>"
                                    class="product-img"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <div class="product-placeholder">
                                    <span><?= htmlspecialchars($p['category_name']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="product-body">
                            <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                            <p class="product-meta">
                                ETA: <strong><?= (int)$p['eta_minutes'] ?> mins</strong>
                            </p>
                            <div class="product-price-row">
                                <span class="price">₹<?= number_format((int)$p['price']) ?></span>
                                <span class="mrp">₹<?= number_format((int)$p['mrp']) ?></span>

                                <button 
                                    class="wishlist-btn"
                                    data-product-id="<?= (int)$p['id'] ?>"
                                    title="Add to wishlist"
                                >♡</button>
                            </div>

                            <div class="qty-controls" 
                                data-id="<?= (int)$p['id'] ?>" 
                                data-name="<?= htmlspecialchars($p['name']) ?>" 
                                data-price="<?= (int)$p['price'] ?>"
                                data-stock="<?= (int)$p['stock'] ?>">

                                <button class="add-btn">+ Add</button>

                                <div class="stepper hidden">
                                    <button class="stepper-minus">-</button>
                                    <span class="stepper-qty">1</span>
                                    <button class="stepper-plus">+</button>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
    </footer>
    <div class="page">

    


</div>

<script>

window.BLINKHUB_IS_LOGGED_IN = <?= $user ? 'true' : 'false' ?>;
window.BLINKHUB_USER_ID = <?= $user ? (int)$user['id'] : 0 ?>;

</script>
<script>
window.BLINKHUB_USER_ID = <?= $user ? $user['id'] : 'null' ?>;
</script>
<script src="js/app.js"></script>

</body>
</html>
