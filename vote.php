<?php
// API JSON : POST { id_submission, note } — l'utilisateur vient de la SESSION, jamais du client.
require __DIR__ . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['success' => false, 'message' => 'Méthode non supportée.'], 405);

$uid = require_login(true);
csrf_check(true);

$in   = json_input();
$sid  = filter_var($in['id_submission'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$note = filter_var($in['note'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
if (!$sid || !$note) json_out(['success' => false, 'message' => 'Note invalide (1 à 5).'], 422);

$subs = new Submission($db);
$sub  = $subs->find($sid);
if (!$sub)                          json_out(['success' => false, 'message' => 'Participation introuvable.'], 404);
if ((int)$sub['id_user'] === $uid)  json_out(['success' => false, 'message' => 'Vous ne pouvez pas voter pour votre propre participation.'], 403);

$stats = $subs->vote($sid, $uid, $note);
(new Badge($db))->afterVote($sid);

json_out(['success' => true, 'note' => $note, 'note_moyenne' => $stats['note_moyenne'], 'nb_votes' => (int)$stats['nb_votes']]);
