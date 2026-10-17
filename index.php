<?php require __DIR__ . '/includes/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CreatiZone — Accueil</title>
    <link rel="icon" type="image/png" href="assets/logof.png">
    
    <!--font awesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!--font css-->
    <link href="https://db.onlinewebfonts.com/c/6a17929c7360288d1242981da7712dc3?family=Torus+Notched"
     rel="stylesheet"
     type="text/css">
     <!--mon css-->
     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
     <link rel="stylesheet" href="assets/acceuil.css">

</head>
<body>
  <header>
    <div class="container">
        <div class="logo">
             <img src="assets/logof.png" alt="Logo">
        </div>
        <div class="onglet">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="discover.php">Découvrir</a></li>
                <?php if (current_user_id()): ?>
                <li><a href="profile.php">Mon profil</a></li>
                <?php else: ?>
                <li><a href="login.php">Connexion</a></li>
                <?php endif; ?>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </div>
        <div class="logo"></div>
    </div>
  </header>


    <div class="sec1">
        <div class="container">
            <h1>CreatiZone</h1>
            <h3>Challenge Hub : Créez, Compétitez, Brillez</h3>
              <p>Rejoignez une communauté de challengers dans des défis 
                <br> passionnants dans les domaines du sport, de la culture générale, du code et 
                <br> bien plus encore. Suivez votre progression, grimpez dans les classements et gagnez  
                <br> des badges pour devenir le champion de la communauté !
              </p>
        </div>
    </div>

    <div class="transition">
        <img src="assets/imgtransition.png" alt="img">
    </div>

    <div class="sec2">
        <div class="container">
            <div class="title">
                <h1>Rejoignez nous...</h1>
            </div>
            <div class="content content-top">
                <div class="left" style="max-width: 700px;">
                    <h2>Dépassez vos limites... et vos amis !</h2>
                    <div class="separateur"></div>
                        <p>Challenge Hub est l'espace idéal pour créer des <br>
                            défis sur mesure et défier la communauté. <br>
                            Partagez vos exploits, <br>
                            comparez vos scores dans des quiz endiablés <br>
                            et soyez le champion! </p>

                </div>
                <div class="right">
                    <img src="assets/sec2img.png" alt="img">
                </div>
            </div>
            <div class="content content-bottom">
                <div class="left">
                    <img src="assets/sec2img2.png" alt="img">
                </div>
                <div class="right" style="max-width: 700px;">
                    <h2>Challenge Hub : Bien plus qu'une plateforme</h2>
                    <div class="separateur"></div>
                        <p>Et si on rendait la culture générale et le sport plus amusants ? <br>
                            Créez des défis sur mesure et voyez qui, dans la communauté, <br>
                            a ce qu'il faut pour être premier.</p>
                    
                </div>
                
            </div>
        </div>
    </div>
<footer id="contact">
    <div class="container">
        <div class="footer-section">
            <h3>Contact</h3>
            <p><i class="fas fa-envelope"></i>contactcreatiZone@gmail.com</p>
            <p><i class="fas fa-phone"></i>+216 22 222 222</p>
            <p><i class="fas fa-map-marker-alt"></i>Bizerte, Tunisie</p>
        </div>
        <div class="footer-section">
            <h3>Suivez nous</h3>
              <div class="social-links">
                  <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                  <a href="#"><i class="fab fa-instagram"></i></a>
                  <a href="#"><i class="fab fa-linkedin"></i></a>
              </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; CreatiZone. Tous droits réservés.</p>
        </div>
    </div>
</footer>
</body>
</html>
