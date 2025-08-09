-- Database schema and seed data for du_an_ai

CREATE DATABASE IF NOT EXISTS du_an_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE du_an_ai;

-- Core tables
CREATE TABLE IF NOT EXISTS majors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  parent_id INT NULL
);

CREATE TABLE IF NOT EXISTS skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  skill VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  gender VARCHAR(20) NOT NULL,
  age INT NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(50) NULL,
  address VARCHAR(255) NULL,
  school VARCHAR(255) NOT NULL,
  grad_year INT NOT NULL,
  avg_score DECIMAL(4,2) NOT NULL,
  interests JSON NULL,
  interest_desc TEXT NULL,
  skills JSON NULL,
  skill_levels JSON NULL,
  experience TEXT NULL,
  goals TEXT NOT NULL,
  initial_interests JSON NULL,
  ai_result MEDIUMTEXT NULL,
  source VARCHAR(50) DEFAULT 'web_form',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customer_interests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  major_id INT NOT NULL,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
  FOREIGN KEY (major_id) REFERENCES majors(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS customer_skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  skill_id INT NOT NULL,
  level VARCHAR(50) NOT NULL,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
  FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  raw_input JSON NULL,
  prompt MEDIUMTEXT NULL,
  ai_response MEDIUMTEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Seed basic reference data
INSERT INTO majors (name, parent_id) VALUES
  ('Công nghệ thông tin', NULL),
  ('Thiết kế đồ hoạ', NULL),
  ('Quản trị kinh doanh', NULL);

INSERT INTO skills (skill) VALUES
  ('Lập trình'),
  ('Phân tích dữ liệu'),
  ('Thiết kế'),
  ('Giao tiếp'),
  ('Quản lý thời gian');


