<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id']) && isset($_POST['action'])) {
    $book_id = (int)$_POST['book_id'];
    $action = $_POST['action'];

    // Fetch current stock
    $stmt = $pdo->prepare("SELECT StockQuantity FROM Books WHERE BookID = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($book) {
        $current_stock = $book['StockQuantity'];

        // Check if the cart session exists, if not create it
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $cart_quantity = isset($_SESSION['cart'][$book_id]) ? $_SESSION['cart'][$book_id] : 0;

        // Handle adding to cart
        if ($action === 'add') {
            if ($cart_quantity < $current_stock) {
                $_SESSION['cart'][$book_id] = $cart_quantity + 1;
                $message = "Book quantity increased.";
            } else {
                $message = "Insufficient stock for this book.";
            }

        // Handle removing from cart
        } elseif ($action === 'remove') {
            if ($cart_quantity > 0) {
                $_SESSION['cart'][$book_id] = $cart_quantity - 1;
                $message = "Book quantity decreased.";
                if ($_SESSION['cart'][$book_id] == 0) {
                    unset($_SESSION['cart'][$book_id]);  // Remove from cart if quantity is 0
                }
            } else {
                $message = "This book is not in your cart.";
            }
        }

        // Redirect or display message
        header("Location: cart.php?message=" . urlencode($message));
        exit();
    } else {
        echo "<p>Book not found.</p>";
    }
} else {
    echo "<p>Invalid request. Please try again.</p>";
}
?>
