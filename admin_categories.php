<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Handle form submission for adding a new category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $new_category = trim($_POST['new_category']);
    if (!empty($new_category)) {
        $stmt = $pdo->prepare("INSERT INTO Categories (CategoryName) VALUES (?)");
        if ($stmt->execute([$new_category])) {
            $success_message = "Category added successfully.";
        } else {
            $error_message = "Error adding category.";
        }
    }
}

// Fetch all categories
$categories = $pdo->query("SELECT * FROM Categories ORDER BY CategoryName")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Panel</title>
    <style>
        body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    position: relative;
}

.background-blur {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('admin_images/admin_categories.jpg') no-repeat center center fixed;
    background-size: cover;
    filter: blur(6px); /* Adjust the blur value */
    z-index: -1; /* Ensure the background stays behind the content */
}

.container {
    max-width: 900px;
    margin: 50px auto;
    background-color: #1a2639; /* White background with slight transparency */
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1; /* Ensure the content stays above the background */
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
            border: 1px solid #dee2e6;
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

        .table-container {
            margin-top: 20px;
        }

        .btn-danger {
            background-color: #dc3545;
            padding: 8px 12px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
<div class="background-blur"></div>
    <div class="container">
        <h1>Manage Categories</h1>

        <!-- Display success or error message -->
        <?php if (isset($success_message)): ?>
            <div class="message success"><?= $success_message ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="message error"><?= $error_message ?></div>
        <?php endif; ?>

        <!-- Form to add a new category -->
        <form method="post" action="admin_categories.php">
            <div class="form-group">
                <label for="new_category">Add New Category</label>
                <input type="text" id="new_category" name="new_category" placeholder="Enter category name" required>
            </div>
            <button type="submit" name="add_category">Add Category</button>
        </form>

        <!-- Display categories in a table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= htmlspecialchars($category['CategoryID']) ?></td>
                            <td><?= htmlspecialchars($category['CategoryName']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p>
    <a href="admin.php" style="background-color: #007bff; color: white;  padding: 10px 20px; text-decoration: none; border-radius: 5px;">Back to Admin page</a>
</p>
</body>
</html>
