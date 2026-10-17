<?php
require __DIR__ . '/includes/bootstrap.php';
$uid = require_login();
$cm  = new Challenge($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $idc    = (int)($_POST['id_challenge'] ?? 0);
    try {
        if ($action === 'delete') {
            $old = $cm->find($idc);
            if (!$old || (int)$old['id_user'] !== $uid) throw new RuntimeException('Action non autorisée.');
            $cm->delete($idc, $uid);
            delete_image($old['image']);
            flash('success', 'Défi supprimé.');
        } elseif ($action === 'save') {
            $titre = trim($_POST['titre'] ?? ''); $desc = trim($_POST['description'] ?? '');
            $cat = $_POST['categorie'] ?? ''; $deadline = $_POST['deadline'] ?? '';
            $d = DateTime::createFromFormat('Y-m-d', $deadline);
            if ($titre === '' || $desc === '' || !$d || $d->format('Y-m-d') !== $deadline) throw new RuntimeException('Veuillez remplir tous les champs correctement.');
            if (!in_array($cat, CATEGORIES, true))  throw new RuntimeException('Catégorie invalide.');
            if (mb_strlen($titre) > 150)            throw new RuntimeException('Titre trop long (150 max).');
            if (mb_strlen($desc) > 2000)            throw new RuntimeException('Description trop longue (2000 max).');

            if ($idc) {
                $old = $cm->find($idc);
                if (!$old || (int)$old['id_user'] !== $uid) throw new RuntimeException('Action non autorisée.');
                $image = $old['image'];
                if ($new = save_image($_FILES['image'] ?? null)) { delete_image($image); $image = $new; }
                $cm->update($idc, $uid, $titre, $desc, $cat, $deadline, $image);
                flash('success', 'Défi modifié.');
            } else {
                $cm->create($uid, $titre, $desc, $cat, $deadline, save_image($_FILES['image'] ?? null));
                flash('success', 'Défi créé !');
            }
        }
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
    }
    redirect('mes_defis.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $c = $cm->find((int)$_GET['edit']);
    if ($c && (int)$c['id_user'] === $uid) $edit = $c;
}
$mine = $cm->byUser($uid);

$pageTitle = 'Mes défis';
include __DIR__ . '/includes/header.php';
?>
<section class="box">
  <h2><?= $edit ? 'Modifier le défi' : 'Créer un défi' ?></h2>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id_challenge" value="<?= (int)($edit['id_challenge'] ?? 0) ?>">
    <label>Titre *</label><input name="titre" maxlength="150" required value="<?= e($edit['titre'] ?? '') ?>">
    <label>Description *</label><textarea name="description" rows="4" maxlength="2000" required><?= e($edit['description'] ?? '') ?></textarea>
    <div class="row" style="align-items:flex-start">
      <div style="flex:1;min-width:200px"><label>Catégorie *</label>
        <select name="categorie" required><?php foreach (CATEGORIES as $c): ?>
          <option <?= ($edit['categorie'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
      <div style="flex:1;min-width:200px"><label>Date limite *</label>
        <input type="date" name="deadline" required value="<?= e($edit['deadline'] ?? '') ?>"></div>
    </div>
    <label>Image (optionnelle, max 3 Mo)</label><input type="file" name="image" accept="image/*">
    <p class="row" style="margin-top:16px"><button class="btn"><?= $edit ? 'Enregistrer' : 'Créer le défi' ?></button>
      <?php if ($edit): ?><a class="btn ghost" href="mes_defis.php">Annuler</a><?php endif; ?></p>
  </form>
</section>

<section class="box">
  <h2>Mes défis (<?= count($mine) ?>)</h2>
  <?php if (!$mine): ?><p class="muted">Vous n'avez pas encore créé de défi.</p><?php else: ?>
  <div style="overflow-x:auto"><table>
    <tr><th>Titre</th><th>Catégorie</th><th>Date limite</th><th>Participants</th><th></th></tr>
    <?php foreach ($mine as $d): ?>
    <tr>
      <td><a href="defi.php?id=<?= (int)$d['id_challenge'] ?>"><?= e($d['titre']) ?></a></td>
      <td><?= e($d['categorie']) ?></td><td><?= date_fr($d['deadline']) ?></td><td><?= (int)$d['nb_participants'] ?></td>
      <td class="row">
        <a class="btn small ghost" href="?edit=<?= (int)$d['id_challenge'] ?>">Modifier</a>
        <form method="post" class="inline" onsubmit="return confirm('Supprimer ce défi et toutes ses participations ?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete">
          <input type="hidden" name="id_challenge" value="<?= (int)$d['id_challenge'] ?>"><button class="btn small danger">Supprimer</button></form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table></div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
