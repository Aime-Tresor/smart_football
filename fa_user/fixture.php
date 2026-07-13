<?php require_once 'header.php'; ?>
<style>
.badge-live {
    background-color: #dc3545 !important;
    color: white !important;
    animation: pulse 2s infinite;
}

.badge-upcoming {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.badge-completed {
    background-color: #28a745 !important;
    color: white !important;
}

@keyframes pulse {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(1.05); }
    100% { opacity: 1; transform: scale(1); }
}

.match-actions {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
    justify-content: center;
}

.match-score {
    font-weight: bold;
    color: #007bff;
    font-size: 1.1em;
}

.table-primary {
    background-color: #b3d7ff !important;
}
</style>
<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->

        <div class="row">
            <!-- Matches List Column -->
            <div class="col-lg-8 d-flex align-items-stretch">
                <div class="card w-100">
                    <?php
                    // Fetch matches with teams info
                    $sql = "SELECT m.*, 
                                t1.name AS team1_name, t1.logon AS team1_logo, t1.stadium AS team1_stadium,
                                t2.name AS team2_name, t2.logon AS team2_logo, t2.stadium AS team2_stadium
                            FROM `match` m
                            JOIN `team` t1 ON m.team1_id = t1.team_id
                            JOIN `team` t2 ON m.team2_id = t2.team_id
                            WHERE m.status IN ('live', 'upcoming', 'completed')
                            ORDER BY 
                                CASE 
                                    WHEN m.status = 'live' THEN 1
                                    WHEN m.status = 'upcoming' THEN 2
                                    WHEN m.status = 'completed' THEN 3
                                    ELSE 4
                                END,
                                m.match_date,
                                m.match_time";

                    $stmt = $connection->prepare($sql);
                    $stmt->execute();
                    $matches = $stmt->fetchAll(PDO::FETCH_OBJ);

                    // Group matches by status
                    $groupedMatches = ['live' => [], 'upcoming' => [], 'completed' => []];
                    foreach ($matches as $match) {
                        $groupedMatches[$match->status][] = $match;
                    }
                    ?>

                    <div class="card-body">
                        <h5 class="card-title">All Matches</h5>

                        <?php
                        // Display success/error messages
                        if (isset($_SESSION['success'])) {
                            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                            echo htmlspecialchars($_SESSION['success']);
                            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                            echo '<span aria-hidden="true">&times;</span>';
                            echo '</button></div>';
                            unset($_SESSION['success']);
                        }

                        if (isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                            echo htmlspecialchars($_SESSION['error']);
                            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                            echo '<span aria-hidden="true">&times;</span>';
                            echo '</button></div>';
                            unset($_SESSION['error']);
                        }
                        ?>

                        <div class="table-responsive no-wrap">
                            <table class="table vm no-th-brd pro-of-month">
                                <thead>
                                    <tr>
                                        <th>Team 1</th>
                                        <th></th>
                                        <th>Match Info</th>
                                        <th></th>
                                        <th>Team 2</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $statusTitles = [
                                        'live' => 'Live Matches',
                                        'upcoming' => 'Upcoming Matches',
                                        'completed' => 'Completed Matches'
                                    ];

                                    $empty = true;

                                    foreach (['live', 'upcoming', 'completed'] as $status):
                                        if (!empty($groupedMatches[$status])):
                                            $empty = false;
                                            echo '<tr><td colspan="5" class="table-primary text-center fw-bold">'. $statusTitles[$status] .'</td></tr>';
                                            foreach ($groupedMatches[$status] as $match): ?>
                                                <tr>
                                                    <td style="text-align: right; vertical-align: middle;">
                                                        <img src="../Logo/<?= htmlspecialchars($match->team1_logo); ?>" width="50" height="50" class="rounded-circle"><br>
                                                        <strong><?= htmlspecialchars($match->team1_name); ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($match->team1_stadium); ?></small>
                                                    </td>
                                                    <td></td>
                                                                    <td style="text-align: center; vertical-align: middle;">
                                                        <div><strong>Date:</strong> <?= htmlspecialchars($match->match_date); ?> at <?= htmlspecialchars($match->match_time); ?></div>
                                                        <div><strong>Stadium:</strong> <?= htmlspecialchars($match->stadium); ?></div>
                                                        <div><strong>Status:</strong>
                                                            <span class="badge badge-<?= $status ?>">
                                                                <?= ucfirst($status) ?>
                                                            </span>
                                                        </div>
                                                        <?php if ($status === 'live' || $status === 'completed'): ?>
                                                            <div class="match-score"><strong>Score:</strong> <?= $match->team1_goal ?? 0 ?> - <?= $match->team2_goal ?? 0 ?></div>
                                                        <?php endif; ?>
                                                        <div class="mt-2 match-actions">
                                                            <?php if ($status !== 'completed'): ?>
                                                                <a href="?set=<?= $match->id ?>" class="btn btn-sm btn-info">Set Referee</a>
                                                            <?php endif; ?>
                                                            <button class="btn btn-sm btn-primary" onclick="showStatusModal(<?= $match->id ?>, '<?= $status ?>', '<?= htmlspecialchars($match->team1_name) ?>', '<?= htmlspecialchars($match->team2_name) ?>')">
                                                                Update Status
                                                            </button>
                                                            <?php if ($status === 'completed'): ?>
                                                                <form method="post" action="controls/reopen_match.php" class="d-inline"
                                                                      onsubmit="return confirm('Reopen this match? Referees will be able to record events again.');">
                                                                    <input type="hidden" name="match_id" value="<?= $match->id ?>">
                                                                    <button type="submit" class="btn btn-sm btn-warning">Reopen</button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td></td>
                                                    <td style="text-align: left; vertical-align: middle;">
                                                        <img src="../Logo/<?= htmlspecialchars($match->team2_logo); ?>" width="50" height="50" class="rounded-circle"><br>
                                                        <strong><?= htmlspecialchars($match->team2_name); ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($match->stadium); ?></small>
                                                    </td>
                                                </tr>
                                            <?php endforeach;
                                        endif;
                                    endforeach;

                                    if ($empty):
                                        echo '<tr><td colspan="5" class="text-center text-muted">No matches found.</td></tr>';
                                    endif;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Set Referee Form or Create Match -->
            <div class="col-lg-4 col-md-12">
                <div class="card card-body mailbox">
                    <?php
                    if (isset($_GET['set'])) {
                        $id = (int)$_GET['set'];

                        // Check if referees are assigned for this match
                        $sql = 'SELECT * FROM `weekly_fixtures` WHERE match_id = ?';
                        $statement = $connection->prepare($sql);
                        $statement->execute([$id]);
                        $rowCount = $statement->rowCount();

                        // Fetch referees for selects
                        $refereesListStmt = $connection->prepare('SELECT * FROM referee');
                        $refereesListStmt->execute();
                        $refereesList = $refereesListStmt->fetchAll(PDO::FETCH_OBJ);

                        if ($rowCount > 0) {
                            $assignments = $statement->fetch(PDO::FETCH_OBJ);
                            ?>
                            <form method="post" action="controls/reset_ref.php">
                                <h5 class="card-title mb-5">MatchDay Referees</h5>
                                <input type="hidden" name="match_id" value="<?= $id ?>">

                                <div class="form-group mb-3">
                                    <label>Match Referee</label>
                                    <?php
                                    foreach ($refereesList as $ref) {
                                        if ($ref->referee_id == $assignments->referee) {
                                            echo '<input type="hidden" name="select1" value="'.htmlspecialchars($ref->referee_id).'">';
                                            echo '<input type="text" class="form-control" readonly value="'.htmlspecialchars($ref->fname . ' ' . $ref->lname).'">';
                                        }
                                    }
                                    ?>
                                </div>



                                <div class="form-group mb-3">
                                    <label>Official Referee</label>
                                    <?php
                                    foreach ($refereesList as $ref) {
                                        if ($ref->referee_id == $assignments->official) {
                                            echo '<input type="hidden" name="select4" value="'.htmlspecialchars($ref->referee_id).'">';
                                            echo '<input type="text" class="form-control" readonly value="'.htmlspecialchars($ref->fname . ' ' . $ref->lname).'">';
                                        }
                                    }
                                    ?>
                                </div>

                                <button type="submit" name="submit" class="btn btn-danger">Reset Referees</button>
                            </form>
                        <?php
                        } else {
                            // Show the set referees form (dropdowns)
                            ?>
                            <form method="post" action="controls/setReferee.php">
                                <h5 class="card-title mb-5">MatchDay Referees</h5>
                                <input type="hidden" name="match_id" value="<?= $id ?>">

                                <div class="form-group mb-3">
                                    <label>Match Referee</label>
                                    <select name="select1" class="form-control" required>
                                        <option disabled selected>Select Match Referee</option>
                                        <?php foreach ($refereesList as $ref): ?>
                                            <option value="<?= $ref->referee_id ?>">
                                                <?= htmlspecialchars($ref->fname . ' ' . $ref->lname) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>



                                <div class="form-group mb-3">
                                    <label>Official Referee</label>
                                    <select name="select4" class="form-control" required>
                                        <option disabled selected>Select Official Referee</option>
                                        <?php foreach ($refereesList as $ref): ?>
                                            <option value="<?= $ref->referee_id ?>">
                                                <?= htmlspecialchars($ref->fname . ' ' . $ref->lname) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <button type="submit" name="submit" class="btn btn-success">Set Referees</button>
                            </form>
                            <?php
                        }
                    } else {
                        // No set param -> show create match form
                        ?>
                        <center class="mt-4">
                            <form class="form-horizontal form-material mx-2" method="post" action="controls/setMatch.php">
                                <h5 class="card-title mb-5">Match Teams</h5>

                                <?php
                                if (isset($_SESSION['error'])) {
                                    echo '<div class="alert alert-danger">'.htmlspecialchars($_SESSION['error']).'</div>';
                                    unset($_SESSION['error']);
                                }

                                if (isset($_SESSION['success'])) {
                                    echo '<div class="alert alert-success">'.htmlspecialchars($_SESSION['success']).'</div>';
                                    unset($_SESSION['success']);
                                }
                                ?>

                                <!-- Team 1 Dropdown -->
                                <div class="form-group mb-3">
                                    <select id="team1" name="team1" class="form-control form-control-line" required>
                                        <option selected disabled>Select Team 1</option>
                                        <?php
                                            $sql = 'SELECT * FROM team';
                                            $statement = $connection->prepare($sql);
                                            $statement->execute();
                                            $teams = $statement->fetchAll(PDO::FETCH_OBJ);
                                            foreach($teams as $team):
                                        ?>
                                            <option value="<?= $team->team_id; ?>" data-stadium="<?= htmlspecialchars($team->stadium); ?>">
                                                <?= htmlspecialchars($team->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Team 2 Dropdown -->
                                <div class="form-group mb-3">
                                    <select id="team2" name="team2" class="form-control form-control-line" required>
                                        <option selected disabled>Select Team 2</option>
                                        <?php foreach($teams as $team): ?>
                                            <option value="<?= $team->team_id; ?>">
                                                <?= htmlspecialchars($team->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Stadium Auto-filled -->
                                <div class="form-group mb-3">
                                    <input type="text" id="stadium" name="stadium" class="form-control form-control-line" readonly placeholder="Stadium will appear here">
                                </div>

                                <!-- Match Day -->
                                <div class="form-group mb-3">
                                    <input type="date" name="match_day" id="match_day" class="form-control" required>
                                </div>

                                <!-- Match Time -->
                                <div class="form-group mb-3">
                                    <input type="time" name="match_time" id="match_time" class="form-control" required>
                                </div>

                                <!-- Submit Button -->
                                <div class="form-group">
                                    <button name="submit" class="btn btn-success">Create Match</button>
                                </div>
                            </form>
                        </center>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

<script>
    const team1 = document.getElementById('team1');
    const team2 = document.getElementById('team2');
    const stadium = document.getElementById('stadium');

    // Store original Team 2 options
    const team2OriginalOptions = [...team2.options];

    // Auto-fill stadium based on selected Team 1
    function updateStadium() {
        const selectedOption = team1.options[team1.selectedIndex];
        const stadiumName = selectedOption ? selectedOption.getAttribute('data-stadium') : '';
        stadium.value = stadiumName || '';
    }

    // Filter Team 2 to exclude selected Team 1
    function filterTeam2Options(selectedTeam1Id) {
        team2.innerHTML = ''; // Clear Team 2 options
        team2OriginalOptions.forEach(option => {
            if (option.value !== selectedTeam1Id) {
                team2.appendChild(option.cloneNode(true));
            }
        });
    }

    // Event: When Team 1 changes
    team1.addEventListener('change', () => {
        const selectedTeam1Id = team1.value;
        updateStadium();
        filterTeam2Options(selectedTeam1Id);
    });
</script>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Match Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="statusUpdateForm" method="post" action="controls/update_match_status.php">
                <div class="modal-body">
                    <input type="hidden" id="matchId" name="match_id">
                    <div class="form-group">
                        <label for="matchInfo"><strong>Match:</strong></label>
                        <p id="matchInfo" class="form-control-static"></p>
                    </div>
                    <div class="form-group">
                        <label for="currentStatus"><strong>Current Status:</strong></label>
                        <p id="currentStatus" class="form-control-static"></p>
                    </div>
                    <div class="form-group">
                        <label for="newStatus"><strong>New Status:</strong></label>
                        <select id="newStatus" name="new_status" class="form-control" required>
                            <option value="">Select Status</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="live">Live</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="form-group" id="scoreSection" style="display: none;">
                        <label><strong>Final Score (for completed matches):</strong></label>
                        <div class="row">
                            <div class="col-md-5">
                                <input type="number" id="team1Score" name="team1_score" class="form-control" min="0" placeholder="Team 1 Score">
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="form-control-static">-</span>
                            </div>
                            <div class="col-md-5">
                                <input type="number" id="team2Score" name="team2_score" class="form-control" min="0" placeholder="Team 2 Score">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showStatusModal(matchId, currentStatus, team1Name, team2Name) {
    document.getElementById('matchId').value = matchId;
    document.getElementById('matchInfo').textContent = team1Name + ' vs ' + team2Name;
    document.getElementById('currentStatus').textContent = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1);

    // Set current status as selected
    const statusSelect = document.getElementById('newStatus');
    statusSelect.value = currentStatus;

    // Show/hide score section based on status
    toggleScoreSection(currentStatus);

    // Show modal
    $('#statusModal').modal('show');
}

function toggleScoreSection(status) {
    const scoreSection = document.getElementById('scoreSection');
    if (status === 'completed') {
        scoreSection.style.display = 'block';
        document.getElementById('team1Score').required = true;
        document.getElementById('team2Score').required = true;
    } else {
        scoreSection.style.display = 'none';
        document.getElementById('team1Score').required = false;
        document.getElementById('team2Score').required = false;
    }
}

// Listen for status change to show/hide score section
document.getElementById('newStatus').addEventListener('change', function() {
    toggleScoreSection(this.value);
});
</script>

    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
<?php require 'footer.php'; ?>
