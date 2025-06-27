<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: index.php");
    exit();
}
include 'db_connect.php';

// Only allow access if logged in as faculty
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: index.php");
    exit();
}

// Handle approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE items SET status='approved' WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
    }

    header("Location: verify_item.php");
    exit();
}

// Get all pending items
$stmt = $conn->prepare("SELECT * FROM items WHERE status='pending' ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Items - Lost & Found Portal</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        h2 { text-align: center; color: #27ae60; }
        .items-container { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; margin-top: 2rem; }
        .item-card {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 30%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .item-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ccc;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .item-card img:hover {
            transform: scale(1.05);
        }
        .item-details {
            width: 100%;
            text-align: center;
        }
        .item-details h4 {
            margin: 0 0 0.5rem;
            color: #2c3e50;
        }
        .item-details p {
            margin: 0.2rem 0;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 1rem;
        }
        .action-buttons form button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        .approve-btn { background-color: #27ae60; }
        .reject-btn { background-color: #e74c3c; }
    </style>
</head>
<body>
    <h2>Pending Items for Verification</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="items-container">
            <?php while ($item = $result->fetch_assoc()): ?>
                <div class="item-card">
                    <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Item Image">
                    <div class="item-details">
                        <h4><?= htmlspecialchars($item['title']) ?> (<?= ucfirst($item['type']) ?>)</h4>
                        <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($item['description']) ?></p>
                        <p><strong>Date Submitted:</strong> <?= htmlspecialchars($item['created_at']) ?></p>
                    </div>
                    <div class="action-buttons">
                        <form method="POST" action="">
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="approve-btn">Approve</button>
                        </form>
                        <form method="POST" action="">
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="reject-btn">Reject</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center;">No pending items to verify.</p>
    <?php endif; ?>

</body>
</html>
