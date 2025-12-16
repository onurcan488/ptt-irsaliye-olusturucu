DROP DATABASE IF EXISTS ptt_irsaliye;
CREATE DATABASE ptt_irsaliye;
USE ptt_irsaliye;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS waybills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    status ENUM('draft', 'completed') DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    prepared_by VARCHAR(100) DEFAULT '' COMMENT 'Listeyi Oluşturan',
    delivered_by VARCHAR(100) DEFAULT '' COMMENT 'Teslim Eden',
    received_by VARCHAR(100) DEFAULT '' COMMENT 'Teslim Alan',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    waybill_id INT NOT NULL,
    document_no VARCHAR(100) NOT NULL COMMENT 'Evrak No',
    document_type ENUM('TALİMAT', 'YAKALAMA', 'İLAMAT', 'SORUŞTURMA', 'MUHABERE', 'BAKANLIK MUHABERE', 'EMANET', 'ESAS', 'SORGU', 'DEĞİŞİK İŞ') NOT NULL DEFAULT 'MUHABERE',
    receiver_address TEXT NOT NULL,
    tracking_number VARCHAR(50) NOT NULL UNIQUE COMMENT 'Barkod No',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (waybill_id) REFERENCES waybills(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(64) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value) VALUES 
('institution_name', 'Araklı Adliyesi');

-- Varsayılan Kullanıcılar (Şifreler: password)
INSERT INTO users (username, password_hash, display_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sistem Yöneticisi', 'admin'),
('bassavcilik', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Cumhuriyet Başsavcılığı', 'user'),
('icramudurlugu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'İcra Müdürlüğü', 'user'),
('asliyeceza', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Asliye Ceza Mahkemesi', 'user'),
('sulhhukuk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sulh Hukuk Mahkemesi', 'user'),
('sulhceza', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sulh Ceza Hakimliği', 'user'),
('asliyehukuk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Asliye Hukuk Mahkemesi', 'user'),
('2asliyehukuk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2. Asliye Hukuk Mahkemesi', 'user'),
('2asliyeceza', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2. Asliye Ceza Mahkemesi', 'user');
