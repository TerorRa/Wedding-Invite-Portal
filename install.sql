CREATE TABLE IF NOT EXISTS guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invite_code VARCHAR(64) NOT NULL UNIQUE,
    ticket_number VARCHAR(64) NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    personal_greeting TEXT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    telegram VARCHAR(255) NULL,
    guest_group VARCHAR(255) NULL,
    invitation_type VARCHAR(50) NOT NULL DEFAULT 'single',
    max_plus_one TINYINT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'invited',
    will_attend TINYINT NULL,
    plus_one TINYINT DEFAULT 0,
    plus_one_name VARCHAR(255) NULL,
    drink VARCHAR(255) NULL,
    partner_drink VARCHAR(255) NULL,
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

ALTER TABLE guests
    ADD COLUMN IF NOT EXISTS ticket_number VARCHAR(64) NULL UNIQUE AFTER invite_code;

ALTER TABLE guests
    ADD COLUMN IF NOT EXISTS personal_greeting TEXT NULL AFTER name;

ALTER TABLE guests
    ADD COLUMN IF NOT EXISTS invitation_type VARCHAR(50) NOT NULL DEFAULT 'single' AFTER guest_group;

ALTER TABLE guests
    ADD COLUMN IF NOT EXISTS partner_drink VARCHAR(255) NULL AFTER drink;

UPDATE guests
SET invitation_type = CASE
    WHEN invitation_type IS NULL OR invitation_type = '' THEN IF(max_plus_one = 1, 'single_plus_one', 'single')
    ELSE invitation_type
END;

CREATE TABLE IF NOT EXISTS dayprograms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_time VARCHAR(20) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY dayprograms_sort_order_unique (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO dayprograms (id, event_time, title, description, sort_order, is_active)
VALUES
    (1, '15:00', 'Збір гостей', 'Зустрічаємось у Петрівському Броварі.', 10, 1),
    (2, '16:00', 'Церемонія', 'Найважливіші слова цього дня.', 20, 1),
    (3, '16:40', 'Welcome', 'Легкі напої, фото та перші привітання.', 30, 1),
    (4, '17:30', 'Вечеря', 'Тости, музика і теплі розмови.', 40, 1),
    (5, '19:00', 'Перший танець', 'Момент, із якого починається вечірня магія.', 50, 1),
    (6, '23:00', 'Фінал вечора', 'Обійми, останні фото і тепле до зустрічі.', 60, 1)
ON DUPLICATE KEY UPDATE
    event_time = VALUES(event_time),
    title = VALUES(title),
    description = VALUES(description),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active);

CREATE TABLE IF NOT EXISTS invitelogs (
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
