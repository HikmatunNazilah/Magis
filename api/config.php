<?php
date_default_timezone_set('Asia/Jakarta');
// Configuration for Database Connection
$host = getenv('DB_HOST') ?: '103.30.147.68';
$user = getenv('DB_USER') ?: 'sekelikn_magis_usr';
$pass = getenv('DB_PASSWORD') ?: '[]pl--Xt3)0-!WP[';
$db   = getenv('DB_NAME') ?: 'sekelikn_magis_db';
$port = getenv('DB_PORT') ?: '3306';

$conn = new mysqli($host, $user, $pass, $db, $port);

// --- Session Database Handler (for Vercel persistence) ---
class DatabaseSessionHandler implements SessionHandlerInterface {
    private $mysqli;
    public function open($savePath, $sessionName): bool {
        global $conn;
        $this->mysqli = $conn;
        return true;
    }
    public function close(): bool { return true; }
    public function read($id): string|false {
        $stmt = $this->mysqli->prepare("SELECT payload FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) return base64_decode($row['payload']);
        }
        return "";
    }
    public function write($id, $data): bool {
        $last_activity = time();
        $payload = base64_encode($data);
        $stmt = $this->mysqli->prepare("REPLACE INTO sessions (id, payload, last_activity) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $id, $payload, $last_activity);
        return $stmt->execute();
    }
    public function destroy($id): bool {
        $stmt = $this->mysqli->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }
    public function gc($maxlifetime): int|false {
        $limit = time() - $maxlifetime;
        $stmt = $this->mysqli->prepare("DELETE FROM sessions WHERE last_activity < ?");
        $stmt->bind_param("i", $limit);
        if ($stmt->execute()) return $this->mysqli->affected_rows;
        return false;
    }
}

// Register handler
$handler = new DatabaseSessionHandler();
session_set_save_handler($handler, true);

// Check Connection
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set Charset
$conn->set_charset("utf8mb4");

// Start Session globally
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
