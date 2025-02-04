<?php
require 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Define upload directory
$uploadDir = 'uploads/';

// Ensure the upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $image = $_FILES['image'];

    // Basic validation
    if (empty($name) || empty($price)) {
        $error = "Item name and price are required.";
    } elseif ($image['error'] != UPLOAD_ERR_OK) {
        $error = "Error uploading file. Please try again.";
    } else {
        // Validate image file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($image['type'], $allowedTypes)) {
            $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        } elseif ($image['size'] > $maxFileSize) {
            $error = "File size exceeds 2MB limit.";
        } else {
            // Generate unique file name
            $fileName = uniqid() . '-' . basename($image['name']);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($image['tmp_name'], $filePath)) {
                // Insert item into database
                $stmt = $conn->prepare("INSERT INTO items (name, price, image, user_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $price, $filePath, $_SESSION['user_id']]);
                $success = "Item added successfully!";
            } else {
                $error = "Failed to save the uploaded file.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Item</title>
</head>
<body>
    <h1>Add Item</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <form action="" method="post" enctype="multipart/form-data">
        <label>Item Name:</label>
        <input type="text" name="name" required><br>
        <label>Price:</label>
        <input type="number" step="0.01" name="price" required><br>
        <label>Image:</label>
        <input type="file" name="image" required><br>
        <button type="submit">Add Item</button>
    </form>
</body>
</html>
