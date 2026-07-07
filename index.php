<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Smart Football - Live Updates</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    :root {
      --primary-color: #198754; /* Bootstrap green */
      --secondary-color: #0d6efd; /* Bootstrap blue */
      --danger-color: #dc3545;
      --dark-text: #212529;
      --light-text: #f8f9fa;
      --white: #ffffff;
      --gray-text: #6c757d;
      --border-light: #dee2e6;
      --red-status: #dc3545;
      --orange-status: #fd7e14;
      --green-status: #198754;
    }

    body {
      margin: 0;
      font-family: 'Roboto', sans-serif;
      background: var(--light-text);
      color: var(--dark-text);
      line-height: 1.6;
      overflow-x: hidden;
    }

    .navbar {
      background: var(--white);
      padding: 15px 30px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      position: sticky;
      top: 0;
      z-index: 1000;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .navbar .logo {
      font-family: 'Montserrat', sans-serif;
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary-color);
      text-decoration: none;
      display: flex;
      align-items: center;
    }

    .navbar .logo i {
      margin-right: 10px;
      color: var(--secondary-color);
    }

    .nav-links {
      display: flex;
      gap: 30px;
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .nav-links a {
      color: var(--dark-text);
      text-decoration: none;
      font-weight: 500;
      position: relative;
    }

    .nav-links a::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      background-color: var(--primary-color);
      transition: width 0.3s ease;
    }

    .nav-links a:hover,
    .nav-links a.active {
      color: var(--primary-color);
    }

    .nav-links a:hover::after,
    .nav-links a.active::after {
      width: 100%;
    }

    .login-btn {
      background: var(--primary-color);
      color: white;
      padding: 8px 20px;
      border-radius: 20px;
      font-weight: 600;
      text-decoration: none;
      box-shadow: 0 3px 10px rgba(25, 135, 84, 0.3);
      transition: all 0.3s ease;
    }


    header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: var(--white);
      padding: 70px 30px;
      text-align: center;
      border-bottom-left-radius: 50px;
      border-bottom-right-radius: 50px;
      margin-bottom: 40px;
      box-shadow: 0 6px 25px rgba(0,0,0,0.2);
    }

    header h1 {
      font-family: 'Montserrat', sans-serif;
      font-size: 3rem;
      margin-bottom: 15px;
    }

    header p {
      font-size: 1.1rem;
      max-width: 700px;
      margin: 0 auto;
    }

    .main-content {
      padding: 0 20px 50px;
    }

    .matches-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
    }

    @media (min-width: 992px) {
      .matches-grid {
        grid-template-columns: repeat(4, 1fr);
      }
    }

    .match-card {
      background: var(--white);
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      border-top: 4px solid var(--primary-color);
      transition: transform 0.2s;
    }

    .match-card:hover {
      transform: scale(1.02);
    }

    .match-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      border-bottom: 1px solid var(--border-light);
      padding-bottom: 10px;
    }

    .league {
      color: var(--primary-color);
      font-weight: bold;
      font-size: 0.9rem;
      text-transform: uppercase;
    }

    .time {
      color: var(--gray-text);
      font-size: 0.85rem;
    }

    .match-status {
      padding: 5px 12px;
      border-radius: 25px;
      font-size: 0.75rem;
      font-weight: bold;
      text-transform: uppercase;
      color: var(--white);
    }

    .status-live { background-color: var(--red-status); }
    .status-upcoming { background-color: var(--orange-status); }
    .status-completed { background-color: var(--green-status); }

    .team-display {
      text-align: center;
    }

    .team-logo img {
      width: 60px;
      height: 60px;
      object-fit: contain;
      border-radius: 50%;
      background-color: var(--white);
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .team-name {
      margin-top: 10px;
      font-weight: bold;
      font-size: 0.95rem;
    }

    .score {
      font-size: 1.8rem;
      font-weight: bold;
      margin: 0 15px;
    }

    .vs-text {
      background: var(--secondary-color);
      color: var(--white);
      padding: 8px 18px;
      border-radius: 25px;
      font-weight: bold;
    }

    .match-details {
      text-align: center;
      margin-top: 15px;
      color: var(--gray-text);
      font-size: 0.9rem;
    }

    .footer {
      background: var(--dark-text);
      color: var(--light-text);
      padding: 40px 20px;
      text-align: center;
    }

    .footer a {
      color: var(--light-text);
      text-decoration: none;
    }

    .footer a:hover {
      color: var(--primary-color);
    }

    /* Clickable match styles */
    .clickable-match {
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .clickable-match:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .click-hint {
      margin-top: 8px;
      font-size: 0.8rem;
      color: var(--primary-color);
      font-weight: 500;
    }

    /* Match Details Modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 2000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.7);
      backdrop-filter: blur(5px);
    }

    .modal-content {
      background-color: var(--white);
      margin: 2% auto;
      padding: 0;
      border-radius: 20px;
      width: 90%;
      max-width: 900px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .modal-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: var(--white);
      padding: 25px 30px;
      border-radius: 20px 20px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h2 {
      margin: 0;
      font-family: 'Montserrat', sans-serif;
      font-size: 1.8rem;
    }

    .close {
      color: var(--white);
      font-size: 35px;
      font-weight: bold;
      cursor: pointer;
      line-height: 1;
      opacity: 0.8;
      transition: opacity 0.3s ease;
    }

    .close:hover {
      opacity: 1;
    }

    .modal-body {
      padding: 30px;
    }

    .match-summary {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding: 20px;
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      border-radius: 15px;
    }

    .team-info {
      text-align: center;
      flex: 1;
    }

    .team-info img {
      width: 80px;
      height: 80px;
      object-fit: contain;
      border-radius: 50%;
      margin-bottom: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .team-info h3 {
      margin: 0;
      font-size: 1.2rem;
      color: var(--dark-text);
    }

    .final-score {
      text-align: center;
      flex: 0 0 120px;
    }

    .final-score .score-display {
      font-size: 3rem;
      font-weight: bold;
      color: var(--primary-color);
      margin: 10px 0;
    }

    .final-score .match-info {
      font-size: 0.9rem;
      color: var(--gray-text);
    }

    .details-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-top: 30px;
    }

    .detail-section {
      background: var(--white);
      border: 2px solid var(--border-light);
      border-radius: 15px;
      padding: 20px;
    }

    .detail-section h3 {
      margin: 0 0 20px 0;
      color: var(--primary-color);
      font-size: 1.3rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .cards-list, .goals-list, .officials-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .card-item, .goal-item, .official-item {
      display: flex;
      align-items: flex-start;
      padding: 12px 0;
      border-bottom: 1px solid var(--border-light);
    }

    .card-item:last-child, .goal-item:last-child, .official-item:last-child {
      border-bottom: none;
    }

    .goal-icon {
      margin-right: 12px;
      margin-top: 2px;
    }

    .goal-info {
      flex: 1;
    }

    .goal-details {
      margin-top: 5px;
      font-size: 0.9rem;
    }

    .goal-time {
      background: var(--primary-color);
      color: white;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: bold;
      margin-right: 8px;
    }

    .goal-type {
      color: var(--secondary-color);
      font-weight: 600;
      font-size: 0.85rem;
    }

    .goal-description {
      margin-top: 3px;
      font-size: 0.8rem;
      color: var(--gray-text);
      font-style: italic;
    }

    .goals-section {
      grid-column: 1 / -1; /* Span full width */
    }

    .goals-container {
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    .team-goals-section {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 15px;
      border-left: 4px solid var(--primary-color);
    }

    .team-goals-header {
      margin: 0 0 15px 0;
      color: var(--primary-color);
      font-size: 1.1rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .team-goals-header::before {
      content: '⚽';
      font-size: 1rem;
    }

    .team-goals-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .team-goals-list .goal-item {
      background: white;
      margin-bottom: 10px;
      padding: 12px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      border-bottom: none;
    }

    .team-goals-list .goal-item:last-child {
      margin-bottom: 0;
    }

    .cards-section {
      grid-column: 1 / -1; /* Span full width */
    }

    .cards-container {
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    .team-cards-section {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 15px;
      border-left: 4px solid #ffc107;
    }

    .team-cards-header {
      margin: 0 0 15px 0;
      color: #ffc107;
      font-size: 1.1rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .team-cards-header::before {
      content: '🟨';
      font-size: 1rem;
    }

    .team-cards-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .team-cards-list .card-item {
      background: white;
      margin-bottom: 10px;
      padding: 12px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      border-bottom: none;
    }

    .team-cards-list .card-item:last-child {
      margin-bottom: 0;
    }

    .card-time {
      font-size: 0.8rem;
      color: var(--gray-text);
      margin-top: 2px;
    }

    .card-icon {
      width: 20px;
      height: 20px;
      border-radius: 3px;
      margin-right: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: bold;
    }

    .yellow-card {
      background-color: #ffc107;
      color: #000;
    }

    .red-card {
      background-color: #dc3545;
      color: #fff;
    }

    .player-info {
      flex: 1;
    }

    .player-name {
      font-weight: 600;
      color: var(--dark-text);
    }

    .team-badge {
      font-size: 0.8rem;
      color: var(--gray-text);
    }

    .no-data {
      text-align: center;
      color: var(--gray-text);
      font-style: italic;
      padding: 20px;
    }

    .loading {
      text-align: center;
      padding: 40px;
      color: var(--gray-text);
    }

    .loading i {
      font-size: 2rem;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    @keyframes pulse {
      0% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.7; transform: scale(1.05); }
      100% { opacity: 1; transform: scale(1); }
    }

    @media (max-width: 768px) {
      .modal-content {
        width: 95%;
        margin: 5% auto;
      }

      .match-summary {
        flex-direction: column;
        gap: 20px;
      }

      .details-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .goals-container {
        gap: 15px;
      }

      .team-goals-section {
        padding: 12px;
      }

      .cards-container {
        gap: 15px;
      }

      .team-cards-section {
        padding: 12px;
      }

      .modal-header {
        padding: 20px;
      }

      .modal-body {
        padding: 20px;
      }
    }
  </style>
</head>
<body>

  <nav class="navbar">
    <a href="index.php" class="logo"><i class="fas fa-futbol"></i> FDMRS platform-AI-Based </a>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="logrole.php" class="login-btn">Login</a></li>
    </ul>
  </nav>

  <header>
    <h1>Catch Every Kick Live!</h1>
    <p>Get real-time updates and insights from Rwanda Primus National League and more.</p>
  </header>

  <div class="main-content">
    <div class="matches-grid" id="matchesContainer">
      <?php
      $conn = new mysqli("localhost", "root", "", "fa_db");
      if ($conn->connect_error) {
          die("<p style='text-align: center;'>Database error. Please try again later.</p>");
      }

      $sql = "
          SELECT m.*, t1.name AS team1_name, t2.name AS team2_name, 
                 t1.logon AS team1_logo, t2.logon AS team2_logo
          FROM `match` m
          JOIN `team` t1 ON m.team1_id = t1.team_id
          JOIN `team` t2 ON m.team2_id = t2.team_id
          ORDER BY 
              CASE 
                  WHEN m.status = 'live' THEN 1
                  WHEN m.status = 'upcoming' THEN 2
                  WHEN m.status = 'completed' THEN 3
                  ELSE 4
              END, m.match_date, m.match_time";

      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
          $matchDateTime = new DateTime($row['match_date'] . ' ' . $row['match_time']);
          $statusClass = $row['status'] === 'live' ? 'status-live' : ($row['status'] === 'upcoming' ? 'status-upcoming' : 'status-completed');
      ?>
      <div class="match-card <?= ($row['status'] === 'completed' || $row['status'] === 'live') ? 'clickable-match' : ''; ?>"
           data-match-id="<?= $row['id'] ?>"
           <?= $row['status'] === 'completed' ? 'onclick="showMatchDetails(' . $row['id'] . ')"' : ($row['status'] === 'live' ? 'onclick="showLiveMatchMessage(\'' . htmlspecialchars($row['team1_name']) . '\', \'' . htmlspecialchars($row['team2_name']) . '\')"' : ''); ?>>
        <div class="match-header">
          <span class="league">Week <?= $row['week']; ?></span>
          <div class="time-status">
            <span class="time"><?= $matchDateTime->format("M d, Y H:i") ?> CAT</span><br>
            <span class="match-status <?= $statusClass; ?>"><?= ucfirst($row['status']); ?></span>
          </div>
        </div>
        <div class="match-teams" style="display: flex; justify-content: space-around; align-items: center;">
          <div class="team-display">
            <div class="team-logo"><img src="Logo/<?= $row['team1_logo']; ?>" alt=""></div>
            <div class="team-name"><?= $row['team1_name']; ?></div>
          </div>
          <?php if ($row['status'] === 'live' || $row['status'] === 'completed'): ?>
            <div class="score"><?= $row['team1_goal']; ?> - <?= $row['team2_goal']; ?></div>
          <?php else: ?>
            <div class="vs-text">VS</div>
          <?php endif; ?>
          <div class="team-display">
            <div class="team-logo"><img src="Logo/<?= $row['team2_logo']; ?>" alt=""></div>
            <div class="team-name"><?= $row['team2_name']; ?></div>
          </div>
        </div>
        <div class="match-details">
          <i class="fas fa-map-marker-alt"></i> <?= $row['stadium']; ?>
          <?php if ($row['status'] === 'completed'): ?>
            <div class="click-hint"><i class="fas fa-info-circle"></i> Click for match details</div>
          <?php elseif ($row['status'] === 'live'): ?>
            <div class="click-hint"><i class="fas fa-broadcast-tower"></i> Click for live match info</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; else: ?>
        <p style='text-align: center;'>No matches found.</p>
      <?php endif; $conn->close(); ?>
    </div>
  </div>

  <footer class="footer">
    <p>&copy; 2026 Aime Tresor. All rights reserved.</p>
    <p><a href="#">Privacy</a> | <a href="#">Terms</a></p>
  </footer>

  <!-- Match Details Modal -->
  <div id="matchDetailsModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Match Details</h2>
        <span class="close" onclick="closeMatchDetails()">&times;</span>
      </div>
      <div class="modal-body" id="modalBody">
        <div class="loading">
          <i class="fas fa-spinner"></i>
          <p>Loading match details...</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Show match details modal
    function showMatchDetails(matchId) {
      const modal = document.getElementById('matchDetailsModal');
      const modalBody = document.getElementById('modalBody');

      // Show modal with loading state
      modal.style.display = 'block';
      modalBody.innerHTML = `
        <div class="loading">
          <i class="fas fa-spinner"></i>
          <p>Loading match details...</p>
        </div>
      `;

      // Fetch match details
      fetch('get_match_details.php?match_id=' + matchId)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            displayMatchDetails(data.match);
          } else {
            modalBody.innerHTML = `
              <div class="no-data">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: var(--danger-color); margin-bottom: 15px;"></i>
                <p>Error loading match details: ${data.message}</p>
              </div>
            `;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          modalBody.innerHTML = `
            <div class="no-data">
              <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: var(--danger-color); margin-bottom: 15px;"></i>
              <p>Failed to load match details. Please try again.</p>
            </div>
          `;
        });
    }

    // Display match details in modal
    function displayMatchDetails(match) {
      const modalBody = document.getElementById('modalBody');

      // Group cards by team
      let team1Cards = [];
      let team2Cards = [];

      if (match.cards && match.cards.length > 0) {
        match.cards.forEach(card => {
          if (card.team_name === match.team1_name) {
            team1Cards.push(card);
          } else if (card.team_name === match.team2_name) {
            team2Cards.push(card);
          }
        });
      }

      // Generate HTML for team cards
      function generateTeamCardsHtml(cards, teamName) {
        if (cards.length === 0) {
          return ''; // Return empty string if no cards
        }

        let html = `<div class="team-cards-section">
          <h4 class="team-cards-header">${teamName} Cards</h4>
          <ul class="team-cards-list">`;

        cards.forEach(card => {
          const cardClass = card.card_type === 'yellow' ? 'yellow-card' : 'red-card';
          const cardIcon = card.card_type === 'yellow' ? 'Y' : 'R';
          html += `
            <li class="card-item">
              <div class="card-icon ${cardClass}">${cardIcon}</div>
              <div class="player-info">
                <div class="player-name">#${card.number || '?'} ${card.fname} ${card.lname}</div>
                ${card.card_time ? `<div class="card-time">${card.card_time}'</div>` : ''}
              </div>
            </li>
          `;
        });

        html += '</ul></div>';
        return html;
      }

      let cardsHtml = '';
      const team1CardsHtml = generateTeamCardsHtml(team1Cards, match.team1_name);
      const team2CardsHtml = generateTeamCardsHtml(team2Cards, match.team2_name);

      if (team1CardsHtml || team2CardsHtml) {
        cardsHtml = team1CardsHtml + team2CardsHtml;
      } else {
        cardsHtml = '<div class="no-data">No cards issued in this match</div>';
      }

      // Group goals by team
      let team1Goals = [];
      let team2Goals = [];

      if (match.goals && match.goals.length > 0) {
        match.goals.forEach(goal => {
          if (goal.team_name === match.team1_name) {
            team1Goals.push(goal);
          } else if (goal.team_name === match.team2_name) {
            team2Goals.push(goal);
          }
        });
      }

      // Generate HTML for team goals
      function generateTeamGoalsHtml(goals, teamName) {
        if (goals.length === 0) {
          return ''; // Return empty string if no goals
        }

        let html = `<div class="team-goals-section">
          <h4 class="team-goals-header">${teamName} Goals</h4>
          <ul class="team-goals-list">`;

        goals.forEach(goal => {
          const goalTypeIcon = getGoalTypeIcon(goal.goal_type);
          const goalTypeText = getGoalTypeText(goal.goal_type);
          html += `
            <li class="goal-item">
              <div class="goal-icon">
                <i class="fas ${goalTypeIcon}" style="color: var(--primary-color); margin-right: 10px;"></i>
              </div>
              <div class="goal-info">
                <div class="player-name">
                  ${goal.player_name ? `#${goal.number || '?'} ${goal.player_name}` : 'Unknown Player'}
                </div>
                <div class="goal-details">
                  <span class="goal-time">${goal.goal_minute}'</span>
                  ${goal.goal_type && goal.goal_type !== 'regular' ? `<span class="goal-type">(${goalTypeText})</span>` : ''}
                  ${goal.description ? `<div class="goal-description">${goal.description}</div>` : ''}
                </div>
              </div>
            </li>
          `;
        });

        html += '</ul></div>';
        return html;
      }

      let goalsHtml = '';
      const team1GoalsHtml = generateTeamGoalsHtml(team1Goals, match.team1_name);
      const team2GoalsHtml = generateTeamGoalsHtml(team2Goals, match.team2_name);

      if (team1GoalsHtml || team2GoalsHtml) {
        goalsHtml = team1GoalsHtml + team2GoalsHtml;
      } else {
        goalsHtml = '<div class="no-data">No goals scored in this match</div>';
      }

      let officialsHtml = '';
      if (match.officials) {
        if (match.officials.referee) {
          officialsHtml += `
            <li class="official-item">
              <i class="fas fa-user-tie" style="margin-right: 10px; color: var(--primary-color);"></i>
              <div>
                <div class="player-name">Main Referee</div>
                <div class="team-badge">${match.officials.referee}</div>
              </div>
            </li>
          `;
        }

      }

      if (!officialsHtml) {
        officialsHtml = '<li class="no-data">No referee information available</li>';
      }

      modalBody.innerHTML = `
        <div class="match-summary">
          <div class="team-info">
            <img src="Logo/${match.team1_logo}" alt="${match.team1_name}">
            <h3>${match.team1_name}</h3>
          </div>
          <div class="final-score">
            <div class="score-display">${match.team1_goal} - ${match.team2_goal}</div>
            <div class="match-info">
              <div><i class="fas fa-calendar"></i> ${match.match_date}</div>
              <div><i class="fas fa-clock"></i> ${match.match_time}</div>
              <div><i class="fas fa-map-marker-alt"></i> ${match.stadium}</div>
            </div>
          </div>
          <div class="team-info">
            <img src="Logo/${match.team2_logo}" alt="${match.team2_name}">
            <h3>${match.team2_name}</h3>
          </div>
        </div>

        <div class="details-grid">
          <div class="detail-section cards-section">
            <h3><i class="fas fa-square" style="color: #ffc107;"></i><i class="fas fa-square" style="color: #dc3545;"></i> Cards Issued</h3>
            <div class="cards-container">
              ${cardsHtml}
            </div>
          </div>

          <div class="detail-section goals-section">
            <h3><i class="fas fa-futbol"></i> Goals Scored</h3>
            <div class="goals-container">
              ${goalsHtml}
            </div>
          </div>

          <div class="detail-section">
            <h3><i class="fas fa-user-tie"></i> Match Officials</h3>
            <ul class="officials-list">
              ${officialsHtml}
            </ul>
          </div>

          <div class="detail-section">
            <h3><i class="fas fa-info-circle"></i> Match Information</h3>
            <div style="line-height: 1.8;">
              <div><strong>Week:</strong> ${match.week}</div>
              <div><strong>Season:</strong> ${match.season}</div>
              <div><strong>Status:</strong> <span style="color: var(--green-status); font-weight: bold;">${match.status.charAt(0).toUpperCase() + match.status.slice(1)}</span></div>
              <div><strong>Final Score:</strong> ${match.team1_name} ${match.team1_goal} - ${match.team2_goal} ${match.team2_name}</div>
            </div>
          </div>
        </div>
      `;
    }

    // Helper function to get goal type icon
    function getGoalTypeIcon(goalType) {
      switch(goalType) {
        case 'penalty': return 'fa-dot-circle';
        case 'own_goal': return 'fa-times-circle';
        case 'free_kick': return 'fa-running';
        default: return 'fa-futbol';
      }
    }

    // Helper function to get goal type text
    function getGoalTypeText(goalType) {
      switch(goalType) {
        case 'penalty': return 'Penalty';
        case 'own_goal': return 'Own Goal';
        case 'free_kick': return 'Free Kick';
        default: return 'Regular';
      }
    }

    // Show live match message
    function showLiveMatchMessage(team1Name, team2Name) {
      const modal = document.getElementById('matchDetailsModal');
      const modalBody = document.getElementById('modalBody');

      // Show modal with live match message
      modal.style.display = 'block';
      modalBody.innerHTML = `
        <div style="text-align: center; padding: 40px 20px;">
          <div style="font-size: 4rem; color: var(--red-status); margin-bottom: 20px; animation: pulse 2s infinite;">
            <i class="fas fa-broadcast-tower"></i>
          </div>
          <h2 style="color: var(--red-status); margin-bottom: 15px; font-family: 'Montserrat', sans-serif;">
            Match Currently Live!
          </h2>
          <p style="font-size: 1.2rem; color: var(--dark-text); margin-bottom: 10px;">
            <strong>${team1Name} vs ${team2Name}</strong>
          </p>
          <p style="color: var(--gray-text); font-size: 1rem; line-height: 1.6;">
            This match is currently being played. Match details will be available once the match is completed.
          </p>
          <div style="margin-top: 30px;">
            <button onclick="closeMatchDetails()" style="
              background: var(--primary-color);
              color: white;
              border: none;
              padding: 12px 30px;
              border-radius: 25px;
              font-size: 1rem;
              font-weight: 600;
              cursor: pointer;
              transition: all 0.3s ease;
            " onmouseover="this.style.background='var(--green-status)'" onmouseout="this.style.background='var(--primary-color)'">
              <i class="fas fa-check"></i> Got it
            </button>
          </div>
        </div>
      `;
    }

    // Close match details modal
    function closeMatchDetails() {
      document.getElementById('matchDetailsModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('matchDetailsModal');
      if (event.target === modal) {
        closeMatchDetails();
      }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeMatchDetails();
      }
    });

    // Auto-refresh matches every 30 seconds to show updated status
    function refreshMatches() {
      // Only refresh if no modal is open
      if (!document.getElementById('matchDetailsModal').style.display ||
          document.getElementById('matchDetailsModal').style.display === 'none') {

        fetch('get_matches_status.php')
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              updateMatchDisplay(data.matches);
            }
          })
          .catch(error => {
            console.log('Auto-refresh failed, falling back to page reload');
            location.reload();
          });
      }
    }

    // Update match display with new data
    function updateMatchDisplay(matches) {
      matches.forEach(match => {
        const matchCard = document.querySelector(`[data-match-id="${match.id}"]`);
        if (matchCard) {
          // Update status badge
          const statusBadge = matchCard.querySelector('.match-status');
          if (statusBadge) {
            statusBadge.className = `match-status status-${match.status}`;
            statusBadge.textContent = match.status.charAt(0).toUpperCase() + match.status.slice(1);
          }

          // Update score if match is live or completed
          const scoreElement = matchCard.querySelector('.score');
          if (scoreElement && (match.status === 'live' || match.status === 'completed')) {
            if (match.team1_goal !== null && match.team2_goal !== null) {
              scoreElement.textContent = `${match.team1_goal} - ${match.team2_goal}`;
            }
          }
        }
      });

      // Re-animate live matches
      animateLiveMatches();
    }

    // Set up auto-refresh for live updates
    setInterval(refreshMatches, 30000); // 30 seconds

    // Add visual indicator for live matches
    function animateLiveMatches() {
      const liveStatuses = document.querySelectorAll('.status-live');
      liveStatuses.forEach(status => {
        status.style.animation = 'pulse 2s infinite';
      });
    }

    // Initialize live match animations
    animateLiveMatches();
  </script>

</body>
</html>
