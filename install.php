<?php
require_once __DIR__ . '/includes/config.php';
mysqli_report(MYSQLI_REPORT_OFF);

$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    die('<h2>Cannot connect to MySQL</h2><p>' . htmlspecialchars($mysqli->connect_error)
        . '</p><p>Make sure database <b>' . DB_NAME . '</b> exists and user <b>'
        . DB_USER . '</b> has ALL PRIVILEGES.</p>');
}
$mysqli->set_charset('utf8mb4');

$sqlRaw = file_get_contents(__DIR__ . '/sql/schema.sql');
$statements = array_filter(array_map('trim', preg_split('/;\s*[\r\n]+/', $sqlRaw)));

$ok=0; $fail=[];
foreach ($statements as $stmt) {
    if ($stmt === '' || str_starts_with(ltrim($stmt), '--')) continue;
    if (!$mysqli->query($stmt)) $fail[] = $mysqli->error . ' :: ' . substr($stmt,0,120);
    else $ok++;
}

// Migrations for existing installs
$col = $mysqli->query("SHOW COLUMNS FROM contacts LIKE 'rank_level'");
if ($col && $col->num_rows === 0) {
    if ($mysqli->query("ALTER TABLE contacts ADD COLUMN rank_level TINYINT NOT NULL DEFAULT 10")) $ok++;
    else $fail[] = 'ALTER contacts add rank_level: ' . $mysqli->error;
}

echo '<h2>Contacts: installation complete</h2>';
echo '<p>Statements executed: <b>' . $ok . '</b></p>';
if ($fail) {
    echo '<h3>Warnings:</h3><pre>';
    foreach ($fail as $f) echo htmlspecialchars($f) . "\n";
    echo '</pre>';
}
echo '<p>Admin password: <b>' . ADMIN_PASSWORD . '</b> (change in <code>includes/config.php</code>)</p>';
echo '<p style="color:#c00"><b>Important:</b> delete this <code>install.php</code> file now.</p>';
echo '<p><a href="' . BASE_URL . '/">Open directory</a> &middot; <a href="' . BASE_URL . '/login.php">Admin login</a></p>';
