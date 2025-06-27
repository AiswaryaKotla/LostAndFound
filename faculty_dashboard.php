<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$categories = ['Electronics', 'Jewellery', 'IDs', 'Others'];

function getClaimStatus($conn, $item_id) {
    $stmt = $conn->prepare("SELECT status FROM claims WHERE item_id=? ORDER BY submitted_at DESC LIMIT 1");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        return $row['status'];
    }
    return null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Faculty Dashboard - Lost & Found</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .green-button {
      background-color: #90ee90;
      color: #000;
      border: none;
      padding: 8px 16px;
      border-radius: 5px;
      cursor: pointer;
      margin: 4px;
      transition: background-color 0.3s ease;
    }
    .green-button:hover {
      background-color: #76c776;
    }
    .items-list {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      justify-content: center;
      margin-top: 1rem;
    }
    .item-card {
      width: 300px;
      border: 1px solid #ccc;
      border-radius: 8px;
      padding: 10px;
      background: #f9f9f9;
      box-shadow: 2px 2px 6px rgba(0,0,0,0.1);
      text-align: center;
    }
    .item-card img {
      max-width: 100%;
      height: auto;
      border-radius: 6px;
    }
  </style>
  <script>
    function showSection(id) {
      document.querySelectorAll('.section').forEach(sec => sec.style.display = 'none');
      document.getElementById(id).style.display = 'block';
    }

    function filterItems(type) {
      const category = document.getElementById(type + '-category').value;
      window.location.href = 'faculty_dashboard.php?view=' + type + '&category=' + category;
    }
  </script>
</head>
<body>
  <nav>
    <div class="logo">Lost & Found Portal</div>
    <ul>
      <li><a href="home.html">Home</a></li>
      <li><a href="about.html">About</a></li>
      <li><a href="contact.html">Contact</a></li>
    </ul>
  </nav>

  <main>
    <h2 style="text-align:center; margin-top:1rem;">Faculty Dashboard</h2>
    <p style="text-align:center;">Logged in as: <strong>Faculty</strong></p>

    <div class="dashboard-buttons" style="text-align:center;">
      <button class="green-button" onclick="showSection('view-lost')">View Lost Items</button>
      <button class="green-button" onclick="showSection('view-found')">View Found Items</button>
      <button class="green-button" onclick="showSection('report-lost')">Report Lost Item</button>
      <button class="green-button" onclick="showSection('report-found')">Report Found Item</button>
      <button class="green-button" onclick="window.location.href='verify_item.php'">Verify</button>
      <button class="green-button" onclick="window.location.href='delete_item.php'">Delete</button>
      <button class="green-button" onclick="window.location.href='verify_claims.php'">Verify Claims</button>
    </div>

    <!-- Lost Items -->
    <section id="view-lost" class="section" style="display:none;">
      <h3 style="text-align:center;">Lost Items</h3>
      <div style="text-align:center;">
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
          $cat = $_GET['category'] ?? strtolower($categories[0]);
          $stmt = $conn->prepare("SELECT * FROM items WHERE type='lost' AND status='approved' AND LOWER(category)=? ORDER BY created_at DESC");
          $stmt->bind_param("s", $cat);
          $stmt->execute();
          $res = $stmt->get_result();
          if ($res->num_rows > 0) {
            while ($item = $res->fetch_assoc()) {
              $claimStatus = getClaimStatus($conn, $item['id']);
              echo '<div class="item-card">';
              echo '<a href="' . htmlspecialchars($item['image_path']) . '" class="lightbox-trigger"><img src="' . htmlspecialchars($item['image_path']) . '" alt="Item Image"></a>';
              echo '<div class="item-details">';
              echo '<h4>' . htmlspecialchars($item['title']);
              if ($claimStatus) {
                  $color = $claimStatus === 'approved' ? 'green' : ($claimStatus === 'rejected' ? 'red' : 'orange');
                  echo " <small style='font-size:0.8rem; color:$color;'>[" . ucfirst($claimStatus) . "]</small>";
              }
              echo '</h4>';
              echo '<p>' . htmlspecialchars($item['description']) . '</p>';
              echo '</div>';
              echo '<form method="GET" action="found.php">';
              echo '<input type="hidden" name="item_id" value="' . $item['id'] . '">';
              echo '<button type="submit" class="green-button">Found</button>';
              echo '</form>';
              echo '</div>';
            }
          } else {
            echo "<p style='text-align:center;'>No lost items found in this category.</p>";
          }
        }
        ?>
      </div>
    </section>

    <!-- Found Items -->
    <section id="view-found" class="section" style="display:none;">
      <h3 style="text-align:center;">Found Items</h3>
      <div style="text-align:center;">
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
          $cat = $_GET['category'] ?? strtolower($categories[0]);
          $stmt = $conn->prepare("SELECT * FROM items WHERE type='found' AND status='approved' AND LOWER(category)=? ORDER BY created_at DESC");
          $stmt->bind_param("s", $cat);
          $stmt->execute();
          $res = $stmt->get_result();
          if ($res->num_rows > 0) {
            while ($item = $res->fetch_assoc()) {
              $claimStatus = getClaimStatus($conn, $item['id']);
              echo '<div class="item-card">';
              echo '<a href="' . htmlspecialchars($item['image_path']) . '" class="lightbox-trigger"><img src="' . htmlspecialchars($item['image_path']) . '" alt="Item Image"></a>';
              echo '<div class="item-details">';
              echo '<h4>' . htmlspecialchars($item['title']);
              if ($claimStatus) {
                  $color = $claimStatus === 'approved' ? 'green' : ($claimStatus === 'rejected' ? 'red' : 'orange');
                  echo " <small style='font-size:0.8rem; color:$color;'>[" . ucfirst($claimStatus) . "]</small>";
              }
              echo '</h4>';
              echo '<p>' . htmlspecialchars($item['description']) . '</p>';
              echo '</div>';
              echo '<form method="GET" action="claim.php">';
              echo '<input type="hidden" name="item_id" value="' . $item['id'] . '">';
              echo '<button type="submit" class="green-button">Claim</button>';
              echo '</form>';
              echo '</div>';
            }
          } else {
            echo "<p style='text-align:center;'>No found items found in this category.</p>";
          }
        }
        ?>
      </div>
    </section>

    <!-- Report Lost Item Section -->
    <section id="report-lost" class="section" style="display:none;">
      <h3>Report Lost Item</h3>
      <!-- your existing form for reporting lost items -->
    </section>

    <!-- Report Found Item Section -->
    <section id="report-found" class="section" style="display:none;">
      <h3>Report Found Item</h3>
      <!-- your existing form for reporting found items -->
    </section>

  </main>

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    const view = urlParams.get('view');
    if(view === 'lost') {
      showSection('view-lost');
      document.getElementById('lost-category').value = urlParams.get('category') || 'electronics';
    } else if(view === 'found') {
      showSection('view-found');
      document.getElementById('found-category').value = urlParams.get('category') || 'electronics';
    } else {
      showSection('view-lost');
    }
  </script>
</body>
</html>
