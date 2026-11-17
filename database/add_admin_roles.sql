-- Run this ONCE (phpMyAdmin > your database > Import > choose this file > Go).
-- Adds super-admin / sub-admin accounts. Safe to run even if some parts were already applied.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('super','sub') NOT NULL DEFAULT 'sub',
  society_id INT UNSIGNED NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  KEY idx_society (society_id),
  CONSTRAINT fk_admin_society FOREIGN KEY (society_id) REFERENCES societies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One super admin, seeded from the old single admin password ("S7x@Admin2026!").
-- Username: admin   Password: S7x@Admin2026!  (change it from the admin panel after first login)
INSERT IGNORE INTO admins (username, password_hash, role, society_id, active, created_at)
VALUES ('admin', '$2y$10$CrOrMQuZX9U/mOtWzF99fOT9p2aT0yGKz8.7gAS4US6xBbyYJVVHi', 'super', NULL, 1, NOW());
