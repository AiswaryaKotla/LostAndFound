<?php
session_start();
include 'db_connect.php';

// Ensure the user is logged in and is a student
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['student', 'faculty'])) {
    header("Location: index.php");
    exit();
}


$user_id = $_SESSION['user_id'];

$type = $_POST['type'] ?? '';
$category = $_POST['category'] ?? '';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$image_path = '';

if (!$type || !$category || !$title || !$description) {
    die("All fields are required.");
}

// Handle image upload
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
    die("Image upload failed.");
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$fileType = mime_content_type($_FILES['image']['tmp_name']);
if (!in_array($fileType, $allowedTypes)) {
    die("Only JPG, PNG, and GIF images are allowed.");
}

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$imageName = time() . '_' . basename($_FILES['image']['name']);
$targetPath = $uploadDir . $imageName;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
    die("Failed to save uploaded image.");
}

$image_path = $targetPath;

// Insert item into DB with status 'pending'
$stmt = $conn->prepare("INSERT INTO items (user_id, type, category, title, description, image_path, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
$stmt->bind_param("isssss", $user_id, $type, $category, $title, $description, $image_path);

if ($stmt->execute()) {
    header("Location: dashboard.php?submitted=1");
    exit();
} else {
    echo "Database error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
