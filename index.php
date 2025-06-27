<?php
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php';

$errorStudent = '';
$errorFaculty = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_id = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    // Adjust SQL to allow either email or numeric ID
    $stmt = $conn->prepare("SELECT * FROM users WHERE (email=? OR id=?) AND password=? AND role=?");
    $stmt->bind_param("siss", $email_or_id, $email_or_id, $password, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        if ($role === 'faculty') {
            header("Location: faculty_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        if ($role === 'student') {
            $errorStudent = "Invalid student credentials!";
        } elseif ($role === 'faculty') {
            $errorFaculty = "Invalid faculty credentials!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - Lost & Found Portal</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .login-section {
      gap: 120px;
    }
    .error-msg {
      color: red;
      margin-top: 0.5rem;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <nav>
    <div class="logo">Lost & Found Portal</div>
    <ul>
      <li><a href="home.html">Home</a></li>
      <li><a href="about.html">About Us</a></li>
      <li><a href="contact.html">Contact</a></li>
    </ul>
  </nav>

  <div class="login-section">
    <!-- Student Login Box -->
    <div class="login-box">
      <img src="images/student.png" alt="Student Icon" />
      <h3>Student Login</h3>
      <form method="POST" action="">
        <input type="text" name="email" placeholder="Student ID or Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <input type="hidden" name="role" value="student" />
        <button type="submit">Login</button>
      </form>
      <?php if ($errorStudent): ?>
        <p class="error-msg"><?php echo $errorStudent; ?></p>
      <?php endif; ?>
    </div>

    <!-- Faculty Login Box -->
    <div class="login-box">
      <img src="images/faculty.png" alt="Faculty Icon" />
      <h3>Faculty Login</h3>
      <form method="POST" action="">
        <input type="text" name="email" placeholder="Faculty ID or Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <input type="hidden" name="role" value="faculty" />
        <button type="submit">Login</button>
      </form>
      <?php if ($errorFaculty): ?>
        <p class="error-msg"><?php echo $errorFaculty; ?></p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
