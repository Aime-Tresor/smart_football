<?php
// Test page for the enhanced match score functionality
session_start();

// Set fallback referee ID
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Enhanced Match Score Test</h2>";

// Get available matches
$matches_sql = "
    SELECT
        m.id,
        m.match_date,
        m.match_time,
        m.status,
        m.team1_goal,
        m.team2_goal,
        t1.name AS team1_name,
        t2.name AS team2_name
    FROM `match` m
    JOIN `team` t1 ON m.team1_id = t1.team_id
    JOIN `team` t2 ON m.team2_id = t2.team_id
    ORDER BY m.id
    LIMIT 5
";

$matches_result = $conn->query($matches_sql);

if ($matches_result && $matches_result->num_rows > 0) {
    echo "<h3>Available Matches (Enhanced View):</h3>";
    echo "<div style='display: grid; gap: 20px; margin: 20px 0;'>";
    
    while ($row = $matches_result->fetch_assoc()) {
        echo "<div style='border: 2px solid #007bff; border-radius: 10px; padding: 20px; background: linear-gradient(135deg, #f8f9fa, #e9ecef);'>";
        echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;'>";
        echo "<h4 style='margin: 0; color: #2c3e50;'>Match #{$row['id']}</h4>";
        echo "<span style='background: #007bff; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;'>" . ucfirst($row['status']) . "</span>";
        echo "</div>";
        
        echo "<div style='display: flex; align-items: center; justify-content: center; gap: 20px; margin: 20px 0;'>";
        echo "<div style='text-align: center;'>";
        echo "<div style='font-weight: bold; color: #2c3e50;'>{$row['team1_name']}</div>";
        echo "<div style='font-size: 24px; font-weight: 900; color: #007bff;'>" . ($row['team1_goal'] ?? 0) . "</div>";
        echo "</div>";
        
        echo "<div style='font-size: 18px; color: #6c757d; font-weight: 300;'>VS</div>";
        
        echo "<div style='text-align: center;'>";
        echo "<div style='font-weight: bold; color: #2c3e50;'>{$row['team2_name']}</div>";
        echo "<div style='font-size: 24px; font-weight: 900; color: #007bff;'>" . ($row['team2_goal'] ?? 0) . "</div>";
        echo "</div>";
        echo "</div>";
        
        echo "<div style='text-align: center; margin: 15px 0;'>";
        echo "<div style='color: #6c757d; font-size: 14px;'>";
        echo date('M d, Y', strtotime($row['match_date'])) . " at " . date('H:i', strtotime($row['match_time']));
        echo "</div>";
        echo "</div>";
        
        echo "<div style='text-align: center;'>";
        echo "<a href='view_match.php?match_id={$row['id']}' target='_blank' style='background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; transition: all 0.3s ease;'>⚽ Manage Match</a>";
        echo "</div>";
        
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ No matches found</p>";
}

echo "<h3>Features to Test:</h3>";
echo "<ul style='line-height: 1.8;'>";
echo "<li><strong>Enhanced Score Display:</strong> Team names with individual score numbers and quick '+' buttons</li>";
echo "<li><strong>Quick Goal Entry:</strong> Click the '+' button next to team scores for instant goal entry</li>";
echo "<li><strong>Tabbed Goal Entry:</strong> Switch between 'Quick Entry' and 'Detailed Entry' tabs</li>";
echo "<li><strong>Player Selection:</strong> Detailed entry allows selecting specific players who scored</li>";
echo "<li><strong>Goal Types:</strong> Support for regular goals, penalties, free kicks, and own goals</li>";
echo "<li><strong>Responsive Design:</strong> Interface adapts to different screen sizes</li>";
echo "<li><strong>Real-time Updates:</strong> Scores update immediately after adding goals</li>";
echo "</ul>";

echo "<h3>Testing Instructions:</h3>";
echo "<ol style='line-height: 1.8;'>";
echo "<li>Click 'Manage Match' for any match above</li>";
echo "<li>Try clicking the '+' button next to team scores for quick goal entry</li>";
echo "<li>Test both 'Quick Entry' and 'Detailed Entry' tabs in the goal entry section</li>";
echo "<li>Click on team player sections to load and view players</li>";
echo "<li>Try assigning cards to players using the yellow/red card buttons</li>";
echo "<li>Test the responsive design by resizing your browser window</li>";
echo "</ol>";

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 15px; margin: 20px 0;'>";
echo "<h4 style='color: #155724; margin-top: 0;'>✅ Enhanced Features Added:</h4>";
echo "<ul style='color: #155724; margin-bottom: 0;'>";
echo "<li>Improved score display with team names and quick action buttons</li>";
echo "<li>Tabbed interface for quick vs detailed goal entry</li>";
echo "<li>Modal popup for instant goal entry</li>";
echo "<li>Better player selection with team-specific loading</li>";
echo "<li>Responsive design for mobile and tablet devices</li>";
echo "<li>Enhanced visual feedback and animations</li>";
echo "</ul>";
echo "</div>";

$conn->close();
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    margin: 0;
    padding: 20px;
    color: #2c3e50;
}

h2, h3 {
    color: white;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

a:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40,167,69,0.3);
}
</style>
