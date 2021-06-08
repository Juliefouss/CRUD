<?php
// on fait appel à la connection-DB.php pour se connecter
include 'connection-DB.php';

// Definition des constantes et variables
if(isset($_POST['login']) && isset($_POST['password'])){

    $user= $_POST['login'];
    $password=$_POST['password'];
    $regex="#[0-9]#"; // Pour contenir au minimum 1 chiffre.
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

}


// Test de l'envoi du formulaire
if(!empty($_POST)) {
    // Les identifiants sont ils transmis? Le login doit contenir au minimum un chiffre
    if ((!empty($_POST['login'])&& preg_match( $regex, $user))
        && !empty($_POST['password']))
        // on vérifie si le login appartient à la base de données.
    {
        // on prepare la requête pour éviter toutes injections sql
        $request = $bdd->prepare('SELECT * FROM `utilisateurs`WHERE `login`=:login');
        $request->bindParam('login', $_POST['login']);
        $request->execute();
        $user = $request->fetchAll();
        if ($user !== null) {
            // si on trouve un utilisateur dans la base de données on vérifie le mot de passe
        }
        if (passwordValid ($user[0], $_POST['password'])) {
            // si le password est valide on connecte l'utilisateurs et on le redirige vers la page d'accueil, en demarrant la session.
            session_start();
            $_SESSION['login'] = $user;
            $_SESSION['password'] = $password;
            header('location: home.php');// redirection vers la page d'accueil
        } else {
            echo' veuillez entrer vos identifiants corrects';// il y a une erreur alors on renvoit le formulaire
             getForm();

        }

    }
}


// fonction qui permet de vérifier le mot de passe.
function passwordValid($user, $password): bool
{
    return password_verify($password, $user['password']);
}

// Fonction pour afficher le formulaire qui propose, soit la connection soit une redirection vers le formulaire d'inscription.
function getForm()
{
    $form = '<form  method="post">

<div class="form-group">
    <label for="login">Login</label>
    <input type="text" name="login"  required> 
</div>    
 <div class="form-group">
    <label for="password">Mot de passe</label>
    <input type="password" name="password"  required> 
</div>  

<div class="form-group">
<button class="submit" type="submit">Envoyer</button>
<button class="submit" type="button"><a id="color" href="utilisateurs.php?action=create">Inscription</a> </button>
</div>
</form>';

    return $form;
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
				<span>jf</span> Webdevelopper</h1>
				<h2>Julie Fouss</h2>
			</div>
		</header>
		<section>
		<div class="content">';

echo getForm();
	echo' 	
</div>
</section>
</main>
</body>
</html>';
