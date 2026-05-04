<?php
require_once __DIR__ . '/includes/auth.php';

$companies = fetch_all_assoc(q('SELECT * FROM companies ORDER BY id'));
$companyCode = strtoupper(trim($_GET['company'] ?? ''));
$qstr = trim($_GET['q'] ?? '');

$where = ['1=1']; $params=[]; $types='';
if ($companyCode) { $where[]='co.code=?'; $params[]=$companyCode; $types.='s'; }
if ($qstr !== '') {
    $where[] = '(c.name LIKE ? OR c.position LIKE ? OR c.phone LIKE ?)';
    $like = '%'.$qstr.'%';
    array_push($params,$like,$like,$like);
    $types .= 'sss';
}
$rows = fetch_all_assoc(q("SELECT c.*, co.code AS company_code, co.name AS company_name
    FROM contacts c JOIN companies co ON co.id=c.company_id
    WHERE ".implode(' AND ',$where)."
    ORDER BY co.id, c.rank_level, c.position, c.name", $params, $types));

// Group by company
$grouped = [];
foreach ($rows as $r) $grouped[$r['company_code']][] = $r;

$pageTitle='ZDG Contacts Directory';
$active='home';
include __DIR__.'/includes/header.php';
?>
<div class="landing-hero">
  <img src="<?= BASE_URL ?>/assets/images/zdg.png" alt="ZDG">
  <h1>ZDG Directory</h1>
  <p class="sub">Group Contacts Directory</p>
</div>

<div class="card">
  <form method="get" class="search">
    <div>
      <label>Search</label>
      <input name="q" value="<?= h($qstr) ?>" placeholder="Name, position, or phone">
    </div>
    <div>
      <label>Company</label>
      <select name="company">
        <option value="">All Companies</option>
        <?php foreach($companies as $c): ?>
          <option value="<?= h($c['code']) ?>" <?= $companyCode===$c['code']?'selected':'' ?>><?= h($c['code'].' - '.$c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="flex:0 0 auto">
      <label style="visibility:hidden">go</label>
      <button class="btn btn-primary">Search</button>
    </div>
    <?php if($qstr || $companyCode): ?>
      <div style="flex:0 0 auto">
        <label style="visibility:hidden">reset</label>
        <a class="btn btn-ghost" href="<?= BASE_URL ?>/">Reset</a>
      </div>
    <?php endif; ?>
  </form>
</div>

<?php if (!$rows): ?>
  <div class="card" style="margin-top:20px"><p class="muted">No contacts found<?= ($qstr||$companyCode)?' for your search':'' ?>.</p></div>
<?php else:
  foreach ($grouped as $code => $list):
    $co = current(array_filter($companies, fn($c)=>$c['code']===$code));
?>
  <div class="section-title">
    <img src="<?= BASE_URL ?>/assets/images/<?= h(strtolower($code)) ?>.png" alt="<?= h($code) ?>">
    <div>
      <h2><?= h($co['name'] ?? $code) ?></h2>
      <div class="sub"><?= count($list) ?> contact<?= count($list)===1?'':'s' ?></div>
    </div>
  </div>
  <div class="grid grid-2">
    <?php foreach ($list as $c): ?>
      <div class="contact-card">
        <div class="head">
          <img src="<?= BASE_URL ?>/assets/images/<?= h(strtolower($c['company_code'])) ?>.png" alt="">
          <div>
            <div class="position-main"><?= h($c['position']) ?></div>
            <div class="name-sub"><?= h($c['name']) ?></div>
          </div>
        </div>
        <div class="phone"><?= h($c['phone']) ?></div>
        <div class="actions">
          <a class="btn btn-sm btn-primary" href="<?= h(tel_link($c['phone'])) ?>">Call</a>
          <a class="btn btn-sm btn-wa" target="_blank" href="<?= h(wa_link($c['phone'])) ?>">WhatsApp</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; endif; ?>

<?php include __DIR__.'/includes/footer.php'; ?>
