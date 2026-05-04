<?php
require_once __DIR__ . '/auth.php';
$active = $active ?? '';
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($pageTitle ?? APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head><body>
<header class="topbar">
  <a class="brand" href="<?= BASE_URL ?>/">
    <span class="logo"><img src="<?= BASE_URL ?>/assets/images/zdg.png" alt="ZDG"></span>
    <span>Contacts Directory</span>
  </a>
  <nav>
    <a href="<?= BASE_URL ?>/" class="<?= $active==='home'?'active':'' ?>">Directory</a>
    <?php if (is_admin()): ?>
      <a href="<?= BASE_URL ?>/admin.php" class="<?= $active==='admin'?'active':'' ?>">Manage</a>
      <a href="<?= BASE_URL ?>/logout.php">Logout</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/login.php" class="<?= $active==='login'?'active':'' ?>">Admin Login</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
<?php
foreach (['error','success'] as $k) {
    $m = flash($k);
    if ($m) echo '<div class="alert alert-'.$k.'">'.h($m).'</div>';
}
