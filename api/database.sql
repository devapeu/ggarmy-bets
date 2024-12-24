CREATE DATABASE IF NOT EXISTS `challonge`;

USE `challonge`;

CREATE TABLE IF NOT EXISTS `votes` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `match_id` INT NOT NULL,
    `tournament_id` VARCHAR(255) NOT NULL,
    `ip` VARCHAR(64) NOT NULL,
    `voted_for_player_id` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `one_vote_per_ip_match` (`match_id`, `ip`)
);