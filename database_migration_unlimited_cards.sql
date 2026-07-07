-- Database Migration: Enable Unlimited Cards for Players
-- This script modifies the team_members table to support unlimited card counts
-- Run this script to update your database schema

-- Backup current data (optional but recommended)
-- CREATE TABLE team_members_backup AS SELECT * FROM team_members;

-- The current schema already supports unlimited cards since the columns are INT(11)
-- However, we'll ensure the columns can handle large numbers and update any constraints

-- Update the team_members table to ensure unlimited card support
-- Remove any check constraints that might limit card values (if they exist)
ALTER TABLE `team_members` 
MODIFY COLUMN `yellow` int(11) DEFAULT 0 COMMENT 'Total yellow cards received (unlimited)',
MODIFY COLUMN `double_yellow` int(11) DEFAULT 0 COMMENT 'Total double yellow cards (red from 2 yellows)',
MODIFY COLUMN `red` int(11) DEFAULT 0 COMMENT 'Total red cards received (unlimited)';

-- Update the cards table to ensure it can handle the new unlimited card system
-- The cards table already supports unlimited entries per player, so no changes needed
-- But let's add an index for better performance when querying player cards
CREATE INDEX IF NOT EXISTS idx_cards_member_match ON cards(member_id, match_id);
CREATE INDEX IF NOT EXISTS idx_cards_match_type ON cards(match_id, card_type);

-- Optional: Add a view to easily get total cards per player across all matches
CREATE OR REPLACE VIEW player_card_summary AS
SELECT 
    tm.member_id,
    tm.fname,
    tm.lname,
    tm.team,
    tm.yellow as total_yellows,
    tm.red as total_reds,
    tm.double_yellow as total_double_yellows,
    (tm.yellow + tm.red + tm.double_yellow) as total_cards,
    COUNT(c.card_id) as cards_in_history
FROM team_members tm
LEFT JOIN cards c ON tm.member_id = c.member_id
GROUP BY tm.member_id, tm.fname, tm.lname, tm.team, tm.yellow, tm.red, tm.double_yellow;

-- Migration completed successfully
-- The system now supports unlimited cards for all players
