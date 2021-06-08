<?php
session_start();
include 'connection-DB.php';
// pour vérifier que les utilisateurs sont bien connectés
if (isset($_SESSION['login'])) {
    $action= 'read';
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
    }
}

//**********************CRUD***********************************************

 $action= 'read'; // pour afficher les données de la table par défaut

 if (isset($_GET['action'])){
    $action = $_GET['action'];
 }
 $content= '';// pour pouvoir changer le contenu en fonction de l'action choisie

// Pour afficher les données de la table
 if ($action == 'read'){
    if (isset($_GET['prenom'])) {// on fait une recherche par le prénom
        // on prépare la requete de la table coordonnées pour éviter les injections sql
        $request= $bdd->prepare ('SELECT * FROM `coordonnees` WHERE `prenom` =:prenom');
        $request->bindParam('prenom', $_GET['prenom']);
        $request->execute();
    }else{
        $request=$bdd ->prepare('SELECT * FROM `coordonnees`');
        $request ->execute();
    }
    $lines = $request ->fetchAll();
    $content = getTable($lines);// pour afficher toutes les lignes de la table

// Pour créer une nouvelle personnes avec des coordonnées
 }else if ($action == 'create') {
    if (isFormSubmit()) {// on vérifie que le formulaire est envoyé
        if (isFormValid()) { // on vérifie que le formulaire est valide.
            $request = $bdd->prepare('INSERT INTO `coordonnees` (`nom`, `prenom`, `adresse`,`mail` )VALUES  (:nom, :prenom, :adresse , :mail)');
            // on doit donner les différents paramètres contenus dans la table
            $params = ['nom' => $_POST ['nom'],
                'prenom' => $_POST ['prenom'],
                'adresse' => $_POST['adresse'],
                'mail' => $_POST ['mail']
            ];
            if ($request->execute($params)) { // si les paramètres correspondent on exécute.
                writeServiceMessage('Merci '.$_POST['prenom'].' a été ajouté'); // on affiche un message
                header('location: coordonnees.php'); // on redirige
                die();
            }

        }

    }
    $content = getForm(null); //  si ce n'est pas valide, on affiche une nouvelle fois le formulaire.
 }else if ($action =='delete'){ // pour supprimer une ligne de la table
    if(!isset ($_GET['id'])){ // on supprime par l'id de la ligne
        http_response_code(400);// mauvaise requête on propose d aller vers la page en cliquant sur retour
        $content= 'Mauvaise requête, il faut sélectionner un id <a href="coordonnees.php">Retour</a>';
    }else {
        // si l'id est bien sélectionné alors on prépare la requête et on supprime la ligne
        $request= $bdd ->prepare('DELETE FROM `coordonnees` WHERE `id`=:id');
        $params=['id' => $_GET['id']];
        if ($request -> execute($params)){
            writeServiceMessage('Les données de'.$_POST['prenom'].' ont été supprimées');
            header('location: coordonnees.php');
            die();
        }
    }
 }
// Pour mettre à jour la ligne de la table, cela se réalise également par l'id.
 else if ($action =='update'){
    if (!isset ($_GET['id'])){
        http_response_code(400);
        $content= 'Il faut choisir la personne que vous voulez modifier <a href="coordonnees.php">Retour</a>';
    }else{
        if (isFormSubmit()) {
            if (isFormValid()) {
                $request = $bdd->prepare('UPDATE `coordonnees` SET `nom`=:nom, `prenom`=:prenom, `adresse`=:adresse, `mail`=:mail  WHERE `id`=:id');
                $params = [
                         'nom' => $_POST ['nom'],
                        'prenom' => $_POST ['prenom'],
                        'adresse' => $_POST['adresse'],
                        'mail' => $_POST['mail'],
                        'id' => $_GET['id'] // dans ce cas on doit mettre l id dans les paramètres.
                ];
                if ($request->execute($params)) {
                    writeServiceMessage('Merci '.$_POST['prenom'].' a été mis à jour');
                    header('location: coordonnees.php');
                    die();
                }

            }
        }
        $request =$bdd->prepare('SELECT * FROM `coordonnees` WHERE `id`=:id');
        $request -> execute(['id' =>$_GET['id']]);
        $lines= $request ->fetchAll();
        if (!count($lines)){
            http_response_code(404);
            $content= 'Donnée introuvable <a href="coordonnees.php">Retour</a>';
        }else {
            $content = getForm($lines[0]);
        }

    }
 }


