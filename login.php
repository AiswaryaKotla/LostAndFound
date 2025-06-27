<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_id = $_POST["email"];
    $password = $_POST["password"];

    // Allow both email and ID as login input
    $stmt = $conn->prepare("SELECT * FROM users WHERE (email = ? OR id = ?) AND password = ?");
    $stmt->bind_param("sis", $email_or_id, $email_or_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        if ($user['role'] === 'faculty') {
            header("Location: dashboard.php");
        } else {
            header("Location: faculty_dashboard.php");
        }
        exit();
    } else {
        echo "<script>alert('Invalid login credentials'); window.location.href='index.php';</script>";
    }
}
?>
