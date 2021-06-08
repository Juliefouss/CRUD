<?php
session_start();
include 'connection-DB.php';

if (isset( $_POST['password'])){
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
}


$action= 'read';

if (isset($_GET['action'])){
    $action = $_GET['action'];
}
$content= '';

//**********************CRUD***********************************************
if ($action == 'read'){
    if (isset($_GET['nom'])) {
        $request= $bdd->prepare ('SELECT * FROM `utilisateurs` WHERE `nom` =:nom');
        $request->bindParam('nom', $_GET['nom']);
        $request->execute();
    }else{
        $request=$bdd ->prepare('SELECT * FROM `utilisateurs`');
        $request ->execute();
    }
    $lines = $request ->fetchAll();
    $content = getTable($lines);

}else if ($action == 'create') {
    if (isFormSubmit()) {
        if (isFormValid()) {
            $request = $bdd->prepare('INSERT INTO `utilisateurs` (`nom`, `prenom`, `adresse` , `phone`,`mail`, `login`, `password`)VALUES  (:nom, :prenom,:adresse, :phone, :mail, :login, :password)');
            // on doit définir les différents paramètres pour pouvoir executer
            $params = ['nom' => $_POST ['nom'],
                'prenom' => $_POST ['prenom'],
                'adresse' => $_POST['adresse'],
                'phone' => $_POST['phone'],
                'mail' => $_POST['mail'],
                'login' => $_POST['login'],
                'password' => $hashed_password,

            ];
            if ($request->execute($params)) {
                writeServiceMessage('Merci '.$_POST['nom'].' a été ajouté');
                header('location: index.php');
                die(); // pas besoin  de le rediriger vers le formulaire puisque c'est déjà ce qui se passe donc on met un die
            }

        }

    }
    $content = getForm( null);
}
else if ($action =='delete'){
    if(!isset ($_GET['id'])){
        http_response_code(400);// mauvaise requête
        $content= 'Mauvaise requête, il faut sélectionner un id <a href="utilisateurs.php">Retour</a>';
    }else {
        $request= $bdd ->prepare('DELETE FROM `utilisateurs` WHERE `id`=:id');
        $params=['id' => $_GET['id']];
        if ($request -> execute($params)){
            writeServiceMessage('Utilisateur '.$_POST['nom'].' a été supprimé');
            header('location: utilisateurs.php');
            die();
        }
    }
}

else if ($action =='update'){
    if (!isset ($_GET['id'])){
        http_response_code(400);
        $content= 'Il faut choisir la réalisation à modifier <a href="utilisateurs.php">Retour</a>';
    }else{
        if (isFormSubmit()) {
            if (isFormValid()) {
                $request = $bdd->prepare('UPDATE `utilisateurs` SET `nom`=:nom, `prenom`=:prenom, `adresse`=:adresse,`phone`=:phone, `mail`=:mail, `login`=:login, `password`=:password WHERE `id`=:id');
                $params = [
                    'id'=> $_GET['id'],
                    'nom' => $_POST ['nom'],
                    'prenom' => $_POST ['prenom'],
                    'adresse' => $_POST['adresse'],
                    'phone' => $_POST['phone'],
                    'mail' => $_POST['mail'],
                    'login' => $_POST['login'],
                    'password' => $hashed_password,

                ];
                if ($request->execute($params)) {
                    writeServiceMessage('Merci '.$_POST['nom'].' a été modifié');
                    header('location: utilisateurs.php');
                    die();
                }

            }
        }
        $request =$bdd->prepare('SELECT * FROM `utilisateurs` WHERE `id`=:id');
        $request -> execute(['id' =>$_GET['id']]);
        $lines= $request ->fetchAll();
        if (!count($lines)){
            http_response_code(404);
            $content= 'Donnée introuvable <a href="utilisateurs.php">Retour</a>';
        }else {
            $content = getForm($lines[0]);
        }

    }
}



//**********************FONCTIONS***********************************************

