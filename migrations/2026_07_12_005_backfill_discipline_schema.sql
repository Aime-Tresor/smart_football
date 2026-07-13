-- Migration 005: Reproducible schema for tables that only existed ad hoc on
-- the live DB (ai_discipline_cases, appeal_cases, committee_members) plus a
-- proper link from a discipline case back to the card that generated it.
-- All CREATE TABLEs are IF NOT EXISTS - non-destructive against an existing
-- live database; columns are inferred from actual usage across
-- referee/record_card.php, fa_user/committee_appeals.php and
-- fa_user/discipline_committee_dashboard.php.

CREATE TABLE IF NOT EXISTS `committee_members` (
  `committee_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NULL,
  `phone` VARCHAR(30) NULL,
  `role` ENUM('Member','Chairperson','Secretary','Treasurer') NOT NULL DEFAULT 'Member',
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`committee_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ai_discipline_cases` (
  `case_id` INT(11) NOT NULL AUTO_INCREMENT,
  `team_id` INT(11) NOT NULL,
  `member_id` INT(11) NOT NULL,
  `card_id` INT(11) NULL,
  `offence_description` TEXT NOT NULL,
  `article_code` VARCHAR(20) NOT NULL,
  `sanction` VARCHAR(100) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`case_id`),
  KEY `idx_ai_discipline_cases_card` (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `appeal_cases` (
  `appeal_id` INT(11) NOT NULL AUTO_INCREMENT,
  `discipline_case_id` INT(11) NOT NULL,
  `team_id` INT(11) NOT NULL,
  `appeal_reason` TEXT NOT NULL,
  `appeal_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `decision_date` DATETIME NULL,
  `decision_reason` TEXT NULL,
  `hearing_date` DATETIME NULL,
  `hearing_notes` TEXT NULL,
  PRIMARY KEY (`appeal_id`),
  KEY `idx_appeal_cases_discipline_case` (`discipline_case_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- In case the tables already existed on the live DB (without card_id):
SET @db := DATABASE();

SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'ai_discipline_cases' AND COLUMN_NAME = 'card_id') = 0,
  'ALTER TABLE `ai_discipline_cases` ADD COLUMN `card_id` INT(11) NULL, ADD KEY `idx_ai_discipline_cases_card` (`card_id`)',
  'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
