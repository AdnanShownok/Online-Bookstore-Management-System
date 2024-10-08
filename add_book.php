<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Fetch authors and categories for dropdown menus
$authors = $pdo->query("SELECT * FROM Authors")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM Categories")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process form submission
    $title = $_POST['title'];
    $author_id = $_POST['author_id'];
    $new_author = $_POST['new_author'];
    $category_id = $_POST['category_id'];
    $new_category = $_POST['new_category'];
    $isbn = $_POST['isbn'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $pdo->beginTransaction();

    try {
        // Handle new author
        if ($author_id == 'new' && !empty($new_author)) {
            $stmt = $pdo->prepare("INSERT INTO Authors (AuthorName) VALUES (?)");
            $stmt->execute([$new_author]);
            $author_id = $pdo->lastInsertId();
        }

        // Handle new category
        if ($category_id == 'new' && !empty($new_category)) {
            $stmt = $pdo->prepare("INSERT INTO Categories (CategoryName) VALUES (?)");
            $stmt->execute([$new_category]);
            $category_id = $pdo->lastInsertId();
        }

        // Insert new book
        $stmt = $pdo->prepare("INSERT INTO Books (Title, AuthorID, CategoryID, ISBN, Price, StockQuantity) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $author_id, $category_id, $isbn, $price, $stock]);

        $pdo->commit();
        header("Location: admin_books.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error adding book: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book - Admin Panel</title>
    <script>
        function toggleNewInput(selectId, inputId) {
            var select = document.getElementById(selectId);
            var input = document.getElementById(inputId);
            input.style.display = (select.value === 'new') ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <h1>Add New Book</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    <form method="post">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br>

        <label for="author_id">Author:</label>
        <select id="author_id" name="author_id" required onchange="toggleNewInput('author_id', 'new_author_input')">
            <?php foreach ($authors as $author): ?>
                <option value="<?= $author['AuthorID'] ?>"><?= htmlspecialchars($author['AuthorName']) ?></option>
            <?php endforeach; ?>
            <option value="new">Add New Author</option>
        </select><br>
        <input type="text" id="new_author_input" name="new_author" style="display: none;" placeholder="Enter new author name">

        <label for="category_id">Category:</label>
        <select id="category_id" name="category_id" required onchange="toggleNewInput('category_id', 'new_category_input')">
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['CategoryID'] ?>"><?= htmlspecialchars($category['CategoryName']) ?></option>
            <?php endforeach; ?>
            <option value="new">Add New Category</option>
        </select><br>
        <input type="text" id="new_category_input" name="new_category" style="display: none;" placeholder="Enter new category name">

        <label for="isbn">ISBN:</label>
        <input type="text" id="isbn" name="isbn" required><br>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" required><br>

        <label for="stock">Stock Quantity:</label>
        <input type="number" id="stock" name="stock" required><br>

        <input type="submit" value="Add Book">
    </form>
</body>
</html>