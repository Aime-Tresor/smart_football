<?php
require __DIR__ . '/../vendor/autoload.php';

use App\ServiceFactory;

require_once 'header.php';

$season = $_GET['season'] ?? null;
$standings = ServiceFactory::standingsService()->compute($season ?: null);

$seasonsStmt = $connection->query("SELECT DISTINCT season FROM `match` ORDER BY season DESC");
$seasons = $seasonsStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">League Table</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <form method="get" class="d-inline-flex align-items-center justify-content-end">
                    <label class="me-2 mb-0">Season:</label>
                    <select name="season" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">All seasons</option>
                        <?php foreach ($seasons as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" <?= $season === $s ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($standings)): ?>
                            <div class="alert alert-info">No completed matches yet - standings recompute automatically as matches finish.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Team</th>
                                            <th>Played</th>
                                            <th>Won</th>
                                            <th>Drawn</th>
                                            <th>Lost</th>
                                            <th>GF</th>
                                            <th>GA</th>
                                            <th>GD</th>
                                            <th>Points</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($standings as $i => $row): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                                <td><?= $row['played'] ?></td>
                                                <td><?= $row['won'] ?></td>
                                                <td><?= $row['drawn'] ?></td>
                                                <td><?= $row['lost'] ?></td>
                                                <td><?= $row['goals_for'] ?></td>
                                                <td><?= $row['goals_against'] ?></td>
                                                <td><?= $row['goal_difference'] ?></td>
                                                <td><strong><?= $row['points'] ?></strong></td>
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

<?php require 'footer.php'; ?>
