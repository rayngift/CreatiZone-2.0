<?php
require __DIR__ . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('discover.php');

$uid = require_login();
csrf_check();

$subs   = new Submission($db);
$cid    = (int)($_POST['id_challenge'] ?? 0);
$action = $_POST['action'] ?? '';
$back   = "defi.php?id=$cid";
$challenge = (new Challenge($db))->find($cid);
if (!$challenge) { flash('error', 'Défi introuvable.'); redirect('discover.php'); }

try {
    if ($action === 'store') {
        if (is_expired($challenge['deadline']))   throw new RuntimeException('Ce défi est terminé.');
        if ($subs->hasSubmitted($cid, $uid))      throw new RuntimeException('Vous avez déjà participé à ce défi.');
        [$desc, $link] = validate_submission();
        $image = save_image($_FILES['image'] ?? null);
        try {
            $id = $subs->create($cid, $uid, $desc, $image, $link);
        } catch (PDOException $ex) {
            delete_image($image);
            throw new RuntimeException($ex->getCode() === '23000' ? 'Vous avez déjà participé à ce défi.' : 'Erreur lors de la publication.');
        }
        flash('success', 'Participation publiée !');
        foreach ((new Badge($db))->afterSubmission($uid) as $b) flash('success', "Nouveau badge : $b");

    } elseif ($action === 'update' || $action === 'delete') {
        $sub = $subs->find((int)($_POST['id_submission'] ?? 0));
        if (!$sub || (int)$sub['id_user'] !== $uid) throw new RuntimeException('Action non autorisée.');

        if ($action === 'delete') {
            $subs->delete((int)$sub['id_submission'], $uid);
            delete_image($sub['image']);
            flash('success', 'Participation supprimée.');
        } else {
            [$desc, $link] = validate_submission();
            $image = $sub['image'];
            if ($new = save_image($_FILES['image'] ?? null)) { delete_image($image); $image = $new; }
            $subs->update((int)$sub['id_submission'], $uid, $desc, $image, $link);
            flash('success', 'Participation mise à jour.');
        }
    }
} catch (RuntimeException $ex) {
    flash('error', $ex->getMessage());
}
redirect($back);

function validate_submission(): array
{
    $desc = trim($_POST['description'] ?? '');
    $link = trim($_POST['link'] ?? '') ?: null;
    if ($desc === '')                 throw new RuntimeException('La description est obligatoire.');
    if (mb_strlen($desc) > 2000)      throw new RuntimeException('Description trop longue (max 2000 caractères).');
    if ($link && (!filter_var($link, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $link))) {
        throw new RuntimeException('Le lien doit commencer par http:// ou https://.');
    }
    return [$desc, $link];
}
