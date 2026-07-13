<?php

// Get referee ID from session - callers are expected to have already
// redirected unauthenticated visitors before including this header.
$refereeId = $_SESSION['referee_id'] ?? null;

// Connect to DB
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get referee info
$stmt = $conn->prepare("SELECT fname, lname FROM referee WHERE referee_id = ?");
$stmt->bind_param("i", $refereeId);
$stmt->execute();
$result = $stmt->get_result();
$referee = $result->fetch_assoc();

$refereeName = $referee ? $referee['fname'] . ' ' . $referee['lname'] : 'Unknown';
$initials = strtoupper(substr($referee['fname'] ?? '', 0, 1) . substr($referee['lname'] ?? '', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Referee Header</title>
  <style>
    body {
      font-family: Arial, sans-serif;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #f8f9fa;
      padding: 15px 25px;
      border-bottom: 1px solid #dee2e6;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .page-title {
      font-size: 1.5rem;
      font-weight: bold;
      color: #343a40;
    }

    .user-info {
      position: relative;
    }

    .dropdown-toggle {
      display: flex;
      align-items: center;
      cursor: pointer;
      border: none;
      background: none;
      padding: 0;
    }

    .user-avatar {
      background-color: #007bff;
      color: white;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      margin-right: 10px;
    }

    .user-info-text {
      text-align: left;
    }

    .user-name {
      font-weight: bold;
      font-size: 0.95rem;
    }

    .user-role {
      font-size: 0.8rem;
      color: #6c757d;
    }

    .dropdown {
  position: relative;
}

.dropdown:hover .dropdown-menu,
.dropdown:focus-within .dropdown-menu {
  display: block;
}

.dropdown-menu {
  display: none;
  position: absolute;
  top: 55px;
  right: 0;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 5px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
  min-width: 180px;
  z-index: 10;
}

.dropdown-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  color: #333;
  text-decoration: none;
  transition: background-color 0.2s ease;
  gap: 10px;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
  color: #333;
  text-decoration: none;
}

.dropdown-item:last-child {
  color: #dc3545;
}

.dropdown-item:last-child:hover {
  background-color: #f8d7da;
  color: #721c24;
}

.dropdown-icon {
  fill: currentColor;
  flex-shrink: 0;
}

.dropdown-divider {
  height: 1px;
  background-color: #e9ecef;
  margin: 8px 0;
}

.dropdown-arrow {
  fill: #6c757d;
  margin-left: 8px;
  transition: transform 0.2s ease;
}

.dropdown:hover .dropdown-arrow {
  transform: rotate(180deg);
}

  </style>
</head>
<body>
  <header class="header">
    <h1 class="page-title"></h1>
    <div class="user-info">
      <div class="dropdown">
        <button type="button" class="dropdown-toggle">
          <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
          <div class="user-info-text">
            <div class="user-name"><?= htmlspecialchars($refereeName) ?></div>
            <div class="user-role">ID: <?= htmlspecialchars($refereeId) ?></div>
          </div>
          <svg class="dropdown-arrow" viewBox="0 0 24 24" width="16" height="16">
            <path d="M7 10l5 5 5-5z"/>
          </svg>
        </button>
        <div class="dropdown-menu">
          <a href="profile.php" class="dropdown-item">
            <svg class="dropdown-icon" viewBox="0 0 24 24" width="16" height="16">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <span>Profile</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item" onclick="return confirm('Are you sure you want to logout?')">
            <svg class="dropdown-icon" viewBox="0 0 24 24" width="16" height="16">
              <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
            </svg>
            <span>Logout</span>
          </a>
        </div>
      </div>
    </div>
  </header>
</body>
</html>
