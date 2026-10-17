<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user_id()) redirect('discover.php');

$users  = new User($db);
$error  = '';
$active = ($_GET['form'] ?? '') === 'register' ? 'register' : 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');

    if (isset($_POST['login'])) {
        $row = $users->findByEmail($email);
        if ($row && password_verify($_POST['motdepasse'] ?? '', $row['motdepasse'])) {
            login_user($row);
            redirect('discover.php');
        }
        $error = 'Email ou mot de passe incorrect.'; // même message dans les deux cas
    }

    if (isset($_POST['register'])) {
        $active = 'register';
        $nom = trim($_POST['nom'] ?? ''); $prenom = trim($_POST['prenom'] ?? ''); $pwd = $_POST['motdepasse'] ?? '';
        if ($nom === '' || $prenom === '')                  $error = 'Nom et prénom obligatoires.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Adresse email invalide.';
        elseif (strlen($pwd) < 8)                           $error = 'Le mot de passe doit contenir au moins 8 caractères.';
        elseif ($users->findByEmail($email))                $error = 'Cet email est déjà utilisé.';
        else {
            $id = $users->create($nom, $prenom, $email, $pwd);
            login_user(['id_user' => $id, 'nom' => $nom, 'prenom' => $prenom]);
            flash('success', 'Bienvenue sur CreatiZone !');
            redirect('discover.php');
        }
    }
}
$keep = fn(string $k) => e($_POST[$k] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion / Inscription — CreatiZone</title>
  <link rel="icon" type="image/png" href="assets/logof.png">
  <link rel="stylesheet" href="assets/login_signup.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>.msg{color:#b03a3a;font-size:14px;margin:10px 0 -10px}.back{position:fixed;top:14px;left:18px;color:#fff;font-weight:600}</style>
</head>
<body>
<a class="back" href="index.php">← Accueil</a>
<?php foreach (get_flashes() as [, $fm]): ?><div style="position:fixed;top:14px;right:18px;background:#fff;padding:10px 16px;border-radius:10px;font-weight:600"><?= e($fm) ?></div><?php endforeach; ?>
<div class="container <?= $active === 'register' ? 'active' : '' ?>">

  <div class="form-box login">
    <form method="POST" action="">
      <?= csrf_field() ?>
      <h1>Connexion</h1>
      <?php if ($error && $active === 'login'): ?><p class="msg"><?= e($error) ?></p><?php endif; ?>
      <div class="input-box">
        <input type="email" placeholder="email" name="email" required value="<?= $active === 'login' ? $keep('email') : '' ?>">
        <i class="fa-solid fa-envelope"></i>
      </div>
      <div class="input-box">
        <input type="password" placeholder="mot de passe" name="motdepasse" required>
        <i class="fa-solid fa-lock"></i>
      </div>
      <button type="submit" name="login" value="1" class="btn">Se connecter</button>
    </form>
  </div>

  <div class="form-box register">
    <form method="POST" action="">
      <?= csrf_field() ?>
      <h1>Inscription</h1>
      <?php if ($error && $active === 'register'): ?><p class="msg"><?= e($error) ?></p><?php endif; ?>
      <div class="input-box">
        <input type="text" placeholder="nom" name="nom" required value="<?= $keep('nom') ?>"><i class="fa-solid fa-user"></i>
      </div>
      <div class="input-box">
        <input type="text" placeholder="prénom" name="prenom" required value="<?= $keep('prenom') ?>"><i class="fa-solid fa-user"></i>
      </div>
      <div class="input-box">
        <input type="email" placeholder="nom.prenom@gmail.com" name="email" required value="<?= $active === 'register' ? $keep('email') : '' ?>"><i class="fa-solid fa-envelope"></i>
      </div>
      <div class="input-box">
        <input type="password" placeholder="mot de passe (8 caractères min.)" name="motdepasse" required minlength="8"><i class="fa-solid fa-lock"></i>
      </div>
      <button type="submit" name="register" value="1" class="btn">S'inscrire</button>
    </form>
  </div>

  <div class="toggle-box">
    <div class="toggle-pannel toggle-left">
      <h1>Bonjour !</h1>
      <p>Pas encore de compte ?</p>
      <button type="button" class="btn register-btn">S'inscrire</button>
    </div>
    <div class="toggle-pannel toggle-right">
      <h1>Bon retour !</h1>
      <p>Vous avez déjà un compte ?</p>
      <button type="button" class="btn login-btn">Connexion</button>
    </div>
  </div>
</div>
<script src="assets/login_signup.js"></script>
</body>
</html>
