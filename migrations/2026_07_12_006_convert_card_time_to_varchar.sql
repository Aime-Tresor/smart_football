-- Migration 006: `cards.card_time` was a TIME column, but every caller
-- actually uses it to label a match minute (e.g. "45", "90+3"), not a
-- time-of-day - a TIME type can't represent minutes past 59 without
-- misleading hour rollover. Store it as a short label instead.

ALTER TABLE `cards` MODIFY COLUMN `card_time` VARCHAR(10) NULL DEFAULT NULL;
