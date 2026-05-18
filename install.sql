CREATE TABLE IF NOT EXISTS guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invite_code VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    telegram VARCHAR(255) NULL,
    guest_group VARCHAR(255) NULL,
    max_plus_one TINYINT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'invited',
    will_attend TINYINT NULL,
    plus_one TINYINT DEFAULT 0,
    plus_one_name VARCHAR(255) NULL,
    drink VARCHAR(255) NULL,
    food_notes TEXT NULL,
    need_transfer TINYINT DEFAULT 0,
    song_request VARCHAR(255) NULL,
    wish TEXT NULL,
    table_number VARCHAR(50) NULL,
    opened_at DATETIME NULL,
    answered_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invite_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    ip VARCHAR(100) NULL,
    user_agent TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admin_users (login, password_hash)
VALUES ('admin', '$2y$10$moVOZ7rd7V8.yF7GqZFjAuwqe.dS549ep79wqsC8SZURNeShqyOTu')
ON DUPLICATE KEY UPDATE login = VALUES(login);

INSERT INTO admin_users (login, password_hash)
VALUES ('r.teplov', '$2y$10$ZJIp5HL0woq4D3i1y/ATbeNzSDxLMWaWmQRydvBA5qKHOwWCAu5YG')
ON DUPLICATE KEY UPDATE login = VALUES(login);
