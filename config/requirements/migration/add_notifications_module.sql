CREATE TABLE IF NOT EXISTS notifications (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,

  recipient_type ENUM('user','admin') NOT NULL,
  user_id INT NULL,
  admin_id INT NULL,

  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,

  type ENUM('order','subscription','promotion','system') DEFAULT 'system',
  is_read TINYINT(1) DEFAULT 0,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_notification_user
    FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE,

  CONSTRAINT fk_notification_admin
    FOREIGN KEY (admin_id)
    REFERENCES admins(admin_id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;