# Goal Saving Error Troubleshooting Guide

## Problem Fixed: "An error occurred while adding the goal"

### 🔍 Root Causes Identified and Fixed:

1. **Wrong Database Table**: The original `save_goal.php` was trying to insert into a `goals` table that doesn't exist
2. **Missing Database Table**: The `individual_goals` table needed to be created
3. **Poor Error Handling**: JavaScript wasn't showing detailed error messages
4. **Data Type Issues**: Incorrect parameter binding in SQL queries

### ✅ Solutions Implemented:

#### 1. Fixed Database Table Reference
- **Before**: Trying to insert into `goals` table
- **After**: Using correct `individual_goals` table with proper schema

#### 2. Enhanced save_goal.php
- Added proper error reporting and debugging
- Implemented transaction handling for data consistency
- Added validation for match and team relationships
- Improved parameter binding and data types
- Added support for goal types and descriptions

#### 3. Improved JavaScript Error Handling
- Added detailed console logging for debugging
- Better error message display to users
- Proper JSON response parsing with error handling

#### 4. Database Setup Tools
- Created `setup_individual_goals_table.php` to ensure table exists
- Added test scripts for debugging goal saving functionality

### 🧪 Testing Tools Created:

1. **`setup_individual_goals_table.php`** - Sets up the database table if missing
2. **`test_goal_saving.php`** - Interactive form to test goal saving
3. **Enhanced error logging** in JavaScript console

### 📋 Step-by-Step Fix Process:

#### Step 1: Set Up Database Table
```bash
# Visit this URL to set up the table:
http://localhost/smart-football/referee/setup_individual_goals_table.php
```

#### Step 2: Test Goal Saving
```bash
# Use this URL to test goal saving functionality:
http://localhost/smart-football/referee/test_goal_saving.php
```

#### Step 3: Check Browser Console
- Open browser Developer Tools (F12)
- Go to Console tab
- Try adding a goal and check for detailed error messages

### 🗄️ Database Schema Used:

```sql
CREATE TABLE `individual_goals` (
  `goal_id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `player_id` int(11) DEFAULT NULL,
  `goal_minute` varchar(10) DEFAULT NULL,
  `goal_type` enum('regular','penalty','own_goal','free_kick') DEFAULT 'regular',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`goal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 🔧 Key Code Changes:

#### save_goal.php Improvements:
- ✅ Proper database table (`individual_goals`)
- ✅ Transaction handling
- ✅ Better error messages
- ✅ Input validation
- ✅ Support for all goal types

#### view_match.php Improvements:
- ✅ Enhanced error handling in JavaScript
- ✅ Detailed console logging
- ✅ Better user feedback
- ✅ Multiple goal entry methods (quick/detailed/modal)

### 🚀 How to Use the Enhanced System:

1. **Quick Goal Entry**: Use the "Quick Entry" tab for simple goal addition
2. **Detailed Goal Entry**: Use the "Detailed Entry" tab for complete goal information
3. **Modal Entry**: Click the "+" buttons next to team scores for instant goal entry
4. **Player Selection**: Select specific players who scored (optional)
5. **Goal Types**: Choose from regular, penalty, free kick, or own goal

### 🐛 Debugging Tips:

If you still encounter issues:

1. **Check Browser Console**: Look for detailed error messages
2. **Verify Database**: Ensure `individual_goals` table exists
3. **Test Endpoint**: Use `test_goal_saving.php` to test the API directly
4. **Check PHP Errors**: Look at server error logs
5. **Verify Match Data**: Ensure match and team IDs are valid

### 📊 Expected Behavior:

- ✅ Goals save to `individual_goals` table
- ✅ Match totals update automatically
- ✅ Success messages display to user
- ✅ Scores refresh immediately
- ✅ Player information is recorded (if selected)
- ✅ Goal types and descriptions are saved

### 🎯 Success Indicators:

When working correctly, you should see:
- Green success message: "⚽ Goal added for [Team Name]..."
- Updated score display
- Console logs showing successful API responses
- New records in `individual_goals` table

### 📞 Still Having Issues?

If problems persist:
1. Run `setup_individual_goals_table.php` first
2. Test with `test_goal_saving.php`
3. Check browser console for detailed errors
4. Verify database connection and table structure
5. Ensure proper session management (referee_id)

The system is now robust and should handle goal saving reliably with proper error reporting and user feedback.
