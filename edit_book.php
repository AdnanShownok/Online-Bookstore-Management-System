<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$book_id) {
    header("Location: admin_books.php");
    exit();
}

// Fetch book details
$stmt = $pdo->prepare("SELECT * FROM Books WHERE BookID = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header("Location: admin_books.php");
    exit();
}

// Fetch authors and categories for dropdown menus
$authors = $pdo->query("SELECT * FROM Authors")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM Categories")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process form submission
    $title = $_POST['title'];
    $author_id = $_POST['author_id'];
    $category_id = $_POST['category_id'];
    $isbn = $_POST['isbn'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $stmt = $pdo->prepare("UPDATE Books SET Title = ?, AuthorID = ?, CategoryID = ?, ISBN = ?, Price = ?, StockQuantity = ? WHERE BookID = ?");
    if ($stmt->execute([$title, $author_id, $category_id, $isbn, $price, $stock, $book_id])) {
        header("Location: admin_books.php");
        exit();
    } else {
        $error = "Error updating book";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - Admin Panel</title>
</head>
<body>
    <h1>Edit Book</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    <form method="post">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($book['Title']) ?>" required><br>

        <label for="author_id">Author:</label>
        <select id="author_id" name="author_id" required>
            <?php foreach ($authors as $author): ?>
                <option value="<?= $author['AuthorID'] ?>" <?= $author['AuthorID'] == $book['AuthorID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($author['AuthorName']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="category_id">Category:</label>
        <select id="category_id" name="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['CategoryID'] ?>" <?= $category['CategoryID'] == $book['CategoryID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['CategoryName']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="isbn">ISBN:</label>
        <input type="text" id="isbn" name="isbn" value="<?= htmlspecialchars($book['ISBN']) ?>" required><br>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" value="<?= $book['Price'] ?>" required><br>

        <label for="stock">Stock Quantity:</label>
        <input type="number" id="stock" name="stock" value="<?= $book['StockQuantity'] ?>" required><br>

        <input type="submit" value="Update Book">
    </form>
    <a href="admin_books.php">Back to Book List</a>
</body>
</html>