<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claim_id = $_POST['claim_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE claims SET status='approved' WHERE id=?");
    } else {
        $stmt = $conn->prepare("UPDATE claims SET status='rejected' WHERE id=?");
    }

    $stmt->bind_param("i", $claim_id);
    $stmt->execute();

    header("Location: verify_claims.php");
    exit();
}
?>
