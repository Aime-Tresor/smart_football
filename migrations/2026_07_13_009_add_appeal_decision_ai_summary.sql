-- Migration 009: AI summary of an appeal decision's `decision_reason`,
-- mirroring the card AI summary (cards.ai_summary) so committee decisions
-- get the same concise, consistent treatment shown to teams/admins.

SET @db := DATABASE();

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'appeal_cases' AND COLUMN_NAME = 'ai_summary') = 0,
  'ALTER TABLE `appeal_cases` ADD COLUMN `ai_summary` TEXT NULL',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'appeal_cases' AND COLUMN_NAME = 'ai_summary_status') = 0,
  'ALTER TABLE `appeal_cases` ADD COLUMN `ai_summary_status` ENUM(''none'',''pending'',''completed'',''failed'') NOT NULL DEFAULT ''none''',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'appeal_cases' AND COLUMN_NAME = 'ai_summary_error') = 0,
  'ALTER TABLE `appeal_cases` ADD COLUMN `ai_summary_error` VARCHAR(255) NULL',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'appeal_cases' AND COLUMN_NAME = 'ai_summary_generated_at') = 0,
  'ALTER TABLE `appeal_cases` ADD COLUMN `ai_summary_generated_at` TIMESTAMP NULL DEFAULT NULL',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
