<?php
require __DIR__ . '/includes/bootstrap.php';
$uid  = require_login();
$user = (new User($db))->find($uid);
if (!$user) { $_SESSION = []; redirect('login.php'); }
$badges = (new Badge($db))->forUser($uid);
$mySubs = (new Submission($db))->byUser($uid);

$pageTitle = 'Mon profil';
include __DIR__ . '/includes/header.php';
?>
<section class="box row">
  <div class="avatar" style="width:64px;height:64px;font-size:1.4rem"><?= e(initials($user['prenom'], $user['nom'])) ?></div>
  <div><h1><?= e($user['prenom'] . ' ' . $user['nom']) ?></h1>
    <p class="muted"><?= e($user['email']) ?> · membre depuis le <?= date_fr($user['created_at']) ?></p></div>
</section>

<section class="box"><h2 style="margin-bottom:14px">Mes badges</h2>
  <div class="badges">
    <?php foreach ($badges as $b): ?>
    <div class="badge <?= $b['awarded_at'] ? '' : 'locked' ?>">
      <div class="ic"><?= e($b['icone']) ?></div><strong><?= e($b['nom']) ?></strong>
      <p class="muted"><?= e($b['description']) ?></p>
      <?php if ($b['awarded_at']): ?><p class="muted">Obtenu le <?= date_fr($b['awarded_at']) ?></p><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="box"><h2 style="margin-bottom:10px">Mes participations (<?= count($mySubs) ?>)</h2>
  <?php if (!$mySubs): ?><p class="muted">Aucune participation. <a href="discover.php">Trouvez un défi !</a></p><?php else: ?>
  <table><tr><th>Défi</th><th>Date</th><th>Note</th><th>Votes</th></tr>
    <?php foreach ($mySubs as $s): ?>
    <tr><td><a href="defi.php?id=<?= (int)$s['id_challenge'] ?>"><?= e($s['titre']) ?></a></td>
      <td><?= date_fr($s['created_at']) ?></td>
      <td><span class="stars"><?= stars($s['note_moyenne'] !== null ? (float)$s['note_moyenne'] : null) ?></span></td>
      <td><?= (int)$s['nb_votes'] ?></td></tr>
    <?php endforeach; ?>
  </table><?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
