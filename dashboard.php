<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Define allowed categories (excluding Clothing)
$categories = ['Electronics', 'Jewellery', 'IDs', 'Others'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Dashboard - Lost & Found Portal</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    <?php include 'style.css'; ?>
    .dashboard-buttons { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; margin: 2rem 0; }
    .dashboard-buttons button { padding: 15px 25px; font-size: 1.1rem; border-radius: 8px; border: none; cursor: pointer; background-color: #27ae60; color: white; transition: background-color 0.3s ease; }
    .dashboard-buttons button:hover { background-color: #1e8449; }
    .item-card { background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); padding: 1rem; margin-bottom: 1rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
    .item-card img { width: 120px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
    .item-details { flex: 1; }
    .item-details h4 { margin: 0 0 0.5rem; color: #27ae60; }
    .item-details p { margin: 0; }
    .claim-btn { margin-top: 10px; padding: 8px 15px; background-color: #2980b9; color: white; border: none; border-radius: 6px; cursor: pointer; }
    .claim-btn:hover { background-color: #1c5980; }
    .report-form { max-width: 600px; margin: 0 auto 2rem; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
    .report-form h3 { margin-bottom: 1rem; color: #2c3e50; text-align: center; }
    .report-form label { display: block; margin: 0.8rem 0 0.3rem; font-weight: 600; }
    .report-form select, .report-form input[type="text"], .report-form textarea { width: 100%; padding: 0.7rem; border-radius: 8px; border: 1px solid #ccc; font-family: 'Segoe UI', sans-serif; }
    .report-form textarea { resize: vertical; min-height: 80px; }
    .report-form button { margin-top: 1rem; width: 100%; background-color: #27ae60; color: white; border: none; border-radius: 8px; padding: 0.9rem; font-weight: bold; cursor: pointer; font-size: 1.1rem; }
    .report-form button:hover { background-color: #1e8449; }
    .status-label { margin-top: 10px; font-weight: bold; color: #555; }
  </style>
  <script>
    function showSection(id) {
      document.querySelectorAll('.section').forEach(sec => sec.style.display = 'none');
      document.getElementById(id).style.display = 'block';
    }
    function filterItems(type) {
      const category = document.getElementById(type + '-category').value;
      window.location.href = 'dashboard.php?view=' + type + '&category=' + category;
    }
  </script>
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

  <main>
    <h2 style="text-align:center; margin-top:2rem;">Welcome to your Dashboard</h2>
    <p style="text-align:center;">Logged in as: <strong>student</strong></p>

    <div class="dashboard-buttons">
      <button onclick="showSection('view-lost')">View Lost Items</button>
      <button onclick="showSection('view-found')">View Found Items</button>
      <button onclick="showSection('report-lost')">Report Lost Item</button>
      <button onclick="showSection('report-found')">Report Found Item</button>
    </div>

    <!-- View Lost Items -->
    <section id="view-lost" class="section" style="display:none;">
      <h3 style="text-align:center;">Lost Items</h3>
      <div class="filter-category" style="text-align:center;">
        <label for="lost-category">Filter by Category:</label>
        <select id="lost-category" onchange="filterItems('lost')">
          <?php foreach ($categories as $cat): ?>
            <option value="<?= strtolower($cat) ?>" <?= ($_GET['view'] ?? '') === 'lost' && ($_GET['category'] ?? '') === strtolower($cat) ? 'selected' : '' ?>>
              <?= $cat ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="items-list">
        <?php
          if (($_GET['view'] ?? '') === 'lost') {
            $filter_category = $_GET['category'] ?? strtolower($categories[0]);
            $stmt = $conn->prepare("SELECT * FROM items WHERE type='lost' AND status='approved' AND LOWER(category) = ? ORDER BY created_at DESC");
            $stmt->bind_param("s", $filter_category);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
              echo '<div class="items-grid">';
              while ($item = $result->fetch_assoc()) {
                echo '<div class="item-card">';
                echo '<a href="' . htmlspecialchars($item['image_path']) . '" class="lightbox-trigger">';
                echo '<img src="' . htmlspecialchars($item['image_path']) . '" alt="Item Image">';
                echo '</a>';
                echo '<div class="item-details">';
                echo '<h4>' . htmlspecialchars($item['title']) . '</h4>';
                echo '<p>' . htmlspecialchars($item['description']) . '</p>';
                echo '<a href="found.php?item_id=' . $item['id'] . '"><button class="claim-btn">Found</button></a>';
                echo '</div></div>';
              }
              echo '</div>';
            } else {
              echo "<p style='text-align:center;'>No lost items found in this category.</p>";
            }
          }
        ?>
      </div>
    </section>

    <!-- View Found Items -->
    <section id="view-found" class="section" style="display:none;">
      <h3 style="text-align:center;">Found Items</h3>
      <div class="filter-category" style="text-align:center;">
        <label for="found-category">Filter by Category:</label>
        <select id="found-category" onchange="filterItems('found')">
          <?php foreach ($categories as $cat): ?>
            <option value="<?= strtolower($cat) ?>" <?= ($_GET['view'] ?? '') === 'found' && ($_GET['category'] ?? '') === strtolower($cat) ? 'selected' : '' ?>>
              <?= $cat ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="items-list">
        <?php
          if (($_GET['view'] ?? '') === 'found') {
            $filter_category = $_GET['category'] ?? strtolower($categories[0]);
            $stmt = $conn->prepare("SELECT * FROM items WHERE type='found' AND status='approved' AND LOWER(category) = ? ORDER BY created_at DESC");
            $stmt->bind_param("s", $filter_category);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
              echo '<div class="items-grid">';
              while ($item = $result->fetch_assoc()) {
                echo '<div class="item-card">';
                echo '<a href="' . htmlspecialchars($item['image_path']) . '" class="lightbox-trigger">';
                echo '<img src="' . htmlspecialchars($item['image_path']) . '" alt="Item Image">';
                echo '</a>';
                echo '<div class="item-details">';
                echo '<h4>' . htmlspecialchars($item['title']) . '</h4>';
                echo '<p>' . htmlspecialchars($item['description']) . '</p>';
                echo '<a href="claim.php?item_id=' . $item['id'] . '"><button class="claim-btn">Claim</button></a>';
                echo '</div></div>';
              }
              echo '</div>';
            } else {
              echo "<p style='text-align:center;'>No found items found in this category.</p>";
            }
          }
        ?>
      </div>
    </section>

    <!-- Report Lost Item -->
    <section id="report-lost" class="section" style="display:none;">
      <form class="report-form" method="POST" action="submit_item.php" enctype="multipart/form-data">
        <h3>Report Lost Item</h3>
        <input type="hidden" name="type" value="lost" />
        <input type="hidden" name="user_id" value="<?= $user_id ?>" />
        <label>Category:</label>
        <select name="category" required>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= strtolower($cat) ?>"><?= $cat ?></option>
          <?php endforeach; ?>
        </select>
        <label>Title:</label>
        <input type="text" name="title" placeholder="e.g. Black Wallet" required />
        <label>Description:</label>
        <textarea name="description" placeholder="Enter details..." required></textarea>
        <label>Image:</label>
        <input type="file" name="image" accept="image/*" required />
        <button type="submit">Submit Lost Item</button>
      </form>
    </section>

    <!-- Report Found Item -->
    <section id="report-found" class="section" style="display:none;">
      <form class="report-form" method="POST" action="submit_item.php" enctype="multipart/form-data">
        <h3>Report Found Item</h3>
        <input type="hidden" name="type" value="found" />
        <input type="hidden" name="user_id" value="<?= $user_id ?>" />
        <label>Category:</label>
        <select name="category" required>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= strtolower($cat) ?>"><?= $cat ?></option>
          <?php endforeach; ?>
        </select>
        <label>Title:</label>
        <input type="text" name="title" placeholder="e.g. Gold Ring" required />
        <label>Description:</label>
        <textarea name="description" placeholder="Enter details..." required></textarea>
        <label>Image:</label>
        <input type="file" name="image" accept="image/*" required />
        <button type="submit">Submit Found Item</button>
      </form>
    </section>
  </main>

  <script>
    window.onload = () => {
      const view = new URLSearchParams(window.location.search).get('view');
      if (view === 'lost') showSection('view-lost');
      else if (view === 'found') showSection('view-found');
      else if (view === 'report-lost') showSection('report-lost');
      else if (view === 'report-found') showSection('report-found');
    };
  </script>

  <div class="lightbox-overlay" id="lightboxOverlay">
    <span class="lightbox-close" id="lightboxClose">&times;</span>
    <img id="lightboxImage" src="" alt="Expanded image">
  </div>

  <script>
    const lightboxOverlay = document.getElementById('lightboxOverlay');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxClose = document.getElementById('lightboxClose');

    document.querySelectorAll('.lightbox-trigger').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        lightboxImage.src = this.href;
        lightboxOverlay.style.display = 'flex';
      });
    });

    lightboxClose.addEventListener('click', () => {
      lightboxOverlay.style.display = 'none';
      lightboxImage.src = '';
    });

    lightboxOverlay.addEventListener('click', (e) => {
      if (e.target === lightboxOverlay) {
        lightboxOverlay.style.display = 'none';
        lightboxImage.src = '';
      }
    });
  </script>
</body>
</html>
