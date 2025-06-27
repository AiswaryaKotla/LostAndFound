<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = intval($_POST['item_id']);
    $claimer_name = trim($_POST['claimer_name']);
    $details = trim($_POST['details']);

    // Handle optional image upload
    $image_path = null;
    if (!empty($_FILES['claim_image']['name'])) {
        $target_dir = "uploads/claims/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $file_name = basename($_FILES["claim_image"]["name"]);
        $target_file = $target_dir . time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file_name);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image
        $check = getimagesize($_FILES["claim_image"]["tmp_name"]);
        if ($check === false) {
            $uploadOk = 0;
        }

        // Limit file size to 5MB
        if ($_FILES["claim_image"]["size"] > 5 * 1024 * 1024) {
            $uploadOk = 0;
        }

        // Allow only certain extensions
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $uploadOk = 0;
        }

        if ($uploadOk) {
            if (move_uploaded_file($_FILES["claim_image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO claims (item_id, claimer_name, details, image_path, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isss", $item_id, $claimer_name, $details, $image_path);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Claim submitted successfully. Awaiting faculty approval.";
        header("Location: faculty_dashboard.php?view=found");
        exit();
    } else {
        die("Error submitting claim.");
    }
} else {
    header("Location: faculty_dashboard.php");
    exit();
}
