<?php
session_start();

if (!isset($_SESSION['referee_id'])) {
    header("Location: login.php");
    exit;
}

$refereeId = $_SESSION['referee_id'];

$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get referee info for header
$stmt = $conn->prepare("SELECT fname, lname FROM referee WHERE referee_id = ?");
$stmt->bind_param("i", $refereeId);
$stmt->execute();
$referee = $stmt->get_result()->fetch_assoc();
$refereeName = $referee ? $referee['fname'] . ' ' . $referee['lname'] : 'Unknown';
$initials = strtoupper(substr($referee['fname'] ?? '', 0, 1) . substr($referee['lname'] ?? '', 0, 1));

// Query matches assigned to referee in any role
$sql = "
    SELECT 
        m.id,
        m.week,
        m.stadium,
        m.match_date,
        m.match_time,
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
    JOIN `weekly_fixtures` wf ON m.id = wf.match_id
    WHERE wf.referee = ? OR wf.official = ?
    ORDER BY 
        CASE 
            WHEN m.status = 'live' THEN 1
            WHEN m.status = 'upcoming' THEN 2
            WHEN m.status = 'completed' THEN 3
            ELSE 4
        END,
        m.match_date ASC,
        m.match_time ASC
";

$stmtMatches = $conn->prepare($sql);
$stmtMatches->bind_param("ii", $refereeId, $refereeId);
$stmtMatches->execute();
$result = $stmtMatches->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>My Assigned Matches - Referee Dashboard</title>

<link rel="stylesheet" href="assets/css/styles.css">
<link rel="stylesheet" href="assets/css/components.css">
<script src="assets/js/nav.js" defer></script>
<script src="assets/js/dropdown.js" defer></script>

