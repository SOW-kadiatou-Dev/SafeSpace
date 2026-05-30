CREATE DATABASE IF NOT EXISTS videtoncoeur CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE videtoncoeur;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  is_premium TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS posts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  pseudo VARCHAR(80) NOT NULL,
  content TEXT NOT NULL,
  mood VARCHAR(50) NULL,
  status ENUM('published', 'blocked') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS comments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT NOT NULL,
  pseudo VARCHAR(80) NOT NULL,
  content TEXT NOT NULL,
  status ENUM('published', 'blocked') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reports (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  target_type ENUM('post', 'comment', 'message') NOT NULL,
  target_id BIGINT NOT NULL,
  reason VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inspirations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  quote TEXT NOT NULL,
  author VARCHAR(120) NULL,
  category VARCHAR(80) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS private_messages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  conversation_key VARCHAR(120) NOT NULL,
  sender_pseudo VARCHAR(80) NOT NULL,
  content TEXT NOT NULL,
  status ENUM('published', 'blocked') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_conversation_key (conversation_key)
);

INSERT INTO inspirations (quote, author, category)
SELECT * FROM (
  SELECT 'Respire. Tu n\'as pas besoin d\'être parfait(e) pour avancer.', 'SafeSpace', 'self-kindness' UNION ALL
  SELECT 'Même une petite victoire compte.', 'SafeSpace', 'motivation' UNION ALL
  SELECT 'Demander de l\'aide est un acte de courage.', 'SafeSpace', 'support'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM inspirations);
