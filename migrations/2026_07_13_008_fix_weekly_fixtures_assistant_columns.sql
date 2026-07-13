-- Migration 008: fix "Set Referee" being completely broken.
--
-- Root cause: assistant referees were removed from the UI and the project
-- even has a committed `remove_assistant_referees_migration.sql` documenting
-- dropping `weekly_fixtures.assistant1`/`assistant2` - but that migration was
-- never actually applied to this database. The columns are still
-- `NOT NULL` with no default, and `fa_user/controls/setReferee.php`'s INSERT
-- never supplies them, so under this server's `STRICT_TRANS_TABLES` sql_mode
-- every single "Set Referee" submission throws
-- "Field 'assistant1' doesn't have a default value" and silently fails.
--
-- Fix chosen: give the columns a default (non-destructive, keeps existing
-- historical data) rather than DROP COLUMN (destructive/irreversible).
-- If you want the full cleanup described in
-- remove_assistant_referees_migration.sql, that's a separate, deliberate
-- follow-up - not bundled into this bugfix.

SET @db := DATABASE();

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'weekly_fixtures' AND COLUMN_NAME = 'assistant1'
     AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL) > 0,
  'ALTER TABLE `weekly_fixtures` MODIFY COLUMN `assistant1` INT(11) NOT NULL DEFAULT 0',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'weekly_fixtures' AND COLUMN_NAME = 'assistant2'
     AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL) > 0,
  'ALTER TABLE `weekly_fixtures` MODIFY COLUMN `assistant2` INT(11) NOT NULL DEFAULT 0',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
