# ⚽ Referee Goal Entry System

## Overview
This system allows referees to enter and update match goals for both teams directly from their dashboard. The system provides a user-friendly interface with default 0-0 scores and real-time updates.

## Features Implemented

### ✅ Core Functionality
- **Goal Entry Form**: Modal popup form for entering team goals
- **Default Scores**: Shows 0-0 when no goals have been entered
- **Real-time Updates**: Scores update immediately after submission
- **Authorization**: Only assigned referees can update match goals
- **Validation**: Prevents negative goal values and validates input

### ✅ User Interface
- **Dashboard Integration**: Goal entry buttons on referee dashboard
- **Match View Integration**: Large score display with update functionality
- **Responsive Design**: Works on desktop and mobile devices
- **Visual Feedback**: Success/error messages and hover effects

### ✅ Security Features
- **Referee Authorization**: Verifies referee is assigned to the match
- **Input Validation**: Server-side validation of goal values
- **Transaction Safety**: Database transactions ensure data consistency
- **CSRF Protection**: Form-based submissions with proper validation

## Files Modified/Created

### New Files
1. **`referee/update_goals.php`** - Backend script for processing goal updates
2. **`GOAL_ENTRY_SYSTEM_README.md`** - This documentation file

### Modified Files
1. **`referee/index.php`** - Added goal entry functionality to dashboard
2. **`referee/view_match.php`** - Added score display and goal entry to match view

## Database Structure

The system uses the existing `match` table with these columns:
```sql
team1_goal: INT(11) DEFAULT NULL  -- Goals for team 1
team2_goal: INT(11) DEFAULT NULL  -- Goals for team 2
```

Default behavior: NULL values display as 0 in the interface.

## Usage Instructions

### For Referees

#### From Dashboard (`referee/index.php`)
1. **View Matches**: All assigned matches show current scores (0-0 by default)
2. **Update Goals**: Click "⚽ Update Goals" button on live/upcoming matches
3. **Enter Scores**: Use the modal form to enter goals for both teams
4. **Submit**: Click "Update Goals" to save changes
5. **Confirmation**: Success message confirms the update

#### From Match View (`referee/view_match.php`)
1. **View Score**: Large score display at the top of the match page
2. **Update Score**: Click "⚽ Update Score" button
3. **Enter Goals**: Use the modal form to update scores
4. **Real-time Update**: Score display updates immediately

### Goal Entry Process
1. Click goal entry button (dashboard or match view)
2. Modal popup opens with current scores pre-filled
3. Adjust goal values using number inputs (0-99 range)
4. Click "Update Goals" to submit
5. Modal closes and page shows updated scores

## Technical Implementation

### Frontend Features
- **Modal Interface**: Clean popup form for goal entry
- **JavaScript Validation**: Client-side input validation
- **Responsive Design**: Works on all screen sizes
- **Visual Feedback**: Hover effects and smooth transitions

### Backend Processing
```php
// Key validation steps in update_goals.php
1. Session validation (referee logged in)
2. Input validation (required fields, non-negative values)
3. Authorization check (referee assigned to match)
4. Database transaction (safe updates)
5. Success/error handling (user feedback)
```

### Security Measures
- **Authentication**: Referee must be logged in
- **Authorization**: Only assigned referees can update goals
- **Input Sanitization**: All inputs validated and sanitized
- **Transaction Safety**: Database rollback on errors

## User Interface Elements

### Dashboard Integration
- **Score Display**: Shows current score (0-0 default) for all matches
- **Action Buttons**: "Update Goals" and "View Match" buttons
- **Status Indicators**: Different styling for live/upcoming/completed matches

### Match View Integration
- **Large Score Display**: Prominent score section with team logos
- **Update Button**: Easy access to goal entry form
- **Visual Design**: Gradient background with professional styling

### Modal Form
- **Team Labels**: Dynamic team names in form labels
- **Number Inputs**: Restricted to 0-99 range with validation
- **Action Buttons**: Cancel and Update options
- **Responsive Layout**: Adapts to different screen sizes

## Error Handling

### Client-Side Validation
- Required field validation
- Number range validation (0-99)
- Form submission prevention on invalid data

### Server-Side Validation
- Authentication checks
- Authorization verification
- Input sanitization
- Database error handling

### User Feedback
- Success messages with match details
- Error messages for validation failures
- Visual indicators for form states

## Benefits

1. **Easy Goal Management**: Simple interface for referees
2. **Real-time Updates**: Immediate score reflection
3. **Secure Access**: Only authorized referees can update
4. **Professional Interface**: Clean, modern design
5. **Mobile Friendly**: Works on all devices
6. **Data Integrity**: Transaction-safe updates

## Future Enhancements

Potential improvements could include:
- Goal scorer tracking
- Goal time recording
- Match event timeline
- Goal statistics and reports
- Automated match completion based on final whistle

## Testing Recommendations

1. **Basic Functionality**
   - Test goal entry from dashboard
   - Test goal entry from match view
   - Verify default 0-0 display

2. **Validation Testing**
   - Test negative number rejection
   - Test large number handling
   - Test empty field validation

3. **Authorization Testing**
   - Test with assigned referee
   - Test with non-assigned referee
   - Test without login

4. **UI/UX Testing**
   - Test modal functionality
   - Test responsive design
   - Test success/error messages

The goal entry system is now fully functional and ready for use by referees to manage match scores efficiently and securely.
