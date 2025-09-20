-- Schema for internship repository
CREATE DATABASE IF NOT EXISTS internship_repo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE internship_repo;

CREATE TABLE IF NOT EXISTS students_documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  roll_no VARCHAR(50) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  project_name VARCHAR(200) NOT NULL,
  company_name VARCHAR(200) NOT NULL,
  project_book_path VARCHAR(255) NOT NULL,
  certificate_path VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  user_id INT UNSIGNED NULL,
  CONSTRAINT fk_documents_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (roll_no),
  INDEX (email)
);

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','recruiter') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


