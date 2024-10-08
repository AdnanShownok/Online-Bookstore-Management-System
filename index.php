<?php
session_start();
require_once 'db_connect.php';

// Fetch featured books
$stmt = $pdo->query("SELECT Books.*, Authors.AuthorName FROM Books 
                     JOIN Authors ON Books.AuthorID = Authors.AuthorID 
                     LIMIT 4");
$featuredBooks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bookstore</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-image: url('homepage.jpg'); 
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        header {
            background: rgba(51, 51, 51, 0.9);
            color: #fff;
            padding: 1rem 0;
        }
        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav ul {
            list-style: none;
            padding: 0;
            display: flex;
        }
        nav ul li {
            margin-left: 20px;
        }
        nav ul li a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s;
        }
        nav ul li a:hover {
            color: #ffd700;
        }
        main {
            padding: 2rem 0;
        }
        h1, h2 {
            color: white;
        }
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }
        .book-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s;
        }
        .book-card:hover {
            transform: translateY(-5px);
        }
        .book-card h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .book-card a {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 1rem;
            transition: background 0.3s;
        }
        .book-card a:hover {
            background: #2980b9;
        }
        footer {
            background: rgba(51, 51, 51, 0.9);
            color: #fff;
            text-align: center;
            padding: 1rem 0;
            position: relative;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Online Bookstore</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="books.php">Books</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                    <li><a href="cart.php">Cart</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <h2>Featured Books</h2>
            <div class="book-grid">
                <?php foreach ($featuredBooks as $book): ?>
                    <div class="book-card">
                        <h3><?= htmlspecialchars($book['Title']) ?></h3>
                        <p>By <?= htmlspecialchars($book['AuthorName']) ?></p>
                        <p>Price: $<?= number_format($book['Price'], 2) ?></p>
                        <a href="book.php?id=<?= $book['BookID'] ?>">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <footer>
        
        <p>&copy; 2024 Online Bookstore. All rights reserved.</p>
    </footer>
</body>
</html>