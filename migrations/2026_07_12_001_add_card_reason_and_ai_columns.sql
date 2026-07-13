-- Migration 001: Card Reason Title + AI Summary fields on `cards`
-- Safe to re-run: guards each ALTER with an information_schema check.

SET @db := DATABASE();

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cards' AND COLUMN_NAME = 'card_reason_title') = 0,
  'ALTER TABLE `cards` ADD COLUMN `card_reason_title` VARCHAR(150) NOT NULL DEFAULT '''' AFTER `card_type`',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cards' AND COLUMN_NAME = 'card_reason_detail') = 0,
  'ALTER TABLE `cards` ADD COLUMN `card_reason_detail` TEXT NULL AFTER `card_reason_title`',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cards' AND COLUMN_NAME = 'ai_summary') = 0,
  'ALTER TABLE `cards` ADD COLUMN `ai_summary` TEXT NULL AFTER `card_reason_detail`',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cards' AND COLUMN_NAME = 'ai_summary_status') = 0,
  'ALTER TABLE `cards` ADD COLUMN `ai_summary_status` ENUM(''none'',''pending'',''completed'',''failed'') NOT NULL DEFAULT ''none'' AFTER `ai_summary`',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cards' AND COLUMN_NAME = 'ai_summary_error') = 0,
  'ALTER TABLE `cards` ADD COLUMN `ai_summary_error` VARCHAR(255) NULL AFTER `ai_summary_status`',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cards' AND COLUMN_NAME = 'ai_summary_generated_at') = 0,
  'ALTER TABLE `cards` ADD COLUMN `ai_summary_generated_at` TIMESTAMP NULL DEFAULT NULL AFTER `ai_summary_error`',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cards' AND COLUMN_NAME = 'updated_at') = 0,
  'ALTER TABLE `cards` ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cards' AND COLUMN_NAME = 'deleted_at') = 0,
  'ALTER TABLE `cards` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
