<?php
session_start();

if (!isset($_SESSION['referee_id'])) {
    header('Location: ../referee.php');
    exit;
}

$refereeId = $_SESSION['referee_id'];

$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get referee info
$stmt = $conn->prepare("SELECT fname, lname FROM referee WHERE referee_id = ?");
$stmt->bind_param("i", $refereeId);
$stmt->execute();
$referee = $stmt->get_result()->fetch_assoc();
$refereeName = $referee ? $referee['fname'] . ' ' . $referee['lname'] : 'Unknown';
$initials = strtoupper(substr($referee['fname'] ?? '', 0, 1) . substr($referee['lname'] ?? '', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Matches - Referees Dashboard</title>

  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <script src="assets/js/nav.js" defer></script>
  <script src="assets/js/dropdown.js" defer></script>

  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f6f7;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #fff;
      padding: 15px 25px;
      border-bottom: 1px solid #ddd;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .page-title {
      font-size: 20px;
      font-weight: bold;
      color: #333;
    }

    .dropdown-toggle {
      display: flex;
      align-items: center;
      cursor: pointer;
      border: none;
      background: none;
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

    .dropdown-menu {
      position: absolute;
      top: 60px;
      right: 25px;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 5px;
      display: none;
      min-width: 160px;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
      z-index: 99;
    }

    .dropdown:hover .dropdown-menu { display: block; }

    .dropdown-item {
      padding: 10px 15px;
      display: block;
      color: #333;
      text-decoration: none;
    }

    .dropdown-item:hover {
      background-color: #f8f9fa;
    }

    .status-completed {
      background-color: #4CAF50;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
    }

    .team-name strong {
      margin: 0 8px;
      font-weight: bold;
      color: #222;
      font-size: 1.1em;
    }

    .btn-start-match {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 6px 14px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      margin-top: 10px;
    }

    .btn-start-match:hover {
      background-color: #0056b3;
    }

    .status-live {
      background-color: #f39c12;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
    }

    .status-upcoming {
      background-color: #3498db;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
    }

    .match-card {
      border: 1px solid #ccc;
      padding: 16px;
      border-radius: 8px;
      margin: 20px auto;
      background-color: #fff;
      max-width: 600px;
    }

    .match-info {
      margin-top: 8px;
      color: #555;
    }

    .main-content {
      padding: 30px;
    }

  </style>
</head>
<body>

<?php include "sidebar.php"; ?>

<header class="header">
  <div class="page-title">Referee Dashboard</div>
  <div class="user-info">
    <div class="dropdown">
      <button type="button" class="dropdown-toggle">
        <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
        <div class="user-info-text">
          <div class="user-name"><?= htmlspecialchars($refereeName) ?></div>
          <div class="user-role">ID: <?= htmlspecialchars($refereeId) ?></div>
        </div>
      </button>
      <div class="dropdown-menu">
        <a href="profile.php" class="dropdown-item">Profile</a>
        <a href="logout.php" class="dropdown-item">Logout</a>
      </div>
    </div>
  </div>
</header>

<div class="main-content">
  <h2>Today's Matches</h2>

  <?php if (isset($_SESSION['success_message'])): ?>
    <div style="background:#d4edda;color:#155724;padding:12px 16px;border-radius:8px;margin-bottom:16px;">
      <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
  <?php endif; ?>
  <?php if (isset($_SESSION['error_message'])): ?>
    <div style="background:#f8d7da;color:#721c24;padding:12px 16px;border-radius:8px;margin-bottom:16px;">
      <?= htmlspecialchars($_SESSION['error_message']) ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
  <?php endif; ?>

  <div class="matches-grid" id="matchesContainer">
    <?php
    $sql = "
      SELECT 
        m.id,
        m.week,
        m.stadium AS match_stadium,
        m.match_date,
        m.match_time,
        m.season,
        m.status,
        m.team1_goal,
        m.team2_goal,
        t1.name AS team1_name,
        t1.logon AS team1_logo,
        t2.name AS team2_name,
        t2.logon AS team2_logo
      FROM `match` m
      JOIN `team` t1 ON m.team1_id = t1.team_id
      JOIN `team` t2 ON m.team2_id = t2.team_id
      WHERE m.match_date = CURDATE()
      ORDER BY m.match_time ASC
    ";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
            $team1Name = htmlspecialchars($row['team1_name']);
            $team1Logo = htmlspecialchars($row['team1_logo']);
            $team2Name = htmlspecialchars($row['team2_name']);
            $team2Logo = htmlspecialchars($row['team2_logo']);
            $week = (int)$row['week'];
            $date = date("D M j", strtotime($row['match_date']));
            $time = date("H:i", strtotime($row['match_time']));
            $status = $row['status'];
            $matchStatusClass = $status === 'live' ? 'status-live' : ($status === 'completed' ? 'status-completed' : 'status-upcoming');

            $team1Goal = htmlspecialchars($row['team1_goal']);
            $team2Goal = htmlspecialchars($row['team2_goal']);
    ?>
        <div class="match-card">
          <div class="match-teams" style="display: flex; align-items: center; gap: 10px;">
            <div class="team-logo">
              <img src="../Logo/<?= $team1Logo ?>" alt="<?= $team1Name ?>" width="40" height="40" style="object-fit: cover; border-radius: 50%;" />
            </div>
            <span class="team-name">
              <?= $team1Name ?>
              <strong><?= $status === 'completed' ? "$team1Goal - $team2Goal" : 'vs'; ?></strong>
              <?= $team2Name ?>
            </span>
            <div class="team-logo">
              <img src="../Logo/<?= $team2Logo ?>" alt="<?= $team2Name ?>" width="40" height="40" style="object-fit: cover; border-radius: 50%;" />
            </div>
          </div>
          <div class="match-info">Week <?= $week ?> • <?= $date . ' ' . $time ?></div>
          <div class="match-status <?= $matchStatusClass ?>"><?= ucfirst($status) ?></div>

          <?php if ($status === 'upcoming'): ?>
            <form method="post" action="start_match.php" style="margin-top: 10px;">
              <input type="hidden" name="match_id" value="<?= $row['id']; ?>" />
              <button type="submit" class="btn-start-match">Start Match</button>
            </form>
          <?php elseif ($status === 'live'): ?>
            <div style="margin-top: 10px;">
              <a href="view_match.php?match_id=<?= $row['id']; ?>" class="btn-start-match" style="background-color: #28a745;">View</a>
              <form method="post" action="stop_match.php" style="display: inline;"
                    onsubmit="return confirm('Finish this match? Further goals, cards and substitutions cannot be recorded once it is finished.');">
                <input type="hidden" name="match_id" value="<?= $row['id']; ?>" />
                <input type="hidden" name="confirm_zero_scores" value="1" />
                <button type="submit" class="btn-start-match" style="background-color: #dc3545; margin-left: 10px;">Finish Match</button>
              </form>
            </div>
          <?php elseif ($status === 'completed'): ?>
            <div style="margin-top: 10px;">
              <a href="view_match.php?match_id=<?= $row['id']; ?>" class="btn-start-match" style="background-color: #28a745;">View</a>
            </div>
          <?php endif; ?>
        </div>
    <?php
        endwhile;
    else:
        echo "<p>No matches scheduled for today.</p>";
    endif;

    $conn->close();
    ?>
  </div>
</div>
</body>
</html>
