<?php require_once 'header.php'; ?>

<style>
  /* Card styles */
  .card {
    background-color: #1e293b; /* dark blue-gray */
    border-radius: 12px;
    border: none;
    color: #f1f5f9; /* light text */
    transition: box-shadow 0.3s ease;
  }
  .card:hover {
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
  }
  .card-footer {
    background-color: #273449;
    border-top: 1px solid #374151;
    color: #a1a1aa;
    font-weight: 500;
  }
  .card-body i {
    color: #6366f1; /* indigo */
  }
  .card-category {
    color: #cbd5e1; /* lighter gray */
    font-weight: 600;
  }
  .card-title {
    font-size: 2.1rem;
    font-weight: 700;
    color: #e0e7ff; /* very light */
  }

  /* Table styles */
  thead {
    background-color: #273449;
  }
  thead th {
    color: #cbd5e1;
    font-weight: 600;
  }
  tbody tr {
    background-color: #334155;
    transition: background-color 0.2s;
  }
  tbody tr:hover {
    background-color: #475569;
  }
  tbody td {
    color: #f8fafc;
  }

  /* Team logos */
  .rounded-circle {
    border: 2px solid #6366f1;
    object-fit: cover;
  }

  /* Match card styles */
  .match-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 12px;
    background: #f9f9f9;
    max-width: 600px;
  }
  .match-teams {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
  }
  .team-logo img {
    border-radius: 50%;
    width: 40px;
    height: 40px;
    object-fit: cover;
  }
  .team-name {
    font-weight: 600;
    font-size: 1rem;
    color: #222;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .team-name strong {
    margin: 0 8px;
    color: #555;
    font-weight: 700;
  }
  .match-info {
    font-size: 0.85rem;
    color: #555;
    margin-top: 6px;
    text-align: center;
  }
  .match-status {
    margin-top: 6px;
    font-weight: 700;
    text-align: center;
    padding: 4px 8px;
    border-radius: 4px;
    width: fit-content;
    margin: 0 auto;
    font-size: 0.9rem;
  }
  .status-live {
    background-color: #f87171; /* red */
    color: white;
  }
  .status-completed {
    background-color: #4caf50; /* green */
    color: white;
  }
  .status-upcoming {
    background-color: #3b82f6; /* blue */
    color: white;
  }
</style>

<div class="page-wrapper">
  <div class="container-fluid py-4">

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
      <?php
      // Summary cards configuration
      $stats = [
        ['title' => 'Teams', 'icon' => 'fa-users', 'color' => 'indigo', 'table' => 'team'],
        ['title' => 'Referees', 'icon' => 'fa-user-shield', 'color' => 'cyan', 'table' => 'referee'],
        ['title' => 'Players', 'icon' => 'fa-person-running', 'color' => 'green', 'table' => 'team_members'], // Fixed table name here
        ['title' => 'Cards', 'icon' => 'fa-square-exclamation', 'color' => 'red', 'table' => 'match_day_reports', 'condition' => 'card != ""'],
      ];

      // Loop through each stat and display card
      foreach ($stats as $stat) {
          $sql = isset($stat['condition'])
              ? "SELECT * FROM {$stat['table']} WHERE {$stat['condition']}"
              : "SELECT * FROM {$stat['table']}";
          $stmt = $connection->prepare($sql);
          $stmt->execute();
          $count = $stmt->rowCount();
      ?>
        <div class="col-md-6 col-lg-3">
          <div class="card shadow-sm border-start border-<?= $stat['color']; ?> border-4">
            <div class="card-body d-flex align-items-center">
              <div class="me-3">
                <i class="fa-solid <?= $stat['icon']; ?> fa-2x text-<?= $stat['color']; ?>"></i>
              </div>
              <div>
                <p class="card-category"><?= $stat['title']; ?></p>
                <h5 class="card-title"><?= $count; ?></h5>
              </div>
            </div>
            <div class="card-footer text-end">
              <i class="fa fa-trophy me-1"></i> RNPL
            </div>
          </div>
        </div>
      <?php } ?>
    </div>

    <!-- Matches List -->
    
    <div class="matches-grid" id="matchesContainer">
      <?php
      // Connect to DB
      $conn = new mysqli("localhost", "root", "", "fa_db");
      if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
      }

      $seasonStartDate = '2025-01-01';
      $today = new DateTime();
      $seasonStart = new DateTime($seasonStartDate);

      $weekNumber = floor($seasonStart->diff($today)->days / 7) + 1;
      $nextWeek = $weekNumber + 1;

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
              t1.team_id AS team1_id,
              t1.name AS team1_name,
              t1.logon AS team1_logo,
              t2.team_id AS team2_id,
              t2.name AS team2_name,
              t2.logon AS team2_logo
          FROM `match` m
          JOIN `team` t1 ON m.team1_id = t1.team_id
          JOIN `team` t2 ON m.team2_id = t2.team_id
          WHERE m.week IN (?, ?)
          ORDER BY m.match_date ASC
      ";

      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ii", $weekNumber, $nextWeek);
      $stmt->execute();
      $result = $stmt->get_result();

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
                          <img src="../Logo/<?= $team1Logo; ?>" alt="<?= $team1Name; ?>" width="40" height="40" style="object-fit: cover; border-radius: 50%;" />
                      </div>
                      <span class="team-name">
                          <?= $team1Name; ?>
                          <strong>
                              <?= $status === 'completed' ? "$team1Goal - $team2Goal" : 'vs'; ?>
                          </strong>
                          <?= $team2Name; ?>
                      </span>
                      <div class="team-logo">
                          <img src="../Logo/<?= $team2Logo; ?>" alt="<?= $team2Name; ?>" width="40" height="40" style="object-fit: cover; border-radius: 50%;" />
                      </div>
                  </div>
                  <div class="match-info">Week <?= $week; ?> Fixtures • <?= $date . ' ' . $time; ?></div>
                  <div class="match-status <?= $matchStatusClass; ?>"><?= ucfirst($status); ?></div>
              </div>
      <?php
          endwhile;
      else:
          echo "<p>No upcoming matches found for weeks $weekNumber and $nextWeek.</p>";
      endif;

      $stmt->close();
      $conn->close();
      ?>
    </div>

  </div>
</div>

<?php require 'footer.php'; ?>
