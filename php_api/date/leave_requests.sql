CREATE TABLE IF NOT EXISTS leave_requests (
  id INT NOT NULL AUTO_INCREMENT,
  admin_id INT NOT NULL,
  employee_name VARCHAR(150) NOT NULL,
  department VARCHAR(150) NOT NULL,
  leave_type VARCHAR(50) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  total_days INT UNSIGNED NOT NULL,
  reason TEXT NOT NULL,
  attachment_name VARCHAR(255) DEFAULT NULL,
  attachment_mime VARCHAR(100) DEFAULT NULL,
  attachment_data LONGBLOB DEFAULT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_leave_requests_admin_status (admin_id, status),
  CONSTRAINT fk_leave_requests_admin
    FOREIGN KEY (admin_id) REFERENCES admin (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
