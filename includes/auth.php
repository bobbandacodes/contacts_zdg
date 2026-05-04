<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

function is_admin(): bool { return !empty($_SESSION['contacts_admin']); }
function require_admin(): void {
    if (!is_admin()) { header('Location: ' . BASE_URL . '/login.php'); exit; }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
        http_response_code(400); die('Invalid CSRF token.');
    }
}
function flash(string $key, ?string $msg = null): ?string {
    if ($msg !== null) { $_SESSION['flash'][$key] = $msg; return null; }
    $m = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $m;
}
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function normalize_phone(string $raw): string {
    $d = preg_replace('/\D+/', '', $raw);
    if ($d === '') return '';
    if (str_starts_with($d, '00')) $d = substr($d, 2);
    if (str_starts_with($d, '0'))  $d = '260' . substr($d, 1);
    return $d;
}
function wa_link(string $raw): string  { return 'https://wa.me/' . normalize_phone($raw); }
function tel_link(string $raw): string { return 'tel:+' . normalize_phone($raw); }
