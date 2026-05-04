<?php
$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = (
    str_contains($host, 'localhost') ||
    str_starts_with($host, '127.') ||
    str_starts_with($host, '192.168.') ||
    PHP_SAPI === 'cli'
);

if ($isLocal) {
    // ---------- LOCAL (XAMPP) ----------
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'contacts');
} else {
    // ---------- PRODUCTION (contacts.zambezidiamond.co.zm) ----------
    define('DB_HOST', 'localhost');
    define('DB_USER', 'readiams_contacts');
    define('DB_PASS', 'contacts_zdg');
    define('DB_NAME', 'readiams_contacts');
}

// Auto-compute the URL prefix from the script's directory.
// /contacts/index.php  -> /contacts
// /index.php           -> '' (subdomain root)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
define('BASE_URL', ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/'));

define('APP_NAME', 'ZDG Contacts Directory');

// Single admin password for the contacts admin area
define('ADMIN_PASSWORD', 'contacts@zdg123');
