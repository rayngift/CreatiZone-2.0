<?php $uid = current_user_id(); $pageTitle = $pageTitle ?? 'CreatiZone'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e(csrf_token()) ?>">
  <title><?= e($pageTitle) ?> — CreatiZone</title>
  <link rel="icon" type="image/png" href="assets/logof.png">
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php"><img src="assets/logof.png" alt=""> CreatiZone</a>
  <nav>
    <a href="discover.php">Découvrir</a>
    <a href="classement.php">Classement</a>
    <?php if ($uid): ?>
      <a href="mes_defis.php">Mes défis</a>
      <a href="profile.php">👤 <?= e($_SESSION['prenom'] ?? 'Profil') ?></a>
      <form method="post" action="logout.php" class="inline"><?= csrf_field() ?><button class="link">Déconnexion</button></form>
    <?php else: ?>
      <a class="btn small" href="login.php">Connexion</a>
    <?php endif; ?>
  </nav>
</header>
<main class="wrap">
<?php foreach (get_flashes() as [$type, $msg]): ?>
  <div class="alert <?= e($type) ?>"><?= e($msg) ?></div>
<?php endforeach; ?>
