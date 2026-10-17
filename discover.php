<?php
require __DIR__ . '/includes/bootstrap.php';

$q    = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$cat  = in_array($_GET['categorie'] ?? '', CATEGORIES, true) ? $_GET['categorie'] : '';
$sort = is_string($_GET['sort'] ?? null) ? $_GET['sort'] : 'recent';
$defis = (new Challenge($db))->search($q, $cat, $sort);

$pageTitle = 'Découvrir';
include __DIR__ . '/includes/header.php';
?>
<div class="hero"><h1>Découvrir les défis</h1><p class="muted">Relevez un défi, votez, commentez et gagnez des badges.</p></div>

<form class="filters" method="get">
  <input type="search" name="q" placeholder="🔍 Rechercher un défi…" value="<?= e($q) ?>">
  <select name="categorie">
    <option value="">Toutes catégories</option>
    <?php foreach (CATEGORIES as $c): ?><option <?= $c === $cat ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
  </select>
  <select name="sort">
    <?php foreach (['recent' => 'Plus récents', 'deadline' => 'Date limite', 'popular' => 'Plus populaires', 'rated' => 'Mieux notés'] as $k => $l): ?>
      <option value="<?= $k ?>" <?= $k === $sort ? 'selected' : '' ?>><?= $l ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn">Filtrer</button>
</form>

<?php if (!$defis): ?>
  <div class="box">Aucun défi trouvé. <a href="mes_defis.php">Créez le premier !</a></div>
<?php else: ?>
<div class="grid">
  <?php foreach ($defis as $d): $late = is_expired($d['deadline']); ?>
  <article class="card">
    <?php if ($d['image']): ?><img class="cover" src="uploads/<?= e($d['image']) ?>" alt="">
    <?php else: ?><div class="ph">🎯</div><?php endif; ?>
    <div class="body">
      <div class="row"><span class="tag"><?= e($d['categorie']) ?></span>
        <span class="tag <?= $late ? 'late' : '' ?>"><?= $late ? 'Terminé' : "Jusqu'au " . date_fr($d['deadline']) ?></span></div>
      <h3><?= e($d['titre']) ?></h3>
      <p class="muted"><?= e(mb_strimwidth($d['description'], 0, 110, '…')) ?></p>
      <p class="muted">par <?= e($d['prenom'] . ' ' . $d['nom']) ?> · <?= (int)$d['nb_participants'] ?> participant(s)</p>
      <p><span class="stars"><?= stars($d['note_moyenne'] !== null ? (float)$d['note_moyenne'] : null) ?></span>
         <span class="muted"><?= $d['note_moyenne'] ?? '—' ?></span></p>
      <a class="btn small" href="defi.php?id=<?= (int)$d['id_challenge'] ?>" style="margin-top:auto;text-align:center">Voir le défi</a>
    </div>
  </article>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
