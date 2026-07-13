-- Migration 010: dedicated AI summary columns on `notifications`, mirroring
-- the pattern already used on `cards` and `appeal_cases`. Previously the AI
-- summary was only reachable by parsing the `data` JSON blob - a dedicated
-- column makes it directly queryable/displayable without that indirection.

SET @db := DATABASE();

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'notifications' AND COLUMN_NAME = 'ai_summary') = 0,
  'ALTER TABLE `notifications` ADD COLUMN `ai_summary` TEXT NULL AFTER `data`',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'notifications' AND COLUMN_NAME = 'ai_summary_status') = 0,
  'ALTER TABLE `notifications` ADD COLUMN `ai_summary_status` ENUM(''none'',''pending'',''completed'',''failed'') NOT NULL DEFAULT ''none'' AFTER `ai_summary`',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill existing rows from the `data` JSON so history isn't lost.
UPDATE `notifications`
SET `ai_summary` = JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.ai_summary')),
    `ai_summary_status` = 'completed'
WHERE `data` IS NOT NULL
  AND JSON_EXTRACT(`data`, '$.ai_summary') IS NOT NULL
  AND JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.ai_summary')) NOT IN ('null', '');
