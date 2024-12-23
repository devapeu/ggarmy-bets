CREATE DATABASE IF NOT EXISTS `challonge`;

USE `challonge`;

CREATE TABLE IF NOT EXISTS `votes` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `match_id` INT NOT NULL,
    `ip` VARCHAR(45) NOT NULL,
    `voted_for_player_id` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`match_id`) REFERENCES `matches`(`match_id`),
    UNIQUE KEY `one_vote_per_ip_match` (`match_id`, `ip`)
);