//**********************FONCTIONS***********************************************
function getTable($lines)
{
    $table= '<h3>Coordonnées</h3>';
    $table.= '<table>';
    $table.= '<thead>
      <tr><th>Id</th><th>Nom</th><th>Prénom</th><th>Adresse</th><th>Adresse mail</th></tr></thead>';
    $table.= '<tbody>';
    foreach ($lines as $line) {
        $table.= '<tr>';
        $table.='<td>' . $line['id'] . '</td>';
        $table.= '<td> ' . $line['nom'] . '</td>';
        $table.= '<td> ' . $line['prenom'] . '</td>';
        $table.='<td>' . $line['adresse'] . '</td>';
        $table.='<td><a href="mailto:'.$line['mail'].'">' . $line['mail'] . '</a></td>';
        $table.='<td><a href="?action=delete&id='.$line['id'].'"><i class="fas fa-trash"></i></a></td>';
        $table.='<td><a href="?action=update&id='.$line['id'].'"><i class="far fa-edit"></i></a></td>';
        $table.= '</tr>';
    }
    $table.= '</tbody>';
    $table.='</table>';
    return $table;
}

function getNav(){
    $nav = '
    <nav>
        <ul class="nav">
        <li class="nav-item" >
        <a class="nav-link" href="home.php">Accueil</a>
</li>
          <li class="nav-item">
            <a class="nav-link" href="?action=read">Voir les coordoonées</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?action=create">Ajouter une personne</a>
          </li>
      </ul>
    </nav>
    ';

    return $nav;
}


function getForm($coordonnees)
{

    $form ='
<h3>Ajouter une réalisation</h3>
    <form method="post">
    <div class="form-group">
    <label for="nom">Nom</label>
    <input type="text" name="nom" value="'.($coordonnees ? $coordonnees['nom'] :'' ).'" required>   
</div>
    <div class="form-group">
    <label for="prenom">Prénom</label>
    <input type="text" name="prenom"   value="'.($coordonnees ? $coordonnees['prenom'] : '').'" required> 
</div>    
    <div class="form-group">
    <label for="adresse">Adresse</label>
    <input type="text" name="adresse"   value="'.($coordonnees ? $coordonnees['adresse'] : '').'" required>   
</div>
<div class="form-group">
    <label for="mail">Mail</label>
    <input type="email" name="mail"   value="'.($coordonnees ? $coordonnees['mail'] : '').'" required>   
</div>
<div class="form-group">
   <button class="submit" type="submit">Envoyer</button>  
</div>

</form>';
    return $form;
}
function isFormSubmit() : bool {
    return isset($_POST['nom']);
}

function isFormValid() : bool {
    return
        !empty($_POST['nom'])
        && !empty($_POST['prenom'])
        && !empty($_POST['adresse'])
        && !empty($_POST['mail']);
}

//fonction pour afficher les messages

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
			<div class="content">
				<h1>
				<a href="home.php"><span>jf</span> Webdevelopper</a></h1>
				<h2>Julie Fouss</h2>
			</div>
		</header>
		<section>
		<div class="content">';
echo getNav();

$message = getServiceMessage();
if ($message) {
    echo '<div class="alert alert-primary" role="alert">
        ' . $message . '
</div>';
}
echo $content;
echo '
</div>
</section>
</main>
</body>
</html>';
