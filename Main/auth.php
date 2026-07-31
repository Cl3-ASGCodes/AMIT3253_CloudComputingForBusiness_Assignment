<?php
// Sessions are stored in the database (not local disk) so that any EC2
// instance behind an ALB/ASG can read a session written by a different
// instance - PHP's default file-based sessions only live on the instance
// that created them, so a request an ALB routes to a different instance
// would otherwise see the user as logged out. See schema.sql's
// `sessions` table.
class DbSessionHandler implements SessionHandlerInterface {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function open($path, $name): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string {
        $stmt = $this->conn->prepare('SELECT data FROM sessions WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $row->data : '';
    }

    public function write($id, $data): bool {
        $now = time();
        $stmt = $this->conn->prepare('INSERT INTO sessions (id, data, last_activity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data), last_activity = VALUES(last_activity)');
        return $stmt->execute([$id, $data, $now]);
    }

    public function destroy($id): bool {
        $stmt = $this->conn->prepare('DELETE FROM sessions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function gc($maxLifetime): int|false {
        $threshold = time() - $maxLifetime;
        $stmt = $this->conn->prepare('DELETE FROM sessions WHERE last_activity < ?');
        $stmt->execute([$threshold]);
        return $stmt->rowCount();
    }
}

session_set_save_handler(new DbSessionHandler($conn), true);
register_shutdown_function('session_write_close');
session_start();

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_user_name() {
    return $_SESSION['user_name'] ?? null;
}

function current_user_is_admin() {
    return !empty($_SESSION['is_admin']);
}

function require_login() {
    if (!current_user_id()) {
        header('Location: login.php');
        exit;
    }

    // A session can outlive the account it points to (e.g. the account was
    // deleted or the database was reset). Catch that here instead of letting
    // every write below fail a foreign-key check silently.
    global $conn;
    $stmt = $conn->prepare('SELECT id FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $exists = $stmt->fetch();

    if (!$exists) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

function require_admin() {
    require_login();
    if (!current_user_is_admin()) {
        http_response_code(403);
        die('Forbidden: admin access only.');
    }
}
