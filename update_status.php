<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $action = $_POST['action'];

    switch ($action) {
        case 'approve':
            $status = 'approved';
            break;
        case 'reject':
            $status = 'rejected';
            break;
        case 'delete':
            // For delete, we just remove the row from the DB
            $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            header("Location: faculty_dashboard.php");
            exit();
        default:
            $status = 'pending';
    }

    $stmt = $conn->prepare("UPDATE items SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $item_id);
    $stmt->execute();
}

header("Location: faculty_dashboard.php");
exit();
