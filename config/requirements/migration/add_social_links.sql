CREATE TABLE IF NOT EXISTS social_links (
  social_id INT AUTO_INCREMENT PRIMARY KEY,
  platform VARCHAR(50) NOT NULL,              -- instagram, facebook, etc
  icon_class VARCHAR(100) NOT NULL,            -- fontawesome / boxicons class
  url VARCHAR(255) NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
