
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- texasholdem implementation : © <Hugo Frammery> <hugo@frammery.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- Example 1: create a standard "card" table to be used with the "Deck" tools (see example game "hearts"):

-- CREATE TABLE IF NOT EXISTS `card` (
--   `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--   `card_type` varchar(16) NOT NULL,
--   `card_type_arg` int(11) NOT NULL,
--   `card_location` varchar(16) NOT NULL,
--   `card_location_arg` int(11) NOT NULL,
--   PRIMARY KEY (`card_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- Example 2: add a custom field to the standard "player" table
-- ALTER TABLE `player` ADD `player_my_custom_field` INT UNSIGNED NOT NULL DEFAULT '0';

-- Table to store cards
CREATE TABLE IF NOT EXISTS `card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(16) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- Add fields for the number of tokens of each type in player's stock and in the betting area
ALTER TABLE `player` ADD `player_stock_token_white` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_stock_token_blue` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_stock_token_red` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_stock_token_green` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_stock_token_black` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_bet_token_white` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_bet_token_blue` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_bet_token_red` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_bet_token_green` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_bet_token_black` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `is_fold` BOOLEAN DEFAULT false;
ALTER TABLE `player` ADD `is_all_in` BOOLEAN DEFAULT false;
ALTER TABLE `player` ADD `wants_autoblinds` BOOLEAN DEFAULT false;

-- Table to store tokens bet on previous betting rounds
CREATE TABLE IF NOT EXISTS `token` (
  `token_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token_color` varchar(16) NOT NULL,
  `token_number` INT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
