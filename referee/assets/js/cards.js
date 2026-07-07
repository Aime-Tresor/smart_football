// Game state
let selectedPlayer = null;
let matchEvents = [];
let matchStartTime = new Date();
let players = {
    marine: [
        {number: 1, name: "Goalkeeper", cards: []},
        {number: 2, name: "Defender", cards: []},
        {number: 3, name: "Defender", cards: []},
        {number: 4, name: "Midfielder", cards: []},
        {number: 5, name: "Midfielder", cards: []},
        {number: 6, name: "Midfielder", cards: []},
        {number: 7, name: "Winger", cards: []},
        {number: 8, name: "Midfielder", cards: []},
        {number: 9, name: "Striker", cards: []},
        {number: 10, name: "Playmaker", cards: []},
        {number: 11, name: "Winger", cards: []}
    ],
    mukura: [
        {number: 1, name: "Goalkeeper", cards: []},
        {number: 2, name: "Defender", cards: []},
        {number: 3, name: "Defender", cards: []},
        {number: 4, name: "Midfielder", cards: []},
        {number: 5, name: "Midfielder", cards: []},
        {number: 6, name: "Midfielder", cards: []},
        {number: 7, name: "Winger", cards: []},
        {number: 8, name: "Midfielder", cards: []},
        {number: 9, name: "Striker", cards: []},
        {number: 10, name: "Playmaker", cards: []},
        {number: 11, name: "Winger", cards: []}
    ]
};

function initializePage() {
    generatePlayers('marine', 'marineTeam');
    generatePlayers('mukura', 'mukuraTeam');
    updateMatchTime();
    setInterval(updateMatchTime, 1000);
}

function generatePlayers(team, containerId) {
    const container = document.getElementById(containerId);
    const teamPlayers = players[team];
    
    container.innerHTML = teamPlayers.map(player => `
        <div class="player-card" onclick="selectPlayer('${team}', ${player.number})" id="${team}-${player.number}">
            <div class="player-number">${player.number}</div>
            <div class="player-name">${player.name}</div>
            <div class="player-cards" id="${team}-${player.number}-cards">
                <!-- Cards will appear here -->
            </div>
        </div>
    `).join('');
}

function updateMatchTime() {
    const now = new Date();
    const elapsed = Math.floor((now - matchStartTime) / 1000 / 60);
    document.getElementById('matchTime').textContent = `${elapsed}'`;
}

function selectPlayer(team, playerNumber) {
    // Remove previous selection
    if (selectedPlayer) {
        document.getElementById(`${selectedPlayer.team}-${selectedPlayer.number}`).classList.remove('selected');
    }

    // Set new selection
    selectedPlayer = {team, number: playerNumber};
    document.getElementById(`${team}-${playerNumber}`).classList.add('selected');

    // Update selected info
    const player = players[team].find(p => p.number === playerNumber);
    const teamName = team === 'marine' ? 'Marine FC' : 'Mukura';
    document.getElementById('selectedInfo').classList.add('show');
    document.getElementById('selectedDetails').innerHTML = `
        <strong>${teamName} #${playerNumber}</strong> - ${player.name}<br>
        <small>Cards: ${player.cards.length > 0 ? player.cards.join(', ') : 'None'}</small>
    `;

    // Enable buttons
    document.getElementById('yellowBtn').disabled = false;
    document.getElementById('redBtn').disabled = false;
}

function issueCard(cardType) {
    if (!selectedPlayer) return;

    const {team, number} = selectedPlayer;
    const player = players[team].find(p => p.number === number);
    const teamName = team === 'marine' ? 'Marine FC' : 'Mukura';
    const now = new Date();
    const matchMinute = Math.floor((now - matchStartTime) / 1000 / 60);

    // Allow unlimited cards - no restrictions
    // Simply add the card to the player's record
    player.cards.push(cardType);

    // Add to match events
    matchEvents.push({
        time: matchMinute,
        team: teamName,
        player: `#${number}`,
        playerName: player.name,
        card: cardType,
        timestamp: now
    });

    // Update displays
    updatePlayerCardDisplay(team, number);
    updateTeamStats();
    updateEventsList();
    updateSummary();

    // Clear selection
    clearSelection();
}

function updatePlayerCardDisplay(team, playerNumber) {
    const player = players[team].find(p => p.number === playerNumber);
    const cardContainer = document.getElementById(`${team}-${playerNumber}-cards`);
    
    cardContainer.innerHTML = player.cards.map(card => 
        `<div class="card-indicator ${card}-indicator"></div>`
    ).join('');
}

function updateTeamStats() {
    ['marine', 'mukura'].forEach(team => {
        const yellowCount = players[team].reduce((count, player) => 
            count + player.cards.filter(card => card === 'yellow').length, 0);
        const redCount = players[team].reduce((count, player) => 
            count + player.cards.filter(card => card === 'red').length, 0);
        
        document.getElementById(`${team}-yellow-count`).textContent = yellowCount;
        document.getElementById(`${team}-red-count`).textContent = redCount;
    });
}

function updateEventsList() {
    const container = document.getElementById('eventsList');
    
    if (matchEvents.length === 0) {
        container.innerHTML = '<div class="no-events">No cards issued yet</div>';
        return;
    }

    container.innerHTML = matchEvents
        .sort((a, b) => b.timestamp - a.timestamp)
        .map(event => `
            <div class="event-item">
                <div class="event-time">${event.time}'</div>
                <div class="event-card ${event.card}-indicator"></div>
                <div class="event-details">
                    <strong>${event.team} ${event.player}</strong><br>
                    <small>${event.playerName} - ${event.card.toUpperCase()} CARD</small>
                </div>
            </div>
        `).join('');
}

function updateSummary() {
    const totalYellow = matchEvents.filter(e => e.card === 'yellow').length;
    const totalRed = matchEvents.filter(e => e.card === 'red').length;
    const totalEvents = matchEvents.length;

    document.getElementById('totalYellow').textContent = totalYellow;
    document.getElementById('totalRed').textContent = totalRed;
    document.getElementById('totalEvents').textContent = totalEvents;
}

function clearSelection() {
    if (selectedPlayer) {
        document.getElementById(`${selectedPlayer.team}-${selectedPlayer.number}`).classList.remove('selected');
    }
    selectedPlayer = null;
    document.getElementById('selectedInfo').classList.remove('show');
    document.getElementById('yellowBtn').disabled = true;
    document.getElementById('redBtn').disabled = true;
}

document.addEventListener('DOMContentLoaded', initializePage);