<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background: #f5f6f7;
    margin: 0;
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

  .user-avatar {
    background-color: #007bff;
    color: white;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    margin-right: 10px;
  }

  .dropdown-toggle {
    display: flex;
    align-items: center;
    cursor: pointer;
    border: none;
    background: none;
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

  .main-content {
    padding: 30px;
    max-width: 900px;
    margin: auto;
  }

  .match-card {
    background: #fff;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .match-teams {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .team-logo img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
  }

  .team-name {
    font-weight: 600;
    font-size: 1.1rem;
  }

  .match-info {
    text-align: center;
    color: #555;
    min-width: 140px;
  }

  .match-status {
    min-width: 90px;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: bold;
    text-transform: capitalize;
    color: #fff;
    text-align: center;
  }

  .status-live {
    background-color: #e53935;
  }

  .status-upcoming {
    background-color: #fbc02d;
    color: #333;
  }

  .status-completed {
    background-color: #4caf50;
  }

  /* Goal Entry Styles */
  .match-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  .btn-goal-entry, .btn-view-match {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s ease;
  }

  .btn-goal-entry {
    background-color: #28a745;
    color: white;
  }

  .btn-goal-entry:hover {
    background-color: #218838;
  }

  .btn-view-match {
    background-color: #007bff;
    color: white;
    display: inline-block;
  }

  .btn-view-match:hover {
    background-color: #0056b3;
    text-decoration: none;
  }

  .score-display {
    color: #28a745;
    font-size: 1.1em;
  }

  /* Alert Styles */
  .alert {
    padding: 12px 16px;
    margin-bottom: 20px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  /* Modal Styles */
  .modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
  }

  .modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
  }

  .modal-header {
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-header h3 {
    margin: 0;
    color: #333;
  }

  .close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #aaa;
  }

  .close:hover {
    color: #000;
  }

  .modal-body {
    padding: 20px;
  }

  .teams-goals {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 20px;
  }

  .team-goal-input {
    flex: 1;
    text-align: center;
  }

  .team-goal-input label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
  }

  .team-goal-input input {
    width: 80px;
    padding: 10px;
    border: 2px solid #ddd;
    border-radius: 4px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
  }

  .team-goal-input input:focus {
    outline: none;
    border-color: #007bff;
  }

  .vs-separator {
    font-weight: bold;
    font-size: 18px;
    color: #666;
  }

  .modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
  }

  .btn-cancel, .btn-save {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
  }

  .btn-cancel {
    background-color: #6c757d;
    color: white;
  }

  .btn-cancel:hover {
    background-color: #545b62;
  }

  .btn-save {
    background-color: #28a745;
    color: white;
  }

  .btn-save:hover {
    background-color: #218838;
  }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<header class="header">
  <div class="page-title">My Assigned Matches</div>
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
  <h2>My Assigned Matches</h2>

  <!-- Success/Error Messages -->
  <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
      <i class="fa fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error">
      <i class="fa fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
  <?php endif; ?>

  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()):
      $team1Name = htmlspecialchars($row['team1_name']);
      $team1Logo = htmlspecialchars($row['team1_logo']);
      $team2Name = htmlspecialchars($row['team2_name']);
      $team2Logo = htmlspecialchars($row['team2_logo']);
      $date = date("D, M j, Y", strtotime($row['match_date']));
      $time = date("H:i", strtotime($row['match_time']));
      $status = $row['status'];
      $team1Goal = $row['team1_goal'] !== null ? intval($row['team1_goal']) : 0;
      $team2Goal = $row['team2_goal'] !== null ? intval($row['team2_goal']) : 0;
      $statusClass = ($status === 'live') ? 'status-live' : (($status === 'upcoming') ? 'status-upcoming' : 'status-completed');
      $matchId = intval($row['id']);
    ?>
      <div class="match-card" data-match-id="<?= $matchId ?>">
        <div class="match-teams">
          <div class="team-logo"><img src="../Logo/<?= $team1Logo ?>" alt="<?= $team1Name ?>"></div>
          <div class="team-name">
            <?= $team1Name ?>
            <strong class="score-display"><?= $team1Goal ?> - <?= $team2Goal ?></strong>
            <?= $team2Name ?>
          </div>
          <div class="team-logo"><img src="../Logo/<?= $team2Logo ?>" alt="<?= $team2Name ?>"></div>
        </div>
        <div class="match-info"><?= $date ?> <?= $time ?><br><small><?= htmlspecialchars($row['stadium']) ?></small></div>
        <div class="match-actions">
          <div class="match-status <?= $statusClass ?>"><?= ucfirst($status) ?></div>
          <?php if ($status === 'live' || $status === 'upcoming'): ?>
            <button type="button" class="btn-goal-entry" onclick="openGoalForm(<?= $matchId ?>, '<?= addslashes($team1Name) ?>', '<?= addslashes($team2Name) ?>', <?= $team1Goal ?>, <?= $team2Goal ?>)">
              ⚽ Update Goals
            </button>
          <?php endif; ?>
          <a href="view_match.php?match_id=<?= $matchId ?>" class="btn-view-match">
            👁️ View Match
          </a>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No matches assigned to you yet.</p>
  <?php endif; ?>

</div>

<!-- Goal Entry Modal -->
<div id="goalModal" class="modal" style="display: none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Update Match Goals</h3>
      <span class="close" onclick="closeGoalForm()">&times;</span>
    </div>
    <div class="modal-body">
      <form id="goalForm" method="POST" action="update_goals.php">
        <input type="hidden" id="modalMatchId" name="match_id" value="">

        <div class="teams-goals">
          <div class="team-goal-input">
            <label id="team1Label" for="team1Goals">Team 1:</label>
            <input type="number" id="team1Goals" name="team1_goals" min="0" max="99" value="0" required>
          </div>

          <div class="vs-separator">VS</div>

          <div class="team-goal-input">
            <label id="team2Label" for="team2Goals">Team 2:</label>
            <input type="number" id="team2Goals" name="team2_goals" min="0" max="99" value="0" required>
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick="closeGoalForm()">Cancel</button>
          <button type="submit" class="btn-save">Update Goals</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openGoalForm(matchId, team1Name, team2Name, team1Goals, team2Goals) {
  document.getElementById('modalMatchId').value = matchId;
  document.getElementById('team1Label').textContent = team1Name + ':';
  document.getElementById('team2Label').textContent = team2Name + ':';
  document.getElementById('team1Goals').value = team1Goals;
  document.getElementById('team2Goals').value = team2Goals;
  document.getElementById('goalModal').style.display = 'block';
}

function closeGoalForm() {
  document.getElementById('goalModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
  const modal = document.getElementById('goalModal');
  if (event.target === modal) {
    closeGoalForm();
  }
}
</script>

</body>
</html>

<?php
$stmt->close();
$stmtMatches->close();
$conn->close();
?>
