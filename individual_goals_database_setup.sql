-- Individual Goals Database Setup
-- This script creates the necessary tables and structure for tracking individual goals

-- Create individual_goals table for tracking each goal separately
CREATE TABLE IF NOT EXISTS `individual_goals` (
  `goal_id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `player_id` int(11) DEFAULT NULL,
  `goal_minute` varchar(10) DEFAULT NULL,
  `goal_type` enum('regular','penalty','own_goal','free_kick') DEFAULT 'regular',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL COMMENT 'Referee ID who added the goal',
  PRIMARY KEY (`goal_id`),
  KEY `idx_match_team` (`match_id`, `team_id`),
  KEY `idx_player` (`player_id`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_individual_goals_match` FOREIGN KEY (`match_id`) REFERENCES `match` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_individual_goals_team` FOREIGN KEY (`team_id`) REFERENCES `team` (`team_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_individual_goals_player` FOREIGN KEY (`player_id`) REFERENCES `team_members` (`member_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_individual_goals_referee` FOREIGN KEY (`created_by`) REFERENCES `referee` (`referee_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create a trigger to automatically update match totals when individual goals are added/removed
DELIMITER $$

CREATE TRIGGER `update_match_totals_after_insert` 
AFTER INSERT ON `individual_goals`
FOR EACH ROW
BEGIN
    DECLARE team1_total INT DEFAULT 0;
    DECLARE team2_total INT DEFAULT 0;
    DECLARE match_team1_id INT;
    DECLARE match_team2_id INT;
    
    -- Get team IDs for the match
    SELECT team1_id, team2_id INTO match_team1_id, match_team2_id
    FROM `match` WHERE id = NEW.match_id;
    
    -- Count goals for team 1
    SELECT COUNT(*) INTO team1_total
    FROM individual_goals 
    WHERE match_id = NEW.match_id AND team_id = match_team1_id;
    
    -- Count goals for team 2
    SELECT COUNT(*) INTO team2_total
    FROM individual_goals 
    WHERE match_id = NEW.match_id AND team_id = match_team2_id;
    
    -- Update match table
    UPDATE `match` 
    SET team1_goal = team1_total, team2_goal = team2_total
    WHERE id = NEW.match_id;
END$$

CREATE TRIGGER `update_match_totals_after_delete` 
AFTER DELETE ON `individual_goals`
FOR EACH ROW
BEGIN
    DECLARE team1_total INT DEFAULT 0;
    DECLARE team2_total INT DEFAULT 0;
    DECLARE match_team1_id INT;
    DECLARE match_team2_id INT;
    
    -- Get team IDs for the match
    SELECT team1_id, team2_id INTO match_team1_id, match_team2_id
    FROM `match` WHERE id = OLD.match_id;
    
    -- Count goals for team 1
    SELECT COUNT(*) INTO team1_total
    FROM individual_goals 
    WHERE match_id = OLD.match_id AND team_id = match_team1_id;
    
    -- Count goals for team 2
    SELECT COUNT(*) INTO team2_total
    FROM individual_goals 
    WHERE match_id = OLD.match_id AND team_id = match_team2_id;
    
    -- Update match table
    UPDATE `match` 
    SET team1_goal = team1_total, team2_goal = team2_total
    WHERE id = OLD.match_id;
END$$

DELIMITER ;

-- Create a view for easy goal reporting
CREATE OR REPLACE VIEW `match_goals_summary` AS
SELECT 
    m.id as match_id,
    m.match_date,
    m.match_time,
    m.status,
    t1.name as team1_name,
    t2.name as team2_name,
    m.team1_goal,
    m.team2_goal,
    (SELECT COUNT(*) FROM individual_goals ig WHERE ig.match_id = m.id AND ig.team_id = m.team1_id) as team1_individual_goals,
    (SELECT COUNT(*) FROM individual_goals ig WHERE ig.match_id = m.id AND ig.team_id = m.team2_id) as team2_individual_goals
FROM `match` m
JOIN `team` t1 ON m.team1_id = t1.team_id
JOIN `team` t2 ON m.team2_id = t2.team_id;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_individual_goals_match_minute ON individual_goals(match_id, goal_minute);
CREATE INDEX IF NOT EXISTS idx_individual_goals_team_player ON individual_goals(team_id, player_id);

-- Insert some sample data (optional - remove if not needed)
-- INSERT INTO individual_goals (match_id, team_id, player_id, goal_minute, goal_type, description, created_by) 
-- VALUES 
-- (1, 4, 1, '15', 'regular', 'Great shot from outside the box', 1),
-- (1, 9, 2, '32', 'penalty', 'Penalty kick after foul in the box', 1);

-- Migration completed successfully
SELECT 'Individual goals database setup completed successfully!' as status;
