-- Migration 004: contact fields for notification recipients + captain flag + competition label

SET @db := DATABASE();

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'team_members' AND COLUMN_NAME = 'email') = 0,
  'ALTER TABLE `team_members` ADD COLUMN `email` VARCHAR(100) NULL DEFAULT NULL',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'team_members' AND COLUMN_NAME = 'is_captain') = 0,
  'ALTER TABLE `team_members` ADD COLUMN `is_captain` TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'team' AND COLUMN_NAME = 'email') = 0,
  'ALTER TABLE `team` ADD COLUMN `email` VARCHAR(100) NULL DEFAULT NULL',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'match' AND COLUMN_NAME = 'competition') = 0,
  'ALTER TABLE `match` ADD COLUMN `competition` VARCHAR(50) NULL DEFAULT NULL',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill competition from season for existing rows so breakdowns aren't empty.
UPDATE `match` SET `competition` = `season` WHERE `competition` IS NULL;
