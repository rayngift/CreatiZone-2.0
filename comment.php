<?php
// API JSON : POST { action: add|delete|like, ... }
require __DIR__ . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['success' => false, 'message' => 'Méthode non supportée.'], 405);

$uid = require_login(true);
csrf_check(true);

$in = json_input();
$cm = new Comment($db);

switch ($in['action'] ?? '') {
    case 'add':
        $sid     = filter_var($in['id_submission'] ?? null, FILTER_VALIDATE_INT);
        $content = trim((string)($in['content'] ?? ''));
        if (!$sid || $content === '' || mb_strlen($content) > 1000) json_out(['success' => false, 'message' => 'Commentaire invalide.'], 422);
        if (!(new Submission($db))->find($sid))                     json_out(['success' => false, 'message' => 'Participation introuvable.'], 404);
        $id = $cm->add($sid, $uid, $content);
        json_out(['success' => true, 'comment' => [
            'id_comment' => $id,
            'author'     => $_SESSION['prenom'] . ' ' . $_SESSION['nom'],
            'initials'   => initials($_SESSION['prenom'], $_SESSION['nom']),
            'content'    => $content,            // brut : le JS utilise textContent
            'date'       => date('d/m/Y H:i'),
        ]]);

    case 'delete':
        $id = filter_var($in['id_comment'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || !$cm->delete($id, $uid)) json_out(['success' => false, 'message' => 'Suppression impossible.'], 403);
        json_out(['success' => true]);

    case 'like':
        $id  = filter_var($in['id_comment'] ?? null, FILTER_VALIDATE_INT);
        $res = $id ? $cm->toggleLike($id, $uid) : null;
        if (!$res) json_out(['success' => false, 'message' => 'Commentaire introuvable.'], 404);
        json_out(['success' => true] + $res);

    default:
        json_out(['success' => false, 'message' => 'Action inconnue.'], 400);
}
