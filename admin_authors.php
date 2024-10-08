<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Handle form submission for adding a new author
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_author'])) {
    $new_author = trim($_POST['new_author']);
    if (!empty($new_author)) {
        $stmt = $pdo->prepare("INSERT INTO Authors (AuthorName) VALUES (?)");
        if ($stmt->execute([$new_author])) {
            $success_message = "Author added successfully.";
        } else {
            $error_message = "Error adding author.";
        }
    }
}

// Fetch all authors
$authors = $pdo->query("SELECT * FROM Authors ORDER BY AuthorName")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Authors - Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #007bff;
        }

        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        td {
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Authors</h1>

        <!-- Display success or error message -->
        <?php if (isset($success_message)): ?>
            <div class="message success"><?= $success_message ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="message error"><?= $error_message ?></div>
        <?php endif; ?>

        <!-- Form to add a new author -->
        <form method="post" action="admin_authors.php">
            <div class="form-group">
                <label for="new_author">Add New Author</label>
                <input type="text" id="new_author" name="new_author" placeholder="Enter author name" required>
            </div>
            <button type="submit" name="add_author">Add Author</button>
        </form>

        <!-- Display authors in a table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Author Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authors as $author): ?>
                    <tr>
                        <td><?= htmlspecialchars($author['AuthorID']) ?></td>
                        <td><?= htmlspecialchars($author['AuthorName']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
