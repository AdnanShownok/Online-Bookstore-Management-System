<?php
session_start();
require_once 'db_connect.php';

// Initialize variables

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate total items in cart
$cart_count = array_sum($_SESSION['cart']);
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$books_per_page = 10;

// Prepare the base query
$query = "SELECT Books.*, Authors.AuthorName, Categories.CategoryName 
          FROM Books 
          JOIN Authors ON Books.AuthorID = Authors.AuthorID
          JOIN Categories ON Books.CategoryID = Categories.CategoryID";

// Add search functionality
if (!empty($search)) {
    $query .= " WHERE Books.Title LIKE :search 
                OR Authors.AuthorName LIKE :search 
                OR Categories.CategoryName LIKE :search";
}

// Count total books for pagination
$count_query = $pdo->prepare(str_replace('Books.*, Authors.AuthorName, Categories.CategoryName', 'COUNT(*) as total', $query));
if (!empty($search)) {
    $count_query->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$count_query->execute();
$total_books = $count_query->fetch(PDO::FETCH_ASSOC)['total'];

// Calculate pagination
$total_pages = ceil($total_books / $books_per_page);
$offset = ($page - 1) * $books_per_page;

// Fetch books
$query .= " LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $books_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books - Online Bookstore</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .search-form {
            margin-bottom: 30px;
            text-align: center;
        }
        .search-form input[type="text"] {
            width: 60%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-form input[type="submit"] {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .search-form input[type="submit"]:hover {
            background-color: #2980b9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons a, .action-buttons input[type="submit"] {
            padding: 5px 10px;
            text-decoration: none;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .action-buttons a {
            background-color: #2ecc71;
        }
        .action-buttons input[type="submit"] {
            background-color: #e74c3c;
        }
        .action-buttons a:hover {
            background-color: #27ae60;
        }
        .action-buttons input[type="submit"]:hover {
            background-color: #c0392b;
        }
        .pagination {
            margin-top: 30px;
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            color: #3498db;
            border: 1px solid #3498db;
            margin: 0 4px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        .pagination a:hover, .pagination a.active {
            background-color: #3498db;
            color: white;
        }
        .checkout-button {
            display: block;
            width: 200px;
            margin: 30px auto;
            padding: 10px 20px;
            background-color: #2ecc71;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .checkout-button:hover {
            background-color: #27ae60;
        }

        .cart-summary {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            position: fixed;
            top: 20px;
            right: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .cart-summary a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">

    <?php
        if (isset($_GET['message'])) {
            echo '<div class="message">' . htmlspecialchars($_GET['message']) . '</div>';
        }
        ?>
      <div class="cart-summary">
      <a href="cart.php">
                Cart: <?= $cart_count ?> item<?= $cart_count != 1 ? 's' : '' ?>
            </a>
        </div>
        <h2>Books</h2>

        <div class="search-form">
            <form action="" method="get">
                <input type="text" name="search" placeholder="Search books..." value="<?= htmlspecialchars($search) ?>">
                <input type="submit" value="Search">
            </form>
        </div>
        <?php
if (isset($_GET['message'])) {
    echo '<div class="message">' . htmlspecialchars($_GET['message']) . '</div>';
}
?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['Title']) ?></td>
                        <td><?= htmlspecialchars($book['AuthorName']) ?></td>
                        <td><?= htmlspecialchars($book['CategoryName']) ?></td>
                        <td>$<?= number_format($book['Price'], 2) ?></td>
                        <td><?= $book['StockQuantity'] ?></td>
                        <td class="action-buttons">
                        <a href="book.php?id=<?= $book['BookID'] ?>">View</a>
    <?php if ($book['StockQuantity'] > 0): ?>
        <?php 
        $cart_quantity = isset($_SESSION['cart'][$book['BookID']]) ? $_SESSION['cart'][$book['BookID']] : 0;
        ?>
        <form action="add_to_cart.php" method="post" class="cart-form">
            <input type="hidden" name="book_id" value="<?= $book['BookID'] ?>">
            <button type="submit" name="action" value="remove" <?= $cart_quantity == 0 ? 'disabled' : '' ?>>-</button>
            <span class="quantity"><?= $cart_quantity ?></span>
            <button type="submit" name="action" value="add" <?= $cart_quantity >= $book['StockQuantity'] ? 'disabled' : '' ?>>+</button>
        </form>
    <?php else: ?>
        <span>Out of Stock</span>
    <?php endif; ?>
</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                   class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>

        <a href="checkout.php" class="checkout-button">Proceed to Checkout</a>
    </div>
</body>
</html>