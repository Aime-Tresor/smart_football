<?php
session_start();

if (!isset($_SESSION['referee_id'])) {
    header('Location: ../referee.php');
    exit;
}

$refereeId = $_SESSION['referee_id'];

// Connect to DB
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Get referee info
$stmt = $conn->prepare("SELECT fname, lname FROM referee WHERE referee_id = ?");
$stmt->bind_param("i", $refereeId);
$stmt->execute();
$refereeInfo = $stmt->get_result()->fetch_assoc();
$refereeName = $refereeInfo ? $refereeInfo['fname'] . ' ' . $refereeInfo['lname'] : 'Unknown';
$initials = strtoupper(substr($refereeInfo['fname'] ?? '', 0, 1) . substr($refereeInfo['lname'] ?? '', 0, 1));
$stmt->close();

// Get matches assigned to the referee
$sql = "
    SELECT 
        m.id,
        m.match_date,
        m.match_time,
        m.status,
        m.stadium,
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
        END, m.match_date ASC, m.match_time ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $refereeId, $refereeId);
$stmt->execute();
$matches = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Referee Matches</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <script src="assets/js/nav.js" defer></script>
    <script src="assets/js/dropdown.js" defer></script>

    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 15px 25px;
            border-bottom: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .page-title { font-size: 20px; font-weight: bold; color: #333; }

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

        .main-content {
            padding: 30px;
        }

        .match-card {
            background: #fff;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .match-teams {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .team-logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .team-name strong {
            margin: 0 8px;
            font-weight: bold;
            font-size: 1.1em;
            color: #222;
        }

        .match-info {
            text-align: center;
            color: #555;
        }

        .match-status {
            font-size: 0.85rem;
            font-weight: bold;
            padding: 6px 10px;
            border-radius: 5px;
            text-transform: capitalize;
            color: #fff;
        }

        .status-live { background-color: #e53935; }
        .status-upcoming { background-color: #fbc02d; color: #333; }
        .status-completed { background-color: #4caf50; }
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
    <h2 style="color:blue;">Assigned Matches</h2>

    <?php if ($matches->num_rows > 0): ?>
        <?php while ($match = $matches->fetch_assoc()):
            $team1 = htmlspecialchars($match['team1_name']);
            $team2 = htmlspecialchars($match['team2_name']);
            $logo1 = htmlspecialchars($match['team1_logo']);
            $logo2 = htmlspecialchars($match['team2_logo']);
            $goals = ($match['status'] === 'completed') ? $match['team1_goal'] . ' - ' . $match['team2_goal'] : 'vs';
            $date = date("D, M j", strtotime($match['match_date']));
            $time = date("H:i", strtotime($match['match_time']));
            $statusClass = 'status-' . strtolower($match['status']);
        ?>
            <div class="match-card">
                <div class="match-teams">
                    <div class="team-logo"><img src="../Logo/<?= $logo1 ?>" alt="<?= $team1 ?>"></div>
                    <div class="team-name"><?= $team1 ?> <strong><?= $goals ?></strong> <?= $team2 ?></div>
                    <div class="team-logo"><img src="../Logo/<?= $logo2 ?>" alt="<?= $team2 ?>"></div>
                </div>
                <div class="match-info"><?= $date ?> <?= $time ?><br><small><?= $match['stadium'] ?></small></div>
                <div class="match-status <?= $statusClass ?>"><?= ucfirst($match['status']) ?></div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No matches assigned to you yet.</p>
    <?php endif; ?>

</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
