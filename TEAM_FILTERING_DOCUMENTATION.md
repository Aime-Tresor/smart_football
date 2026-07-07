# Team-Specific Transfer Filtering Documentation

## Overview
The `teams/requests.php` page has been enhanced to ensure it displays **only transfers that belong to the specific logged-in team**. This provides proper team isolation and data security.

## How Team Filtering Works

### 1. **Session-Based Team Identification**
```php
$your_team_id = $_SESSION['Team_id'] ?? null;
if (!$your_team_id) {
    // Access denied - redirect to login
    exit;
}
```

### 2. **SQL Query Filtering**
```sql
SELECT t.*, 
       tm.fname, tm.lname, tm.role_in_team, tm.number, tm.position, tm.post,
       tf.name AS from_team, tt.name AS to_team
FROM transfer t
JOIN team_members tm ON t.member_id = tm.member_id
JOIN team tf ON t.team_from = tf.team_id
JOIN team tt ON t.team_to = tt.team_id
WHERE t.team_from = ?    -- This ensures only transfers FROM the logged-in team
ORDER BY t.requestDate DESC
```

### 3. **Parameter Binding**
```php
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $your_team_id);  // Binds the team ID to the query
$stmt->execute();
```

## What Each Team Sees

### ✅ **Team A (ID: 1) Sees:**
- ✅ Transfers FROM Team A to any other team
- ✅ Team A players requesting to join Team B, C, D, etc.
- ✅ Only outgoing transfer requests from Team A

### ✅ **Team B (ID: 2) Sees:**
- ✅ Transfers FROM Team B to any other team  
- ✅ Team B players requesting to join Team A, C, D, etc.
- ✅ Only outgoing transfer requests from Team B

### ❌ **What Teams DON'T See:**
- ❌ Transfers FROM other teams
- ❌ Incoming transfer requests (players wanting to join their team)
- ❌ Any transfers not originating from their team

## Security Features

### 1. **Access Control**
- **Session Validation**: Checks for valid `$_SESSION['Team_id']`
- **Team Verification**: Validates team exists in database
- **Automatic Logout**: Redirects to login if session invalid

### 2. **Data Isolation**
- **SQL Parameter Binding**: Prevents SQL injection
- **Team-Specific Queries**: Each team only accesses their own data
- **No Cross-Team Data Leakage**: Impossible to see other teams' transfers

### 3. **Input Validation**
- **Prepared Statements**: All queries use prepared statements
- **Data Sanitization**: All output is properly escaped
- **Error Handling**: Graceful handling of database errors

## Visual Indicators

### 1. **Team Information Header**
```php
<div class="card bg-primary text-white">
    <div class="card-body">
        <h4><?= htmlspecialchars($team_info['name']) ?></h4>
        <p>Team ID: <?= $your_team_id ?></p>
        <small>Viewing: Transfer Requests FROM Your Team</small>
    </div>
</div>
```

### 2. **Clear Messaging**
- **Info Alert**: Explains what transfers are shown
- **Empty State**: Clear message when no transfers exist
- **Statistics**: Only count transfers from the specific team

### 3. **Contextual Information**
- **Team Name**: Displayed prominently
- **Team ID**: Shown for verification
- **Filter Description**: Explains the current view

## Database Structure

### **Tables Involved:**
1. **`transfer`** - Main transfer records
   - `team_from` - Source team ID (used for filtering)
   - `team_to` - Destination team ID
   - `member_id` - Member being transferred
   - `status` - Transfer status (0=Pending, 1=Requested, 2=Rejected, 3=Completed)

2. **`team_members`** - Member details
   - `member_id` - Primary key
   - `fname`, `lname` - Member name
   - `role_in_team` - Player or staff role

3. **`team`** - Team information
   - `team_id` - Primary key
   - `name` - Team name
   - `stadium` - Team stadium

### **Key Relationships:**
- `transfer.team_from` → `team.team_id` (Source team)
- `transfer.team_to` → `team.team_id` (Destination team)
- `transfer.member_id` → `team_members.member_id` (Member details)

## Testing and Verification

### 1. **Test Page: `test_team_filtering.php`**
- **Team Selection**: Test with different team IDs
- **Query Verification**: Shows actual SQL results
- **Data Validation**: Confirms only correct team's transfers appear
- **Cross-Reference**: Shows all transfers for comparison

### 2. **Verification Steps:**
1. **Login as Team A**: Should only see Team A's outgoing transfers
2. **Login as Team B**: Should only see Team B's outgoing transfers
3. **No Overlap**: Verify no transfers from other teams appear
4. **Statistics Accuracy**: Confirm counts match filtered data

### 3. **Test Scenarios:**
```php
// Test Case 1: Team with transfers
$_SESSION['Team_id'] = 1;  // Should show transfers where team_from = 1

// Test Case 2: Team without transfers  
$_SESSION['Team_id'] = 5;  // Should show empty state

// Test Case 3: Invalid team
$_SESSION['Team_id'] = 999; // Should show error message
```

## Implementation Benefits

### ✅ **For Teams:**
- **Data Privacy**: Only see their own transfer requests
- **Clear Context**: Understand exactly what they're viewing
- **Accurate Statistics**: Counts reflect only their team's activity
- **Secure Access**: Cannot access other teams' sensitive data

### ✅ **For System Security:**
- **Team Isolation**: Complete separation of team data
- **Access Control**: Proper authentication and authorization
- **SQL Injection Prevention**: Prepared statements throughout
- **Session Management**: Secure session handling

### ✅ **For Administrators:**
- **Audit Trail**: Clear tracking of which team accessed what data
- **Data Integrity**: Ensures teams only modify their own transfers
- **System Reliability**: Robust error handling and validation

## Error Handling

### 1. **No Team Session:**
```php
if (!$your_team_id) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>Access Denied</h5>";
    echo "<p>You are not logged in as a team.</p>";
    echo "<a href='../login.php' class='btn btn-primary'>Login</a>";
    echo "</div>";
    exit;
}
```

### 2. **Invalid Team:**
```php
if (!$team_info) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>Team Not Found</h5>";
    echo "<p>Your team information could not be found.</p>";
    echo "</div>";
    exit;
}
```

### 3. **Database Errors:**
- **Connection Failures**: Graceful error messages
- **Query Failures**: Proper error logging
- **Data Validation**: Input sanitization and validation

## Usage Instructions

### **For Team Users:**
1. **Login**: Use team credentials to access the system
2. **View Transfers**: Navigate to `teams/requests.php`
3. **Verify Context**: Check team name in header
4. **Review Data**: See only your team's outgoing transfers
5. **Take Actions**: Edit or view details as needed

### **For Testing:**
1. **Use Test Page**: Access `teams/test_team_filtering.php`
2. **Select Team**: Choose different teams to test
3. **Verify Results**: Confirm only correct transfers appear
4. **Check Statistics**: Ensure counts are accurate

## Summary

The team filtering system ensures that:

✅ **Each team sees only their own outgoing transfer requests**
✅ **Complete data isolation between teams**
✅ **Secure access control with session validation**
✅ **Clear visual indicators of current team context**
✅ **Accurate statistics and counts for the specific team**
✅ **Robust error handling and user feedback**

This implementation provides a secure, user-friendly way for teams to manage their transfer requests while maintaining strict data privacy and access control.
