<?php
require_once __DIR__ . '/config.php';

function db(): mysqli {
    static $mysqli = null;
    if ($mysqli === null) {
        $mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($mysqli->connect_errno) {
            die('DB connection failed: ' . htmlspecialchars($mysqli->connect_error)
                . '<br>Run <a href="' . BASE_URL . '/install.php">install.php</a> first.');
        }
        $mysqli->set_charset('utf8mb4');
    }
    return $mysqli;
}

function q(string $sql, array $params = [], string $types = ''): mysqli_stmt {
    $stmt = db()->prepare($sql);
    if (!$stmt) throw new RuntimeException('Prepare failed: ' . db()->error);
    if ($params) {
        if ($types === '') $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}
function fetch_all_assoc(mysqli_stmt $stmt): array {
    $r = $stmt->get_result();
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}
function fetch_one(mysqli_stmt $stmt): ?array {
    $r = $stmt->get_result();
    return $r ? ($r->fetch_assoc() ?: null) : null;
}
