-- MySQL 8+ schema for duan_ai
CREATE DATABASE IF NOT EXISTS duan_ai DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE duan_ai;

-- Tables
CREATE TABLE IF NOT EXISTS majors (
  id VARCHAR(50) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  tags JSON NULL,
  weight_interests JSON NULL,
  weight_skills JSON NULL,
  weight_grades JSON NULL,
  constraints_json JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS profiles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  age INT NULL,
  location VARCHAR(100) NULL,
  finance_level ENUM('low','medium','high') NULL,
  interests JSON NULL,
  skills JSON NULL,
  grades JSON NULL,
  career_goals JSON NULL,
  personality JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS recommendations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  profile_id BIGINT UNSIGNED NOT NULL,
  ai_reason LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reco_profile FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS recommendation_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  recommendation_id BIGINT UNSIGNED NOT NULL,
  major_id VARCHAR(50) NOT NULL,
  score DECIMAL(10,2) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_items_reco FOREIGN KEY (recommendation_id) REFERENCES recommendations(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_major FOREIGN KEY (major_id) REFERENCES majors(id)
) ENGINE=InnoDB;

-- Indexes
CREATE INDEX idx_reco_profile ON recommendations(profile_id);
CREATE INDEX idx_items_reco ON recommendation_items(recommendation_id);
CREATE INDEX idx_items_major ON recommendation_items(major_id);


