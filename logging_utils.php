<?php
// Function to verify if logs table exists, if not create it
function ensureLogsTable($pdo)
{
    try {
        $pdo->query("SELECT 1 FROM activity_logs LIMIT 1");
    } catch (PDOException $e) {
        // Table doesn't exist, create it
        $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            details TEXT,
            ip_address VARCHAR(45),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $pdo->exec($sql);
    }
}

function logActivity($pdo, $userId, $action, $details = '')
{
    try {
        ensureLogsTable($pdo);
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $stmt->execute([$userId, $action, $details, $ip]);
    } catch (PDOException $e) {
        // Silently fail logging to not disrupt main flow, or log to file
        error_log("Logging failed: " . $e->getMessage());
    }
}
?>