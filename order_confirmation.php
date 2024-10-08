<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = (int)$_GET['order_id'];

// Fetch order details
$stmt = $pdo->prepare("
    SELECT Orders.*, Users.FullName, Users.Email, Users.Address
    FROM Orders
    JOIN Users ON Orders.UserID = Users.UserID
    WHERE Orders.OrderID = ? AND Orders.UserID = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: index.php");
    exit();
}

// Fetch order items
$stmt = $pdo->prepare("
    SELECT OrderItems.*, Books.Title
    FROM OrderItems
    JOIN Books ON OrderItems.BookID = Books.BookID
    WHERE OrderItems.OrderID = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Online Bookstore</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        h1, h2 {
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
            background-color: #f4f4f4;
        }
        .order-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .thank-you {
            font-size: 1.2em;
            color: #4CAF50;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Order Confirmation</h1>
    
    <div class="thank-you">
        Thank you for your order! Your order has been successfully placed.
    </div>

    <div class="order-info">
        <h2>Order Details</h2>
        <p><strong>Order ID:</strong> <?= $order['OrderID'] ?></p>
        <p><strong>Order Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['OrderDate'])) ?></p>
        <p><strong>Total Amount:</strong> $<?= number_format($order['TotalAmount'], 2) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($order['Status']) ?></p>
    </div>

    <div class="order-info">
        <h2>Shipping Information</h2>
        <p><strong>Name:</strong> <?= htmlspecialchars($order['FullName']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['Email']) ?></p>
        <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($order['Address'])) ?></p>
    </div>

    <h2>Order Items</h2>
    <table>
        <thead>
            <tr>
                <th>Book Title</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['Title']) ?></td>
                    <td><?= $item['Quantity'] ?></td>
                    <td>$<?= number_format($item['Price'], 2) ?></td>
                    <td>$<?= number_format($item['Quantity'] * $item['Price'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="index.php">Return to Homepage</a></p>
</body>
</html>