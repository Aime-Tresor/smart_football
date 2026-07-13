<?php
require __DIR__ . '/../vendor/autoload.php';

use App\ServiceFactory;

session_start();
require '../app/database.php';

// Check if team is logged in
if (!isset($_SESSION['Team_id'])) {
    header("Location: ../teams.php");
    exit();
}

$team_id = $_SESSION['Team_id'];
$team_name = $_SESSION['Team_Name'];

// Get all players with their card information
$sql = "SELECT 
            member_id,
            fname,
            lname,
            number,
            position,
            yellow,
            double_yellow,
            red,
            role_in_team
        FROM team_members 
        WHERE team = ? AND role_in_team = 'player'
        ORDER BY number ASC";

$stmt = $connection->prepare($sql);
$stmt->execute([$team_id]);
$players = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get card history from cards table
$cardHistorySql = "SELECT
                    c.card_id,
                    c.member_id,
                    c.card_type,
                    c.card_time,
                    c.card_reason_title,
                    c.card_reason_detail,
                    c.ai_summary,
                    c.ai_summary_status,
                    c.created_at,
                    m.match_date,
                    m.match_time,
                    m.season,
                    m.competition,
                    CASE
                        WHEN m.team1_id = ? THEN t2.name
                        WHEN m.team2_id = ? THEN t1.name
                        ELSE 'Unknown'
                    END as opponent_name
                   FROM cards c
                   LEFT JOIN `match` m ON c.match_id = m.id
                   LEFT JOIN team t1 ON m.team1_id = t1.team_id
                   LEFT JOIN team t2 ON m.team2_id = t2.team_id
                   WHERE c.deleted_at IS NULL AND c.member_id IN (
                       SELECT member_id FROM team_members WHERE team = ? AND role_in_team = 'player'
                   )
                   ORDER BY c.created_at DESC";

$cardHistoryStmt = $connection->prepare($cardHistorySql);
$cardHistoryStmt->execute([$team_id, $team_id, $team_id]);
$cardHistory = $cardHistoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Group card history by player
$playerCardHistory = [];
foreach ($cardHistory as $card) {
    $playerCardHistory[$card['member_id']][] = $card;
}

// Cards-by-season / cards-by-competition breakdown per player (Phase 3),
// derived from the same `cards` table as the totals above so they can
// never disagree.
$breakdownService = ServiceFactory::cardBreakdownService();
$playerCardBreakdown = [];
foreach ($players as $player) {
    $playerCardBreakdown[$player['member_id']] = $breakdownService->bySeason((int) $player['member_id']);
}

