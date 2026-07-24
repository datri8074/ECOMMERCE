<?php
session_start();
include 'includes/db.php'; // Include the database connection

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: pages/login.php");
    exit();
}

$stmt = $conn->query("SELECT COUNT(*) AS total_products FROM products");
$totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

$seedProducts = [
    ['Product 1', 19.99, 'A premium product for everyday use.', 'product1.jpg'],
    ['Product 2', 24.50, 'A modern design with excellent quality.', 'product2.jpg'],
    ['Product 3', 34.75, 'Built for comfort and reliable performance.', 'product3.jpg'],
    ['Product 4', 49.99, 'A feature-rich option for daily needs.', 'product4.jpg'],
    ['Product 5', 59.99, 'Our top-rated choice with premium finish.', 'product5.png'],
];

if ((int)$totalProducts < count($seedProducts)) {
    $seedStmt = $conn->prepare("INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)");
    for ($i = (int)$totalProducts; $i < count($seedProducts); $i++) {
        $seedStmt->execute($seedProducts[$i]);
    }
}

$stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Store</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Welcome to Our Store</h1>
            <nav>
                <?php if (isset($_SESSION['user_id'])) : ?>
                    <a href="pages/cart.php" class="cart-link">
                        <img src="images/cart-icon.png" alt="Cart" class="cart-icon">
                        Cart
                    </a>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="logout" class="logout-button">Logout</button>
                    </form>
                <?php else : ?>
                    <a href="pages/login.php">Login</a>
                    <a href="pages/register.php">Register</a>
                    <a href="pages/cart.php" class="cart-link">
                        <img src="images/cart-icon.png" alt="Cart" class="cart-icon">
                        Cart
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <div class="main-container">
        <main>
            <h2>Products</h2>
            <div class="product-list">
                <?php if (empty($products)) : ?>
                    <p>No products available.</p>
                <?php else : ?>
                    <?php foreach ($products as $product) : ?>
                        <?php
                            $imageName = !empty($product['image']) ? $product['image'] : 'product1.jpg';
                            $imagePath = 'images/' . $imageName;
                            if (!file_exists($imagePath)) {
                                $imagePath = 'images/product1.jpg';
                            }
                        ?>
                        <div class="product">
                            <h3><?= htmlspecialchars($product['name']); ?></h3>
                            <p>Price: $<?= number_format($product['price'], 2); ?></p>
                            <p><?= htmlspecialchars($product['description']); ?></p>
                            <img src="<?= htmlspecialchars($imagePath); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="product-image">
                            <form method="POST" action="pages/cart.php">
                                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                                <button type="submit" name="add_to_cart" class="add-to-cart-button">Add to Cart</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <footer>
        <p>&copy; <?= date('Y'); ?> Online Store. All rights reserved.</p>
    </footer>
</body>
</html>