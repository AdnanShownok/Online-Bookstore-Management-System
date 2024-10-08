<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("Location: login.php");
    exit();
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM Users WHERE UserID = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch cart items
$cart_items = [];
$total = 0;
$stmt = $pdo->prepare("SELECT * FROM Books WHERE BookID IN (" . implode(',', array_fill(0, count($_SESSION['cart']), '?')) . ")");
$stmt->execute(array_keys($_SESSION['cart']));
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($books as $book) {
    $quantity = $_SESSION['cart'][$book['BookID']];
    $subtotal = $book['Price'] * $quantity;
    $cart_items[] = [
        'book' => $book,
        'quantity' => $quantity,
        'subtotal' => $subtotal
    ];
    $total += $subtotal;
}

// Process the order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    // Start transaction
    $pdo->beginTransaction();

    try {
        // Validate stock quantity
        foreach ($cart_items as $item) {
            if ($item['quantity'] > $item['book']['StockQuantity']) {
                throw new Exception("Not enough stock for " . htmlspecialchars($item['book']['Title']));
            }
        }

        // Insert order into Orders table
        $stmt = $pdo->prepare("INSERT INTO Orders (UserID, OrderDate, TotalAmount) VALUES (?, NOW(), ?)");
        $stmt->execute([$_SESSION['user_id'], $total]);
        $order_id = $pdo->lastInsertId();

        // Insert order items
        $stmt = $pdo->prepare("INSERT INTO OrderItems (OrderID, BookID, Quantity, Price) VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $stmt->execute([$order_id, $item['book']['BookID'], $item['quantity'], $item['book']['Price']]);

            // Update book stock
            $update_stock = $pdo->prepare("UPDATE Books SET StockQuantity = StockQuantity - ? WHERE BookID = ?");
            $update_stock->execute([$item['quantity'], $item['book']['BookID']]);
        }

        // Commit transaction
        $pdo->commit();

        // Clear the cart
        $_SESSION['cart'] = [];

        // Redirect to order confirmation page
        header("Location: order_confirmation.php?order_id=" . $order_id);
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Online Bookstore</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h2, h3 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h2>Checkout</h2>

    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <h3>Order Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Book</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['book']['Title']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>$<?= number_format($item['book']['Price'], 2) ?></td>
                    <td>$<?= number_format($item['subtotal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>Total:</strong></td>
                <td><strong>$<?= number_format($total, 2) ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <h3>Shipping Information</h3>
    <form method="post" action="">
        <input type="hidden" name="total_amount" value="<?= $total ?>">
        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="full_name" value="<?= sanitize_input($user['FullName']) ?>" required>

        <label for="address">Street Address:</label>
        <textarea id="address" name="address" required><?= sanitize_input($user['Address']) ?></textarea>

        <label for="city">City:</label>
        <input type="text" id="city" name="city" required>

        <label for="state">State/Province:</label>
        <input type="text" id="state" name="state" required>

        <label for="zip">ZIP/Postal Code:</label>
        <input type="text" id="zip" name="zip" required>

        <label for="country">Country:</label>
        <input type="text" id="country" name="country" required>

        <!-- Add payment information fields here -->

        <input type="submit" name="place_order" value="Place Order">
    </form>
</body>
</html>