require 'header.php';
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Player Cards Overview</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb justify-content-end">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Player Cards</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Team Summary Cards -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card border-end">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium"><?= count($players) ?></h2>
                                </div>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Total Players</h6>
                            </div>
                            <div class="ms-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-muted"><i class="fa fa-users fa-2x"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card border-end">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-warning mb-1 font-weight-medium">
                                        <?= array_sum(array_column($players, 'yellow')) ?>
                                    </h2>
                                </div>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Yellow Cards</h6>
                            </div>
                            <div class="ms-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-warning"><i class="fa fa-square fa-2x"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card border-end">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-danger mb-1 font-weight-medium">
                                        <?= array_sum(array_column($players, 'red')) + array_sum(array_column($players, 'double_yellow')) ?>
                                    </h2>
                                </div>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Red Cards</h6>
                            </div>
                            <div class="ms-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-danger"><i class="fa fa-square fa-2x"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-info mb-1 font-weight-medium">
                                        <?= count(array_filter($players, function($p) { 
                                            return $p['yellow'] >= 5 || $p['double_yellow'] > 0 || $p['red'] > 0; 
                                        })) ?>
                                    </h2>
                                </div>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Suspended Players</h6>
                            </div>
                            <div class="ms-auto mt-md-3 mt-lg-0">
                                <span class="opacity-7 text-info"><i class="fa fa-ban fa-2x"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Players Cards Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Player Cards Status</h4>
                        <h6 class="card-subtitle">Overview of all player disciplinary records</h6>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Player Name</th>
                                        <th>Position</th>
                                        <th>Yellow Cards</th>
                                        <th>Red Cards</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($players as $player): 
                                        $totalRed = $player['red'] + $player['double_yellow'];
                                        $isSuspended = $player['yellow'] >= 5 || $totalRed > 0;
                                        $statusClass = $isSuspended ? 'danger' : 'success';
                                        $statusText = $isSuspended ? 'Suspended' : 'Available';
                                    ?>
                                    <tr>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($player['number']) ?></span></td>
                                        <td>
                                            <strong><?= htmlspecialchars($player['fname'] . ' ' . $player['lname']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($player['position']) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php for ($i = 0; $i < $player['yellow']; $i++): ?>
                                                    <span class="badge bg-warning me-1">🟨</span>
                                                <?php endfor; ?>
                                                <span class="ms-2"><?= $player['yellow'] ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php for ($i = 0; $i < $totalRed; $i++): ?>
                                                    <span class="badge bg-danger me-1">🟥</span>
                                                <?php endfor; ?>
                                                <span class="ms-2"><?= $totalRed ?></span>
                                                <?php if ($player['double_yellow'] > 0): ?>
                                                    <small class="text-muted ms-1">(2Y→R)</small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="showPlayerHistory(<?= $player['member_id'] ?>)">
                                                <i class="fa fa-history"></i> History
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Card History -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Recent Card History</h4>
                        <h6 class="card-subtitle">Latest disciplinary actions</h6>
                        
                        <?php if (empty($cardHistory)): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> No card history found for your team players.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Player</th>
                                            <th>Card Type</th>
                                            <th>Reason Title</th>
                                            <th>Match</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($cardHistory, 0, 10) as $card): 
                                            $player = array_filter($players, function($p) use ($card) {
                                                return $p['member_id'] == $card['member_id'];
                                            });
                                            $player = reset($player);
                                        ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($card['created_at'])) ?></td>
                                            <td>
                                                <?php if ($player): ?>
                                                    <strong>#<?= $player['number'] ?> <?= htmlspecialchars($player['fname'] . ' ' . $player['lname']) ?></strong>
                                                <?php else: ?>
                                                    Unknown Player
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $cardIcon = '';
                                                $cardClass = '';
                                                switch ($card['card_type']) {
                                                    case 'yellow':
                                                        $cardIcon = '🟨';
                                                        $cardClass = 'warning';
                                                        break;
                                                    case 'red':
                                                        $cardIcon = '🟥';
                                                        $cardClass = 'danger';
                                                        break;
                                                    case 'double_yellow':
                                                        $cardIcon = '🟨🟥';
                                                        $cardClass = 'danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge bg-<?= $cardClass ?>"><?= $cardIcon ?> <?= ucfirst(str_replace('_', ' ', $card['card_type'])) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($card['card_reason_title'] ?: '—') ?></td>
                                            <td>
                                                <?php if ($card['opponent_name']): ?>
                                                    vs <?= htmlspecialchars($card['opponent_name']) ?>
                                                    <br><small class="text-muted"><?= date('M d, Y', strtotime($card['match_date'])) ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">Match details unavailable</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= $card['card_time'] ? htmlspecialchars($card['card_time']) . "'" : '<span class="text-muted">Not recorded</span>' ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Player History Modal -->
<div class="modal fade" id="playerHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Player Card History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="playerHistoryContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function showPlayerHistory(playerId) {
    // Find player data
    const players = <?= json_encode($players) ?>;
    const cardHistory = <?= json_encode($playerCardHistory) ?>;
    const cardBreakdown = <?= json_encode($playerCardBreakdown) ?>;

    const player = players.find(p => p.member_id == playerId);
    const history = cardHistory[playerId] || [];
    const breakdown = cardBreakdown[playerId] || [];
    
    let content = `
        <div class="text-center mb-3">
            <h5>#${player.number} ${player.fname} ${player.lname}</h5>
            <p class="text-muted">${player.position}</p>
        </div>
        
        <div class="row mb-3">
            <div class="col-4 text-center">
                <div class="border rounded p-2">
                    <h4 class="text-warning">${player.yellow}</h4>
                    <small>Yellow Cards</small>
                </div>
            </div>
            <div class="col-4 text-center">
                <div class="border rounded p-2">
                    <h4 class="text-danger">${parseInt(player.red) + parseInt(player.double_yellow)}</h4>
                    <small>Red Cards</small>
                </div>
            </div>
            <div class="col-4 text-center">
                <div class="border rounded p-2">
                    <h4 class="text-info">${history.length}</h4>
                    <small>Total Incidents</small>
                </div>
            </div>
        </div>
    `;
    
    if (breakdown.length > 0) {
        content += '<h6>Cards by Season / Competition:</h6><table class="table table-sm table-bordered mb-3"><thead><tr><th>Season</th><th>Competition</th><th>Yellow</th><th>Red</th><th>Total</th></tr></thead><tbody>';
        breakdown.forEach(row => {
            content += `<tr><td>${row.season}</td><td>${row.competition || row.season}</td><td>${row.yellow}</td><td>${row.double_yellow + row.red}</td><td>${row.total}</td></tr>`;
        });
        content += '</tbody></table>';
    }

    if (history.length === 0) {
        content += '<div class="alert alert-info">No card history found for this player.</div>';
    } else {
        content += '<h6>Card History:</h6><div class="list-group">';
        history.forEach(card => {
            const cardIcon = card.card_type === 'yellow' ? '🟨' :
                           card.card_type === 'red' ? '🟥' : '🟨🟥';
            const cardName = card.card_type.replace('_', ' ');
            const matchInfo = card.opponent_name ? `vs ${card.opponent_name}` : 'Match details unavailable';
            const cardTime = card.card_time ? `${card.card_time}'` : 'Time not recorded';
            // AI-generated summary is intentionally not shown to teams - it's
            // an internal aid for admin/committee review only.
            const reasonTitle = card.card_reason_title ? `<div><strong>Reason:</strong> ${card.card_reason_title}</div>` : '';

            content += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-${card.card_type === 'yellow' ? 'warning' : 'danger'} me-2">${cardIcon}</span>
                            <strong>${cardName.charAt(0).toUpperCase() + cardName.slice(1)}</strong>
                            <br><small class="text-muted">${matchInfo} - ${cardTime}</small>
                            ${reasonTitle}
                        </div>
                        <small class="text-muted">${new Date(card.created_at).toLocaleDateString()}</small>
                    </div>
                </div>
            `;
        });
        content += '</div>';
    }
    
    document.getElementById('playerHistoryContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('playerHistoryModal')).show();
}
</script>

<?php require 'footer.php'; ?>