function getTable($lines)
{
    $table= '<h3>Utilisateurs </h3>';
    $table.= '<table>';
    $table.= '<thead>
      <tr><th>Id</th><th>Nom</th><th>Prénom</th><th>Adresse</th><th>Numéro de téléphone</th><th>eMail</th><th>Login</th><th>Password</th></tr></thead>';
    $table.= '<tbody>';
    foreach ($lines as $line) {
        $table.= '<tr>';
        $table.='<td>' . $line['id'] . '</td>';
        $table.= '<td> ' . $line['nom'] . '</td>';
        $table.= '<td> ' . $line['prenom'] . '</td>';
        $table.= '<td> ' . $line['adresse'] . '</td>';
        $table.='<td>' . $line['phone'] . '</td>';
        $table.= '<td> ' . $line['mail'] . '</td>';
        $table.='<td>' . $line['login'] . '</td>';
        $table.='<td> '.'secret' .' </td>';
        $table.='<td><a href="?action=delete&id='.$line['id'].'"><i class="fas fa-trash"></i></a></td>';
        $table.='<td><a href="?action=update&id='.$line['id'].'"><i class="far fa-edit"></i></a></td>';
        $table.= '</tr>';
    }
    $table.= '</tbody>';
    $table.='</table>';
    return $table;


}

function getForm($utilisateurs): string
{

    $form ='
<h3>Utilisateurs</h3>
    <form method="post">
    <div class="form-group">
    <label for="nom">Nom</label>
    <input type="text" name="nom" value="'.($utilisateurs? $utilisateurs['nom'] :'' ).'" required>   
</div>
    <div class="form-group">
    <label for="prenom">Prénom</label>
    <input type="text" name="prenom"  value="'.($utilisateurs ? $utilisateurs['prenom'] : '').'" required> 
</div>    
<div class="form-group">
    <label for="adresse">Adresse</label>
    <input type="text" name="adresse"  value="'.($utilisateurs ? $utilisateurs['adresse'] : '').'" required> 
</div>    
  
<div class="form-group">
    <label for="phone">Numéro de téléphone</label>
    <input type="text" name="phone"  value="'.($utilisateurs ? $utilisateurs['phone'] : '').'" required> 
</div>  
<div class="form-group">
    <label for="mail">eMail</label>
    <input type="email" name="mail"   value="'.($utilisateurs ? $utilisateurs['mail'] : '').'" required> 
</div>
<div class="form-group">
    <label for="login">Login ( votre login doit contenir au minimum 1 chiffre)</label>
    <input type="text" name="login"  value="'.($utilisateurs ? $utilisateurs['login'] : '').'" required> 
</div>    
 <div class="form-group">
    <label for="password">Mot de passe</label>
    <input type="password" name="password"  value="'.($utilisateurs ? $utilisateurs['password'] : '').'" required> 
</div>  
<div class="form-group">
   <button class="submit" type="submit">Envoyer</button>  
</div>

';
    return $form;
}


function isFormSubmit() {
    return isset($_POST['nom']); // Je ne fais que sur le titre car le formulaire est complété et envoyé
}
// On va créer une fonction pour vérifié la validité du formulaire
function isFormValid() {
    $regex ="#[0-9]#";
    $user = $_POST['login'];
    return
        !empty($_POST['nom'])
        && !empty($_POST['prenom'])
        && !empty($_POST['adresse'])
        && !empty($_POST['phone'])
        && !empty($_POST['mail'])
        && !empty($_POST['login'] && preg_match($regex, $user))
        && !empty($_POST['password']);
}


//FONCTION POUR AFFICHER DES MESSAGES

function writeServiceMessage($message) {
    $_SESSION['serviceMessage'] = $message;
}

function getServiceMessage() {
    $message = null;
    if (isset($_SESSION['serviceMessage'])) {
        $message = $_SESSION['serviceMessage'];
        unset($_SESSION['serviceMessage']);
    }

    return $message;
}

function getNav(){
    $nav = '
    <nav>
        <ul class="nav">
         <li class="nav-item">
            <a class="nav-link" href="index.php">Se connecter</a>
          </li>
      </ul>
    </nav>
    ';

    return $nav;
}


//**********************AFFICHAGE***********************************************

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
			
				<h1>
				<span>jf</span> Webdevelopper</h1>
				<h2>Julie Fouss</h2>
			</div>
		</header>
		<section>';
echo getNav();
$message = getServiceMessage();
if ($message) {
    echo '<div class="alert alert-primary" role="alert">
        '.$message.'
</div>';
}
echo $content;
echo'
</div>
</section>
</main>
</body>
</html>';

