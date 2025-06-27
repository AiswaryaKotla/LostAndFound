<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['item_id'])) {
    die("No item specified.");
}

$item_id = intval($_GET['item_id']);

// Fetch item details for display
$stmt = $conn->prepare("SELECT * FROM items WHERE id=?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Item not found.");
}
$item = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Submit Claim - Lost & Found</title>
</head>
<body>
  <h2>Submit Claim for Item: <?= htmlspecialchars($item['title']) ?></h2>
  <p><?= htmlspecialchars($item['description']) ?></p>
  <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Item Image" style="max-width:200px;" />

  <form action="process_claim.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="item_id" value="<?= $item_id ?>" />
    <label for="claimer_name">Your Name:</label><br />
    <input type="text" name="claimer_name" id="claimer_name" required /><br /><br />

    <label for="details">Additional Details (optional):</label><br />
    <textarea name="details" id="details" rows="4" cols="50"></textarea><br /><br />

    <label for="claim_image">Upload Proof Image (optional):</label><br />
    <input type="file" name="claim_image" id="claim_image" accept="image/*" /><br /><br />

    <button type="submit">Submit Claim</button>
  </form>

  <p><a href="faculty_dashboard.php?view=found">Back to Found Items</a></p>
</body>
</html>
