-- Run this ONCE (phpMyAdmin > your database > Import > choose this file > Go).
-- Adds the advertisement slots (sidebar rail + horizontal banner).
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS ads (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  link_url VARCHAR(500) NOT NULL DEFAULT '',
  placement ENUM('rail','banner') NOT NULL DEFAULT 'rail',
  active TINYINT(1) NOT NULL DEFAULT 1,
  sort SMALLINT NOT NULL DEFAULT 0,
  clicks INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  KEY idx_placement (placement, active, sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
