<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: index.php");
    exit();
}

// Fetch all pending claims with item details
$sql = "SELECT c.*, i.title, i.description AS item_description, i.image_path AS item_image
        FROM claims c
        JOIN items i ON c.item_id = i.id
        WHERE c.status='pending'
        ORDER BY c.submitted_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Verify Claims - Lost & Found</title>
  <style>
    .claim-card {
      border: 1px solid #ccc;
      margin: 1rem;
      padding: 1rem;
      max-width: 600px;
    }
    .claim-card img {
      max-width: 150px;
      display: block;
      margin-bottom: 0.5rem;
    }
  </style>
</head>
<body>
  <h2>Verify Claims</h2>

  <?php if ($result->num_rows === 0): ?>
    <p>No pending claims to verify.</p>
  <?php else: ?>
    <?php while ($claim = $result->fetch_assoc()): ?>
      <div class="claim-card">
        <h3>Claim on Item: <?= htmlspecialchars($claim['title']) ?></h3>
        <p><strong>Item Description:</strong> <?= htmlspecialchars($claim['item_description']) ?></p>
        <img src="<?= htmlspecialchars($claim['item_image']) ?>" alt="Item Image" />

        <p><strong>Claimer Name:</strong> <?= htmlspecialchars($claim['claimer_name']) ?></p>
        <p><strong>Details:</strong> <?= nl2br(htmlspecialchars($claim['details'])) ?></p>
        <?php if ($claim['image_path']): ?>
          <p>Proof Image:</p>
          <img src="<?= htmlspecialchars($claim['image_path']) ?>" alt="Proof Image" />
        <?php endif; ?>

        <form action="process_verify_claim.php" method="post" style="margin-top:10px;">
          <input type="hidden" name="claim_id" value="<?= $claim['id'] ?>" />
          <button type="submit" name="action" value="approve">Approve</button>
          <button type="submit" name="action" value="reject">Reject</button>
        </form>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>

  <p><a href="faculty_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
