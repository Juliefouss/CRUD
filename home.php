<?php
// pour que la session soit ouverte
session_start();
$bdd = new PDO('mysql:host=localhost;dbname=script-server-06-21;charset=utf8', 'root', '');
$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// une fonction pour créer une navigation avec des redirection vers les différentes pages.
function getNav(){
$nav = '
    <nav>
        <ul class="nav">
         <li class="nav-item">
            <a class="nav-link" href="home.php">Accueil</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="realisations.php">Réalisations</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="coordonnees.php">Coordonnées</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="utilisateurs.php">Profil personnel</a>
          </li>
          <li class="nav-item" >
        <a class="nav-link" href="competences.php">Compétences</a>
         </li>
          <li class="nav-item">
            <a class="nav-link" href="deconnection.php">Se déconnecter</a>
          </li>
      </ul>
    </nav>
    ';

    return $nav;
}


echo '<html lang="fr">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Julie Fouss Webdevelopper Portfolio</title>
	<link rel="stylesheet" href="style.css">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Amatic+SC:wght@400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;500;600;700&display=swap" realisationsrel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Courgette&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Mogra&display=swap" rel="stylesheet">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Gotu&display=swap" rel="stylesheet">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Mogra&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
  
      
</head>
<body>
	<main>
		<header>
			<div class="content">
				<h1>
				<a href=""><span>jf</span> Webdevelopper</a></h1>
				<h2>Julie Fouss</h2>
			</div>
</header>
		<section>
		<div class="content">';
echo getNav();
echo'
	        <div class="col col-6-12">
					<h3> Bienvenue</h3>
				</div>
			<div class="clear"></div>
			<div class="col col-6-12 last">
			<img src="asset/white-4522591_1920.jpg" alt="gypsophile">		
				</div>
				<div class="clear"></div>
				
</div>
</section>
</main>
</body>
</html>';
