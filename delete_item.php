<?php
session_start();
include 'db_connect.php';

// Ensure only faculty can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: index.php");
    exit();
}

// Handle deletion if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = intval($_POST['item_id']);
    // First delete image from server (optional)
    $stmt = $conn->prepare("SELECT image_path FROM items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $stmt->bind_result($image_path);
    if ($stmt->fetch() && file_exists($image_path)) {
        unlink($image_path);
    }
    $stmt->close();

    // Delete item from database
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $stmt->close();

    header("Location: delete_item.php?deleted=1");
    exit();
}

// Fetch approved items
$stmt = $conn->prepare("SELECT * FROM items WHERE status='approved' ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Items - Faculty Dashboard</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .item-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 1rem;
            margin: 1rem auto;
            display: flex;
            align-items: center;
            gap: 1rem;
            max-width: 800px;
        }
        .item-card img {
            width: 100px;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
        }
        .item-details { flex: 1; }
        .delete-btn {
            background-color: #c0392b;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
        }
        .delete-btn:hover {
            background-color: #922b21;
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Delete Approved Items</h2>
    <?php if (isset($_GET['deleted'])): ?>
        <p style="color: green; text-align:center;">Item deleted successfully!</p>
    <?php endif; ?>

    <?php while ($item = $result->fetch_assoc()): ?>
        <div class="item-card">
            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Item Image">
            <div class="item-details">
                <h4><?= htmlspecialchars($item['title']) ?> (<?= ucfirst($item['type']) ?>)</h4>
                <p><?= htmlspecialchars($item['description']) ?></p>
            </div>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');">
                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                <button type="submit" class="delete-btn">Delete</button>
            </form>
        </div>
    <?php endwhile; ?>

</body>
</html>
