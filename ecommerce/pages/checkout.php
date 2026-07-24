<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$order_completed = false;

if (isset($_POST['place_order'])) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $order_completed = true;
}

$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_cost = 0;
$products = [];

if (!empty($cart_items)) {
    $product_ids = array_column($cart_items, 'product_id');
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        foreach ($cart_items as $cart_item) {
            if ($cart_item['product_id'] == $product['id']) {
                $total_cost += $product['price'] * $cart_item['quantity'];
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        h2 { margin-top: 0; }
        .item {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            display: inline-block;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 5px;
            background: #28a745;
            color: #fff;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .success {
            background: #e8f8ee;
            color: #1f7a3b;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Checkout</h2>

        <?php if ($order_completed) : ?>
            <div class="success">Thank you! Your order has been placed successfully.</div>
        <?php endif; ?>

        <?php if (empty($cart_items)) : ?>
            <p>Your cart is empty. Add products before checking out.</p>
            <div class="actions">
                <a href="../index.php" class="btn">Back to Shop</a>
            </div>
        <?php else : ?>
            <?php foreach ($products as $product) : ?>
                <?php
                    $quantity = 0;
                    foreach ($cart_items as $cart_item) {
                        if ($cart_item['product_id'] == $product['id']) {
                            $quantity = $cart_item['quantity'];
                            break;
                        }
                    }
                ?>
                <div class="item">
                    <span><?= htmlspecialchars($product['name']); ?> × <?= $quantity; ?></span>
                    <strong>$<?= number_format($product['price'] * $quantity, 2); ?></strong>
                </div>
            <?php endforeach; ?>

            <div class="item" style="font-size: 1.1em; margin-top: 10px;">
                <span>Total</span>
                <strong>$<?= number_format($total_cost, 2); ?></strong>
            </div>

            <form method="POST">
                <div class="actions">
                    <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                    <button type="submit" name="place_order" class="btn">Place Order</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
