<?php
require __DIR__ . '/includes/bootstrap.php';

$id = (int)($_GET['id'] ?? 0);
$ch = (new Challenge($db))->find($id);
if (!$ch) {
    http_response_code(404);
    $pageTitle = 'Introuvable';
    include __DIR__ . '/includes/header.php';
    echo '<div class="box">Défi introuvable. <a href="discover.php">Retour</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$uid      = current_user_id();
$subModel = new Submission($db);
$sort     = ($_GET['sort'] ?? '') === 'votes' ? 'votes' : 'date';
$subs     = $subModel->forChallenge($id, $sort, $uid);
$expired  = is_expired($ch['deadline']);
$mine     = $uid && $subModel->hasSubmitted($id, $uid);

$editing = null;
if ($uid && isset($_GET['edit'])) {
    $e = $subModel->find((int)$_GET['edit']);
    if ($e && (int)$e['id_user'] === $uid && (int)$e['id_challenge'] === $id) $editing = $e;
}
$showForm = $uid && !$expired && (!$mine || $editing);
$comments = new Comment($db);

$pageTitle = $ch['titre'];
include __DIR__ . '/includes/header.php';
?>
<section class="box">
  <div class="row"><span class="tag"><?= e($ch['categorie']) ?></span>
    <span class="tag <?= $expired ? 'late' : '' ?>"><?= $expired ? 'Terminé le ' : "Jusqu'au " ?><?= date_fr($ch['deadline']) ?></span></div>
  <h1 style="margin:10px 0"><?= e($ch['titre']) ?></h1>
  <?php if ($ch['image']): ?><img src="uploads/<?= e($ch['image']) ?>" alt="" style="max-width:100%;max-height:300px;border-radius:14px;margin-bottom:10px"><?php endif; ?>
  <p><?= nl2br(e($ch['description'])) ?></p>
  <p class="muted" style="margin-top:10px">Par <?= e($ch['prenom'] . ' ' . $ch['nom']) ?> · <?= (int)$ch['nb_participants'] ?> participant(s)</p>
</section>

<?php if ($showForm): ?>
<section class="box">
  <h2><?= $editing ? 'Modifier ma participation' : 'Participer à ce défi' ?></h2>
  <form method="post" action="participation.php" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="<?= $editing ? 'update' : 'store' ?>">
    <input type="hidden" name="id_challenge" value="<?= $id ?>">
    <?php if ($editing): ?><input type="hidden" name="id_submission" value="<?= (int)$editing['id_submission'] ?>"><?php endif; ?>
    <label>Description *</label>
    <textarea name="description" rows="4" maxlength="2000" required><?= e($editing['description'] ?? '') ?></textarea>
    <label>Lien (optionnel)</label>
    <input type="url" name="link" placeholder="https://…" value="<?= e($editing['link'] ?? '') ?>">
    <label>Image (optionnel, max 3 Mo)</label>
    <input type="file" name="image" accept="image/*">
    <p style="margin-top:16px" class="row">
      <button class="btn"><?= $editing ? 'Enregistrer' : 'Publier' ?></button>
      <?php if ($editing): ?><a class="btn ghost" href="defi.php?id=<?= $id ?>">Annuler</a><?php endif; ?>
    </p>
  </form>
</section>
<?php elseif (!$uid && !$expired): ?>
  <div class="box"><a href="login.php">Connectez-vous</a> pour participer, voter et commenter.</div>
<?php elseif ($mine && !$editing): ?>
  <div class="alert success">Vous avez déjà participé à ce défi.</div>
<?php elseif ($expired): ?>
  <div class="alert error">Ce défi est terminé : plus de nouvelles participations.</div>
<?php endif; ?>

<div class="row" style="justify-content:space-between;margin:10px 0 16px">
  <h2>Participations (<?= count($subs) ?>)</h2>
  <div class="row"><a class="btn small <?= $sort === 'date' ? '' : 'ghost' ?>" href="?id=<?= $id ?>">Récentes</a>
    <a class="btn small <?= $sort === 'votes' ? '' : 'ghost' ?>" href="?id=<?= $id ?>&sort=votes">Mieux notées</a></div>
</div>

<?php if (!$subs): ?><div class="box muted">Aucune participation pour l'instant.</div><?php endif; ?>

<?php foreach ($subs as $s): $isOwner = $uid === (int)$s['id_user']; $sid = (int)$s['id_submission']; ?>
<article class="box sub">
  <div class="row" style="justify-content:space-between">
    <div class="row"><div class="avatar"><?= e(initials($s['prenom'], $s['nom'])) ?></div>
      <div><strong><?= e($s['prenom'] . ' ' . $s['nom']) ?></strong><div class="muted"><?= date_fr($s['created_at']) ?></div></div></div>
    <?php if ($isOwner): ?>
    <div class="row">
      <a class="btn small ghost" href="defi.php?id=<?= $id ?>&edit=<?= $sid ?>">Modifier</a>
      <form method="post" action="participation.php" class="inline" onsubmit="return confirm('Supprimer cette participation ?')">
        <?= csrf_field() ?><input type="hidden" name="action" value="delete">
        <input type="hidden" name="id_challenge" value="<?= $id ?>"><input type="hidden" name="id_submission" value="<?= $sid ?>">
        <button class="btn small danger">Supprimer</button></form>
    </div>
    <?php endif; ?>
  </div>
  <p><?= nl2br(e($s['description'])) ?></p>
  <?php if ($s['image']): ?><img class="proof" src="uploads/<?= e($s['image']) ?>" alt=""><?php endif; ?>
  <?php if ($s['link']): ?><p><a href="<?= e($s['link']) ?>" target="_blank" rel="noopener noreferrer">🔗 <?= e($s['link']) ?></a></p><?php endif; ?>

  <div class="rating" data-id="<?= $sid ?>">
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <button type="button" class="star <?= $i <= (int)$s['ma_note'] ? 'on' : '' ?>" data-note="<?= $i ?>"
        <?= (!$uid || $isOwner) ? 'disabled' : '' ?> title="<?= $isOwner ? 'Vous ne pouvez pas voter pour vous-même' : 'Noter ' . $i . '/5' ?>">★</button>
    <?php endfor; ?>
    <span class="muted">Moyenne <strong class="avg"><?= $s['note_moyenne'] ?? '—' ?></strong> (<span class="nbv"><?= (int)$s['nb_votes'] ?></span> votes)</span>
  </div>

  <div class="comments">
    <div class="clist">
      <?php foreach ($comments->forSubmission($sid, $uid) as $c): ?>
      <div class="comment" data-id="<?= (int)$c['id_comment'] ?>">
        <div class="avatar"><?= e(initials($c['prenom'], $c['nom'])) ?></div>
        <div class="cb"><strong><?= e($c['prenom'] . ' ' . $c['nom']) ?></strong> <span class="muted"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></span>
          <div class="txt"><?= nl2br(e($c['content'])) ?></div>
          <div class="acts">
            <button type="button" class="like-btn <?= $c['liked'] ? 'liked' : '' ?>">❤️ <span><?= (int)$c['likes'] ?></span></button>
            <?php if ($uid === (int)$c['id_user']): ?><button type="button" class="del-btn">🗑 Supprimer</button><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($uid): ?>
    <form class="cform" data-id="<?= $sid ?>"><input type="text" maxlength="1000" placeholder="Écrire un commentaire…"><button class="btn small">Envoyer</button></form>
    <?php endif; ?>
  </div>
</article>
<?php endforeach; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
