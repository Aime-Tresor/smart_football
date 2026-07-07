-- Migration script to remove Assistant Referees functionality
-- This script removes the assistant_referee table and updates the weekly_fixtures table

-- First, let's backup the current data (optional - for safety)
-- CREATE TABLE assistant_referee_backup AS SELECT * FROM assistant_referee;
-- CREATE TABLE weekly_fixtures_backup AS SELECT * FROM weekly_fixtures;

-- Step 1: Drop foreign key constraints if they exist
-- (Check if there are any foreign key constraints first)
SET FOREIGN_KEY_CHECKS = 0;

-- Step 2: Remove assistant referee columns from weekly_fixtures table
-- First, let's see what data we have in these columns
SELECT 
    fixture_id,
    match_id,
    referee,
    assistant1,
    assistant2,
    official,
    access_code
FROM weekly_fixtures 
WHERE assistant1 IS NOT NULL OR assistant2 IS NOT NULL;

-- Update the weekly_fixtures table structure
-- Remove assistant1 and assistant2 columns
ALTER TABLE `weekly_fixtures` 
DROP COLUMN `assistant1`,
DROP COLUMN `assistant2`;

-- Step 3: Drop the assistant_referee table completely
DROP TABLE IF EXISTS `assistant_referee`;

-- Step 4: Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Step 5: Verify the changes
DESCRIBE weekly_fixtures;

-- The weekly_fixtures table should now only have:
-- - fixture_id (Primary Key)
-- - match_id
-- - referee
-- - official  
-- - access_code

-- Optional: Show remaining data structure
SELECT * FROM weekly_fixtures LIMIT 5;

-- Note: After running this migration, you'll need to update all PHP code
-- that references assistant1, assistant2, or the assistant_referee table
