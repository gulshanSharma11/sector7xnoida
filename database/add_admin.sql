-- Run this ONCE if your database already exists (from before the admin panel).
-- It does NOT delete any data. Safe to run even if some parts were already applied.
-- phpMyAdmin > your database > Import > choose this file > Go.
SET NAMES utf8mb4;

-- Admin panel needs to be able to INSERT new societies/categories/providers
-- without computing an id itself, so these three become auto-incrementing
-- (existing explicit ids are kept; the counter continues after the current max id).
ALTER TABLE categories MODIFY id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE societies  MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE providers  MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT;

-- Lets the admin "feature"/pin a review so it shows on top of the public list.
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS pinned TINYINT(1) NOT NULL DEFAULT 0 AFTER rating;
