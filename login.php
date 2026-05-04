<?php
require_once __DIR__ . '/includes/auth.php';
$err = null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $p = $_POST['password'] ?? '';
    if (hash_equals(ADMIN_PASSWORD, $p)) {
        $_SESSION['contacts_admin'] = 1;
        header('Location: '.BASE_URL.'/admin.php'); exit;
    }
    $err = 'Wrong password.';
}
$pageTitle='Admin Login'; $active='login';
include __DIR__.'/includes/header.php';
?>
<div class="auth-wrap"><div class="auth-card">
  <img src="<?= BASE_URL ?>/assets/images/zdg.png" alt="ZDG" style="display:block;margin:0 auto 12px;width:90px;height:90px;object-fit:contain">
  <h2>Admin Login</h2>
  <p class="muted" style="text-align:center">Enter the admin password to manage contacts</p>
  <?php if($err): ?><div class="alert alert-error"><?= h($err) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <label>Password</label>
    <input type="password" name="password" required autofocus>
    <div style="margin-top:18px"><button class="btn btn-primary" style="width:100%">Sign In</button></div>
  </form>
  <p style="text-align:center;margin-top:14px"><a href="<?= BASE_URL ?>/">Back to directory</a></p>
</div></div>
<?php include __DIR__.'/includes/footer.php'; ?>
