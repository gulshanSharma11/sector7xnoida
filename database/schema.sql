-- Sector7xNoida schema. Run this FIRST in phpMyAdmin (select your database > Import / SQL), then data.sql
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS reviews, society_providers, provider_phones, providers, societies, categories, users, login_log;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE categories (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  icon VARCHAR(16) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE societies (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(150) NOT NULL UNIQUE,
  legacy_slug VARCHAR(150) NOT NULL DEFAULT '',
  mongo_id VARCHAR(40) NOT NULL DEFAULT '',
  KEY idx_legacy (legacy_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE providers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ext_id VARCHAR(40) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  address VARCHAR(500) NOT NULL DEFAULT '',
  specialization VARCHAR(255) NOT NULL DEFAULT '',
  website VARCHAR(500) NOT NULL DEFAULT '',
  priority TINYINT UNSIGNED NOT NULL DEFAULT 5,
  KEY idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE provider_phones (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  provider_id INT UNSIGNED NOT NULL,
  display VARCHAR(40) NOT NULL,
  tel VARCHAR(25) NOT NULL,
  whatsapp VARCHAR(20) NULL,
  is_mobile TINYINT(1) NOT NULL DEFAULT 0,
  sort TINYINT UNSIGNED NOT NULL DEFAULT 0,
  KEY idx_provider (provider_id),
  CONSTRAINT fk_phone_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE society_providers (
  society_id INT UNSIGNED NOT NULL,
  category_id SMALLINT UNSIGNED NOT NULL,
  provider_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (society_id, category_id, provider_id),
  KEY idx_provider (provider_id),
  KEY idx_cat (category_id),
  CONSTRAINT fk_sp_soc FOREIGN KEY (society_id) REFERENCES societies(id) ON DELETE CASCADE,
  CONSTRAINT fk_sp_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
  CONSTRAINT fk_sp_prov FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  country_code VARCHAR(6) NOT NULL DEFAULT '91',
  mobile VARCHAR(15) NOT NULL,
  society_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  last_login_at DATETIME NOT NULL,
  UNIQUE KEY uq_mobile (country_code, mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  provider_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  pinned TINYINT(1) NOT NULL DEFAULT 0,
  comment VARCHAR(600) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_provider_user (provider_id, user_id),
  KEY idx_provider (provider_id),
  CONSTRAINT fk_review_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_log (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  mobile VARCHAR(15) NOT NULL,
  ip VARCHAR(45) NOT NULL,
  action VARCHAR(20) NOT NULL,
  ok TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  KEY idx_ip_time (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
