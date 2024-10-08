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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process deletion
    $stmt = $pdo->prepare("DELETE FROM Books WHERE BookID = ?");
    if ($stmt->execute([$book_id])) {
        header("Location: admin_books.php");
        exit();
    } else {
        $error = "Error deleting book";
    }
} else {
    // Fetch book details for confirmation
    $stmt = $pdo->prepare("SELECT Title FROM Books WHERE BookID = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        header("Location: admin_books.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Book - Admin Panel</title>
</head>
<body>
    <h1>Delete Book</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    <p>Are you sure you want to delete the book "<?= htmlspecialchars($book['Title']) ?>"?</p>
    <form method="post">
        <input type="submit" value="Yes, Delete Book">
    </form>
    <a href="admin_books.php">No, Go Back</a>
</body>
</html>