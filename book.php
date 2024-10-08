<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db_connect.php';

// Check if book ID is provided in the URL
if (isset($_GET['id'])) {
    $book_id = (int) $_GET['id'];

    // Fetch the book details from the database
    $stmt = $pdo->prepare("SELECT Books.*, Authors.AuthorName, Categories.CategoryName 
                           FROM Books 
                           JOIN Authors ON Books.AuthorID = Authors.AuthorID
                           JOIN Categories ON Books.CategoryID = Categories.CategoryID
                           WHERE Books.BookID = :id");
    $stmt->execute(['id' => $book_id]);
    $book = $stmt->fetch();

    // Check if the book was found
    if ($book) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($book['Title']) ?> - Book Details</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    width: 80%;
                    margin: 20px auto;
                    background-color: #fff;
                    padding: 20px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    border-radius: 8px;
                }
                h1 {
                    font-size: 2em;
                    color: #333;
                }
                p {
                    font-size: 1.1em;
                    line-height: 1.6;
                    color: #666;
                }
                .book-info {
                    display: flex;
                    gap: 20px;
                    align-items: center;
                }
                .book-cover {
                    max-width: 200px;
                    max-height: 300px;
                    object-fit: cover;
                }
                .book-details {
                    flex: 1;
                }
                .price {
                    font-size: 1.5em;
                    color: #28a745;
                    font-weight: bold;
                }
                .btn {
                    display: inline-block;
                    background-color: #28a745;
                    color: white;
                    padding: 10px 20px;
                    text-align: center;
                    border: none;
                    border-radius: 5px;
                    font-size: 1em;
                    cursor: pointer;
                    text-decoration: none;
                }
                .btn:hover {
                    background-color: #218838;
                }
                .back-link {
                    margin-top: 20px;
                    display: block;
                    color: #007bff;
                    text-decoration: none;
                }
                .back-link:hover {
                    text-decoration: underline;
                }
            </style>
            <link rel="stylesheet" href="bookstyle.css">
        </head>
        <body>
        <div class="container">
            <h1><?= htmlspecialchars($book['Title']) ?></h1>
            <p><strong>Author:</strong> <?= htmlspecialchars($book['AuthorName']) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($book['CategoryName']) ?></p>
            <p><strong>Price:</strong> $<?= number_format($book['Price'], 2) ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($book['Description'] ?? 'No description available') ?></p>


        <form action="add_to_cart.php" method="post">
    <input type="hidden" name="book_id" value="<?= $book['BookID'] ?>">
    <input type="hidden" name="action" value="add">
    <button type="submit" class="btn">Add to Cart</button>
</form>


            <a href="index.php">Back to home</a>
        </div>
        </body>
        </html>
        <?php
    } else {
        echo "<p>Book not found.</p>";
    }
} else {
    echo "<p>No book ID provided.</p>";
}
?>
