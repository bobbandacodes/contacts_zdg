<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();

$companies = fetch_all_assoc(q('SELECT * FROM companies ORDER BY id'));
$companyCode = strtoupper(trim($_GET['company'] ?? ''));
$selected = $companyCode ? fetch_one(q('SELECT * FROM companies WHERE code=?',[$companyCode],'s')) : null;

$err = null;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $cid = (int)$_POST['company_id'];
        $pos = trim($_POST['position'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $rank = max(1, min(15, (int)($_POST['rank_level'] ?? 10)));
        if (!$cid || !$pos || !$name || !$phone) $err='All fields are required.';
        elseif (normalize_phone($phone)==='') $err='Phone must contain digits.';
        else {
            q('INSERT INTO contacts (company_id,position,name,phone,rank_level) VALUES (?,?,?,?,?)',
              [$cid,$pos,$name,$phone,$rank],'isssi');
            flash('success','Contact added.');
            $back = fetch_one(q('SELECT code FROM companies WHERE id=?',[$cid],'i'));
            header('Location: '.BASE_URL.'/admin.php?company='.($back['code'] ?? '')); exit;
        }
    } elseif ($action === 'update') {
        $id = (int)$_POST['id'];
        $pos = trim($_POST['position'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $rank = max(1, min(15, (int)($_POST['rank_level'] ?? 10)));
        if (!$id || !$pos || !$name || !$phone) $err='All fields are required.';
        elseif (normalize_phone($phone)==='') $err='Phone must contain digits.';
        else {
            q('UPDATE contacts SET position=?, name=?, phone=?, rank_level=? WHERE id=?',
              [$pos,$name,$phone,$rank,$id],'sssii');
            flash('success','Contact updated.');
            header('Location: '.BASE_URL.'/admin.php?company='.urlencode($companyCode)); exit;
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        q('DELETE FROM contacts WHERE id=?',[$id],'i');
        flash('success','Contact removed.');
        header('Location: '.BASE_URL.'/admin.php?company='.urlencode($companyCode)); exit;
    }
}

$where='1=1'; $params=[]; $types='';
if ($selected) { $where='c.company_id=?'; $params[]=$selected['id']; $types='i'; }
$contacts = fetch_all_assoc(q("SELECT c.*, co.code AS company_code, co.name AS company_name
    FROM contacts c JOIN companies co ON co.id=c.company_id
    WHERE $where ORDER BY co.id, c.rank_level, c.position, c.name", $params, $types));

$editId = (int)($_GET['edit'] ?? 0);
$editing = $editId ? fetch_one(q('SELECT * FROM contacts WHERE id=?',[$editId],'i')) : null;

$pageTitle='Manage Contacts'; $active='admin';
include __DIR__.'/includes/header.php';
?>
<h1>Manage Contacts</h1>
<?php if($err): ?><div class="alert alert-error"><?= h($err) ?></div><?php endif; ?>

<div class="card">
  <form method="get">
    <label>Filter by Company</label>
    <select name="company" onchange="this.form.submit()">
      <option value="">All Companies</option>
      <?php foreach($companies as $c): ?>
        <option value="<?= h($c['code']) ?>" <?= $companyCode===$c['code']?'selected':'' ?>><?= h($c['code'].' - '.$c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<div class="card" style="margin-top:18px">
  <h2><?= $editing ? 'Edit Contact' : 'Add Contact' ?></h2>
  <form method="post" class="grid grid-2">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <?php if ($editing): ?>
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
    <?php else: ?>
      <input type="hidden" name="action" value="add">
    <?php endif; ?>
    <div>
      <label>Company</label>
      <?php if ($editing):
        $co = fetch_one(q('SELECT code,name FROM companies WHERE id=?',[$editing['company_id']],'i')); ?>
        <input value="<?= h($co['code'].' - '.$co['name']) ?>" disabled>
      <?php else: ?>
        <select name="company_id" required>
          <option value="">-- choose --</option>
          <?php foreach($companies as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= ($selected && $selected['id']==$c['id'])?'selected':'' ?>><?= h($c['code'].' - '.$c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </div>
    <div>
      <label>Position</label>
      <input name="position" required value="<?= h($editing['position'] ?? '') ?>" placeholder="e.g. HR Manager">
    </div>
    <div>
      <label>Full Name</label>
      <input name="name" required value="<?= h($editing['name'] ?? '') ?>">
    </div>
    <div>
      <label>Phone Number</label>
      <input name="phone" required value="<?= h($editing['phone'] ?? '') ?>" placeholder="0977 123 456">
    </div>
    <div>
      <label>Rank Level (1 = highest, 15 = lowest)</label>
      <?php
        $rankHints = [
          1=>'CEO', 2=>'Director', 3=>'Director',
          4=>'Senior Manager', 5=>'Manager', 6=>'Manager',
          7=>'Assistant Manager', 8=>'Supervisor', 9=>'Supervisor',
          10=>'Officer', 11=>'Officer', 12=>'Senior Staff',
          13=>'Staff', 14=>'Staff', 15=>'Other'
        ];
        $currentRank = (int)($editing['rank_level'] ?? 10);
      ?>
      <select name="rank_level">
        <?php for($i=1;$i<=15;$i++): ?>
          <option value="<?= $i ?>" <?= $currentRank===$i?'selected':'' ?>>
            Level <?= $i ?> &mdash; <?= h($rankHints[$i]) ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>
    <div style="grid-column:1/-1;display:flex;gap:10px">
      <button class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Add Contact' ?></button>
      <?php if ($editing): ?>
        <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin.php?company=<?= h($companyCode) ?>">Cancel</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card" style="margin-top:18px">
  <h2>All Contacts<?= $selected ? ' &mdash; '.h($selected['name']) : '' ?></h2>
  <div class="divider"></div>
  <?php if (!$contacts): ?>
    <p class="muted">No contacts yet.</p>
  <?php else: ?>
    <div class="grid grid-2">
      <?php foreach($contacts as $c): ?>
        <div class="contact-card">
          <div class="head">
            <img src="<?= BASE_URL ?>/assets/images/<?= h(strtolower($c['company_code'])) ?>.png" alt="">
            <div>
              <div class="position-main"><?= h($c['position']) ?> <span style="font-size:.7rem;color:var(--muted);font-weight:500">(L<?= (int)$c['rank_level'] ?>)</span></div>
              <div class="name-sub"><?= h($c['name']) ?> &middot; <?= h($c['company_code']) ?></div>
            </div>
          </div>
          <div class="phone"><?= h($c['phone']) ?></div>
          <div class="actions">
            <a class="btn btn-sm btn-primary" href="<?= h(tel_link($c['phone'])) ?>">Call</a>
            <a class="btn btn-sm btn-wa" target="_blank" href="<?= h(wa_link($c['phone'])) ?>">WhatsApp</a>
            <a class="btn btn-sm btn-ghost" href="<?= BASE_URL ?>/admin.php?company=<?= h($companyCode) ?>&edit=<?= (int)$c['id'] ?>">Edit</a>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete this contact?')">
              <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button class="btn btn-sm btn-danger">Delete</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__.'/includes/footer.php'; ?>
