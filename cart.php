<?php
session_start();
require_once 'db_connect.php';

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding items to cart (this would typically be done on a separate page)
if (isset($_GET['add_to_cart']) && isset($_GET['book_id'])) {
    $book_id = (int)$_GET['book_id'];
    if (!isset($_SESSION['cart'][$book_id])) {
        $_SESSION['cart'][$book_id] = 1;
    } else {
        $_SESSION['cart'][$book_id]++;
    }
    header('Location: cart.php');
    exit();
}

// Handle updating cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $book_id => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity > 0) {
            $_SESSION['cart'][$book_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$book_id]);
        }
    }
    header('Location: cart.php');
    exit();
}

// Fetch cart items from database
$cart_items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $book_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($book_ids), '?'));
    
    $stmt = $pdo->prepare("SELECT * FROM Books WHERE BookID IN ($placeholders)");
    $stmt->execute($book_ids);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($books as $book) {
        $book_id = $book['BookID'];
        $quantity = $_SESSION['cart'][$book_id];
        $subtotal = $book['Price'] * $quantity;
        $cart_items[] = [
            'book' => $book,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
        $total += $subtotal;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Online Bookstore</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .total {
            font-weight: bold;
            text-align: right;
        }
        .actions {
            margin-top: 20px;
        }
        .actions a, .actions input[type="submit"] {
            display: inline-block;
            padding: 10px 15px;
            background-color: #333;
            color: #fff;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h2>Your Shopping Cart</h2>

    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <form method="post" action="">
            <table>
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['book']['Title']) ?></td>
                            <td>$<?= number_format($item['book']['Price'], 2) ?></td>
                            <td>
                                <input type="number" name="quantity[<?= $item['book']['BookID'] ?>]" 
                                       value="<?= $item['quantity'] ?>" min="0" style="width: 50px;">
                            </td>
                            <td>$<?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="total">Total:</td>
                        <td>$<?= number_format($total, 2) ?></td>
                    </tr>
                </tfoot>
            </table>

            <div class="actions">
                <input type="submit" name="update_cart" value="Update Cart">
                <?php if (!empty($cart_items)): ?>
        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
    <?php endif; ?>
               
            </div>
        </form>
    <?php endif; ?>

    <p>
    <a href="books.php" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Continue Shopping</a>
</p>
<p>
    <a href="index.php" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Back to Homepage</a>
</p>

</body>
</html>