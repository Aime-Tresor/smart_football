-- Migration 007: running totals needed for "Update Player Statistics" on
-- match completion (goals, appearances). Cards already have yellow/
-- double_yellow/red columns; goals/appearances did not exist anywhere.
-- Assists/own-goals/minutes-played are NOT added here - the schema has no
-- source data for them (no assist/own-goal flag, no substitution tracking).

SET @db := DATABASE();

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'team_members' AND COLUMN_NAME = 'goals_scored') = 0,
  'ALTER TABLE `team_members` ADD COLUMN `goals_scored` INT(11) NOT NULL DEFAULT 0',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'team_members' AND COLUMN_NAME = 'appearances') = 0,
  'ALTER TABLE `team_members` ADD COLUMN `appearances` INT(11) NOT NULL DEFAULT 0',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
