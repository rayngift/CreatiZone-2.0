<?php
require __DIR__ . '/includes/bootstrap.php';
$top = (new Submission($db))->leaderboard(20);
$pageTitle = 'Classement';
include __DIR__ . '/includes/header.php';
?>
<div class="hero"><h1>Classement</h1><p class="muted">Les participations les mieux notées.</p></div>
<section class="box">
<?php if (!$top): ?><p class="muted">Aucun vote pour l'instant.</p><?php else: ?>
  <table><tr><th>#</th><th>Participant</th><th>Défi</th><th>Note</th><th>Votes</th></tr>
  <?php foreach ($top as $i => $r): ?>
    <tr><td><?= ['🥇', '🥈', '🥉'][$i] ?? $i + 1 ?></td>
      <td><?= e($r['prenom'] . ' ' . $r['nom']) ?></td>
      <td><a href="defi.php?id=<?= (int)$r['id_challenge'] ?>"><?= e($r['titre']) ?></a></td>
      <td><span class="stars"><?= stars((float)$r['note_moyenne']) ?></span> <?= e($r['note_moyenne']) ?></td>
      <td><?= (int)$r['nb_votes'] ?></td></tr>
  <?php endforeach; ?></table>
<?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
