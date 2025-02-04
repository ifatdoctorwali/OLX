<?php
require 'db.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Image upload handling function
function uploadImage($file) {
    $target_dir = "uploads/";
    // Create uploads directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $filename = uniqid() . "_" . basename($file["name"]);
    $target_file = $target_dir . $filename;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return null;
    }

    // Check file size
    if ($file["size"] > 5000000) { // 5MB limit
        return null;
    }

    // Allow certain file formats
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    if(!in_array($imageFileType, $allowed_types)) {
        return null;
    }

    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    }
    return null;
}

// Fetch items
$stmt = $conn->prepare("SELECT * FROM items WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLX Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #002f34;
            --secondary-color: #00a49f;
            --background-gray: #f1f4f5;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--background-gray);
            line-height: 1.6;
            color: #002f34;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 20px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .item-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .item-card:hover {
            transform: scale(1.05);
        }
        .item-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .item-details {
            padding: 15px;
        }
        .item-actions {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .btn {
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
        }
        .edit-btn {
            background-color: #4CAF50;
            color: white;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="items-grid">
            <?php foreach ($items as $item): ?>
                <div class="item-card">
                    <?php 
                    // Determine image path
                    $image_path = !empty($item['image_path']) 
                        ? htmlspecialchars($item['image_path']) 
                        : 'default-image.jpg'; 
                    ?>
                    <img src="<?php echo $image_path; ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                         class="item-image" 
                         onerror="this.src='default-image.jpg';">
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Price: $<?php echo htmlspecialchars($item['price']); ?></p>
                    </div>
                    <div class="item-actions">
                        <a href="edit-item.php?id=<?php echo $item['id']; ?>" class="btn edit-btn">Edit</a>
                        <a href="delete-item.php?id=<?php echo $item['id']; ?>" class="btn delete-btn">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
