CREATE TABLE IF NOT EXISTS advertisements (
  ad_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  image VARCHAR(255) NOT NULL,
  redirect_url VARCHAR(255) NULL,
  position ENUM('home_top','home_middle','home_bottom','popup','sidebar') DEFAULT 'home_top',
  start_date DATE NULL,
  end_date DATE NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
