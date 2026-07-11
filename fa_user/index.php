<?php 
require_once 'header.php'; ?>

<style>
 
  /* existing styles here */

  /* Appeals Section Styling */
  .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
  }

  .card-header h4 {
    color: white !important;
  }

  /* Pending Appeals heading */
  .card-body h5 {
    color: #333 !important;
    font-weight: 600;
  }

  /* No pending message */
  .card-body .text-muted {
    color: #666 !important;
  }

  /* Stats cards */
  .card.bg-warning {
    background: #ffc107 !important;
  }

  .card.bg-success {
    background: #28a745 !important;
  }

  .card.bg-danger {
    background: #dc3545 !important;
  }

  .card.bg-primary {
    background: #667eea !important;
  }

  /* Table styling */
  .table-hover tbody tr:hover {
    background-color: #f0f0f0 !important;
  }

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
                  <?php
          endwhile;
      endif;
      
      if (empty($result) || mysqli_num_rows($result) == 0) {
      ?>
      <!-- APPEALS SECTION FOR ADMIN -->
      <div class="row mt-5">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header bg-primary">
              <h4 class="text-white mb-0">
                <i class="fas fa-gavel me-2"></i>Disciplinary Appeals
              </h4>
            </div>
            <div class="card-body">
              <?php
              // Get appeal statistics
              $stmt = $connection->prepare("SELECT COUNT(*) as count FROM appeal_cases WHERE status = 'pending'");
              $stmt->execute();
              $pending = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

              $stmt = $connection->prepare("SELECT COUNT(*) as count FROM appeal_cases WHERE status = 'approved'");
              $stmt->execute();
              $approved = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

              $stmt = $connection->prepare("SELECT COUNT(*) as count FROM appeal_cases WHERE status = 'rejected'");
              $stmt->execute();
              $rejected = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
              ?>

              <!-- Stats Row -->
              <div class="row mb-4">
                <div class="col-md-3">
                  <div class="card bg-warning text-white text-center p-3">
                    <h5><?php echo $pending; ?></h5>
                    <p class="mb-0">Pending Appeals</p>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card bg-success text-white text-center p-3">
                    <h5><?php echo $approved; ?></h5>
                    <p class="mb-0">Approved</p>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card bg-danger text-white text-center p-3">
                    <h5><?php echo $rejected; ?></h5>
                    <p class="mb-0">Rejected</p>
                  </div>
                </div>
                <div class="col-md-3">
                  <a href="discipline_committee_dashboard.php" class="btn btn-primary w-100 h-100">
                    <i class="fas fa-gavel"></i><br>
                    <strong>Full Dashboard</strong>
                  </a>
                </div>
              </div>

              <!-- Recent Pending Appeals -->
              <h5 >Pending Appeals</h5>
              <?php
              $stmt = $connection->prepare("
                SELECT ac.*, t.name as team_name, dc.offence_description
                FROM appeal_cases ac
                JOIN team t ON ac.team_id = t.team_id
                LEFT JOIN ai_discipline_cases dc ON ac.discipline_case_id = dc.case_id
                WHERE ac.status = 'pending'
                ORDER BY ac.appeal_date DESC
                LIMIT 5
              ");
              $stmt->execute();
              $pending_appeals = $stmt->fetchAll();
              ?>
</div>
      </div>
      <?php } ?>
              <?php if (empty($pending_appeals)): ?>
                <p>No pending appeals at this time.</p>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-sm table-hover">
                    <thead class="table-light">
                      <tr>
                        <th>Team</th>
                        <th>Offense</th>
                        <th>Submitted</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($pending_appeals as $appeal): ?>
                        <tr>
                          <td><strong><?php echo htmlspecialchars($appeal['team_name']); ?></strong></td>
                          <td><?php echo htmlspecialchars(substr($appeal['offence_description'] ?? 'N/A', 0, 40)); ?></td>
                          <td><?php echo date('M d, Y', strtotime($appeal['appeal_date'])); ?></td>
                          <td>
                            <a href="discipline_committee_dashboard.php" class="btn btn-sm btn-primary">
                              Review
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>

              <!-- Quick Links -->
              <div class="mt-3">
                <a href="manage_committee.php" class="btn btn-info btn-sm me-2">
                  <i class="fas fa-users-cog"></i> Manage Committee
                </a>
                <a href="discipline_committee_dashboard.php" class="btn btn-primary btn-sm">
                  <i class="fas fa-gavel"></i> View All Appeals
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<?php require 'footer.php'; ?>
