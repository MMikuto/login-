CREATE TABLE IF NOT EXISTS users (
  id INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  department VARCHAR(150) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS department VARCHAR(150) NOT NULL DEFAULT 'General' AFTER full_name;

ALTER TABLE leave_requests
  DROP FOREIGN KEY fk_leave_requests_admin,
  MODIFY admin_id INT NULL,
  ADD COLUMN user_id INT NULL AFTER id,
  MODIFY status VARCHAR(30) NOT NULL DEFAULT 'pending',
  ADD COLUMN reviewed_by INT NULL AFTER status,
  ADD COLUMN reviewed_at TIMESTAMP NULL DEFAULT NULL AFTER reviewed_by,
  ADD KEY idx_leave_requests_user_status (user_id, status),
  ADD CONSTRAINT fk_leave_requests_user
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_leave_requests_reviewed_by
    FOREIGN KEY (reviewed_by) REFERENCES admin (id) ON DELETE SET NULL;

  UPDATE leave_requests SET status = 'pending';

INSERT IGNORE INTO users (username, password, full_name, department)
VALUES ('Mikuto', '$2y$10$op/.zS2.vuM/zCrlVqysY.h5Hi9aYWMnGrphqTygm6gmj5G2iJa8u', 'Mikuto', 'General');
