<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Status Update</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Test Match Status Update</h2>
        
        <div class="row">
            <div class="col-md-6">
                <h4>Current Matches</h4>
                <div id="matchesList">
                    <p>Loading matches...</p>
                </div>
                <button class="btn btn-primary" onclick="loadMatches()">Refresh Matches</button>
            </div>
            
            <div class="col-md-6">
                <h4>Quick Status Update</h4>
                <form id="quickUpdateForm">
                    <div class="mb-3">
                        <label for="matchSelect" class="form-label">Select Match:</label>
                        <select id="matchSelect" class="form-select" required>
                            <option value="">Choose a match...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="statusSelect" class="form-label">New Status:</label>
                        <select id="statusSelect" class="form-select" required>
                            <option value="">Choose status...</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="live">Live</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div id="scoreInputs" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="team1Score" class="form-label">Team 1 Score:</label>
                                <input type="number" id="team1Score" class="form-control" min="0">
                            </div>
                            <div class="col-md-6">
                                <label for="team2Score" class="form-label">Team 2 Score:</label>
                                <input type="number" id="team2Score" class="form-control" min="0">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Update Status</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadMatches() {
            fetch('get_matches_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMatches(data.matches);
                        populateMatchSelect(data.matches);
                    } else {
                        document.getElementById('matchesList').innerHTML = '<p class="text-danger">Error loading matches: ' + data.error + '</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('matchesList').innerHTML = '<p class="text-danger">Error: ' + error.message + '</p>';
                });
        }

        function displayMatches(matches) {
            let html = '';
            matches.forEach(match => {
                const statusClass = match.status === 'live' ? 'danger' : (match.status === 'upcoming' ? 'warning' : 'success');
                const score = (match.status === 'live' || match.status === 'completed') && match.team1_goal !== null ? 
                    ` (${match.team1_goal} - ${match.team2_goal})` : '';
                
                html += `
                    <div class="card mb-2">
                        <div class="card-body">
                            <h6 class="card-title">${match.team1_name} vs ${match.team2_name}${score}</h6>
                            <p class="card-text">
                                <span class="badge bg-${statusClass}">${match.status.toUpperCase()}</span>
                                <small class="text-muted">${match.match_date} ${match.match_time}</small>
                            </p>
                        </div>
                    </div>
                `;
            });
            document.getElementById('matchesList').innerHTML = html;
        }

        function populateMatchSelect(matches) {
            const select = document.getElementById('matchSelect');
            select.innerHTML = '<option value="">Choose a match...</option>';
            matches.forEach(match => {
                const option = document.createElement('option');
                option.value = match.id;
                option.textContent = `${match.team1_name} vs ${match.team2_name} (${match.status})`;
                option.dataset.status = match.status;
                select.appendChild(option);
            });
        }

        document.getElementById('statusSelect').addEventListener('change', function() {
            const scoreInputs = document.getElementById('scoreInputs');
            if (this.value === 'completed') {
                scoreInputs.style.display = 'block';
                document.getElementById('team1Score').required = true;
                document.getElementById('team2Score').required = true;
            } else {
                scoreInputs.style.display = 'none';
                document.getElementById('team1Score').required = false;
                document.getElementById('team2Score').required = false;
            }
        });

        document.getElementById('quickUpdateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('match_id', document.getElementById('matchSelect').value);
            formData.append('new_status', document.getElementById('statusSelect').value);
            
            if (document.getElementById('statusSelect').value === 'completed') {
                formData.append('team1_score', document.getElementById('team1Score').value);
                formData.append('team2_score', document.getElementById('team2Score').value);
            }

            fetch('fa_user/controls/update_match_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.redirected) {
                    alert('Status updated successfully!');
                    loadMatches();
                    this.reset();
                    document.getElementById('scoreInputs').style.display = 'none';
                } else {
                    alert('Error updating status');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });

        // Load matches on page load
        loadMatches();
        
        // Auto-refresh every 10 seconds
        setInterval(loadMatches, 10000);
    </script>
</body>
</html>
