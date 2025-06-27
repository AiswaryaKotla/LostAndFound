<?php
session_start();
include 'db_connect.php';

// Only faculty allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = intval($_POST['item_id']);

    // Delete item from DB
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $item_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Item removed successfully.";
    } else {
        $_SESSION['message'] = "Error removing item: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $_SESSION['message'] = "Invalid request.";
}

header("Location: faculty_dashboard.php");
exit();
