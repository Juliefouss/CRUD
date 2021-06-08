<?php
// POUR DEMARRER ET CONNECTER LA SESSION A LA BASE DE DONNEES.
session_start();
include 'connection-DB.php';
// LE CRUD
if (isset($_SESSION['login'])) {
    $action= 'read';
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
    }
}
//**********************************************CRUD*******************************


 if (isset($_GET['action'])){
    $action = $_GET['action'];
 }
 $content= '';
// Pour lire les données de la base de données
 if ($action == 'read') {
    if (isset($_GET['titre'])) { // on selectionne la table et on dit ou on doit chercher
        $request = $bdd->prepare('SELECT * FROM `realisations` WHERE `titre` =:titre');
        $request->bindParam('titre', $_GET['titre']);
        $request->execute();
    } else {
        $request = $bdd->prepare('SELECT * FROM `realisations`');
        $request->execute();
    }
    $lines = $request->fetchAll();
    $content = getTable($lines); // pour afficher la tables et toutes ses lignes


  // Pour créer une nouvelle réalisation et l'insérer dans la base de données.
 }else if ($action == 'create') {
    if (isFormSubmit()) { // on vérifie que le formulaire est envoyé
        if (isFormValid()) { // on vérifie que le formulaire est valide
            $filePath = uploadFile(); // pour appeler la fonction upload
            // on insere les valeurs du formulaire dans la table realisations avec les valeurs  dans le bon ordre
            // et puis on dit que les valeurs recueillies dans le formulaire doivent aller dans ces colonnes la dans le bon ordre
            $request = $bdd->prepare('INSERT INTO `realisations` (`image`, `nomImage`, `titre`, `lien`, `date`) VALUES  ( :image, :nomImage, :titre, :lien, :date)');
            // on doit définir les différents paramètres pour pouvoir executer et pour la sécurité
            $params = ['image' => $filePath,
                'nomImage' =>$_POST['nomImage'],
                'titre' => $_POST ['titre'],
                'lien' => $_POST ['lien'],
                'date' => $_POST['date']
            ];
            if ($request->execute($params)) { // on execute
                writeServiceMessage('Merci '.$_POST['titre'].' a été ajouté');
                // on laisse un message pour dire que c'est ok et puis on redirige
                header('location: realisations.php');
                die(); // pas besoin  de le rediriger vers le formulaire puisque c'est déjà ce qui se passe donc on met un die
            }

        }

    }
    $content = getForm( null); // si il y a une erreur on repropose le formulaire

 // Pour supprimer via l'id de la réalisation
 }else if ($action == 'delete') {
    if (!isset($_GET['id'])) {
        http_response_code(400);// si l id n'est pas validé + possible de retour
        $content = 'Mauvaise requête, impossible de supprimer sans avoir un id. <a href="realisations.php">Retour à la liste</a>';
    }else {
        // on choisit la table ou nous allons supprimé un element et on donne l id pour dire lequel
        $request = $bdd->prepare('DELETE FROM `realisations` WHERE `id`=:id');
        $params = ['id' => $_GET['id']];// uniquement le params de l'id vu que c'est uniquement via l'id
        if ($request->execute($params)) {
            //message pour dire que c'est ok
            writeServiceMessage('Realisation '.$_POST['titre'].' a été supprimée');
            header('Location: realisations.php'); // redirection
            die();
        }
    }

    // Pour apporter des modifications à une réalisations déjà encodée.
 }else if ($action == 'update') {
    if (!isset($_GET['id'])) {// il faut aussi un id
        http_response_code(400);
        $content = 'Mauvaise requête, impossible de mettre à jour sans avoir un id. <a href="realisations.php">Retour à la liste</a>';
    } else {
        if (isFormSubmit()) {
            if (isFormValid()) {
                $filePath = uploadFile();

                $request = $bdd->prepare('UPDATE `realisations` SET `image`=:image , `nomImage`=:nomImage, `titre`=:titre, `lien`=:lien, `date`=:date WHERE `id`=:id');
                // on donne tous les params car il peut y avoir plusieurs changement et on ajoute également l'id
                $params = ['id' => $_GET['id'],
                    'nomImage' =>$_POST['nomImage'],
                    'titre' => $_POST ['titre'],
                    'lien' => $_POST ['lien'],
                    'date' => $_POST['date'],
                    'image' => $filePath
                ];

                if ($request->execute($params)) {
                    // message pour dire que l'update est ok
                    writeServiceMessage('Realisation '.$_POST['titre'].' est mise à  jour ');
                    header('location: realisations.php');
                    die();
                }
            }
        }
        $request = $bdd->prepare('SELECT * from `realisations` WHERE `id`=:id');
        $request->execute(['id' => $_GET['id']]);
        $lines = $request->fetchAll();
        if (!count($lines)){
            http_response_code(404);
            $content = 'Données introuvables <a href="realisations.php">Retour à la liste</a>';
        }else {

            $content = getForm($lines[0]);
        }
    }

 }

//**********************************************FONCTIONS*******************************

//Fonction pour afficher la table avec les valeurs de la base de données
    function getTable($lines)
    {
        $table = '<h3>Réalisations</h3>';
        $table .= '<table>';
        $table .= '<thead>
      <tr><th>Id</th><th>Image</th><th>Nom Image</th><th>Titre</th><th>Lien</th><th>Date</th></tr></thead>';
        $table .= '<tbody>';
        foreach ($lines as $rea) {
            $table .= '<tr>';
            $table .= '<td>' . $rea['id'] . '</td>';
            $table .= '<td>' . ($rea['image'] !== null ? '<img src="'. $rea['image'] . '" />' : '') . '</td>';
            $table .= '<td> ' . $rea['nomImage'] . '</td>';
            $table .= '<td> ' . $rea['titre'] . '</td>';
            $table .= '<td> ' . $rea['lien'] . '</td>';
            $table .= '<td> ' . $rea['date'] . '</td>';
            $table .= '<td><a href="?action=delete&id=' . $rea['id'] . '"><i class="fas fa-trash"></i></a></td>';
            $table .= '<td><a href="?action=update&id=' . $rea['id'] . '"><i class="far fa-edit"></i></a></td>';
            $table .= '</tr>';
        }
        $table .= '</tbody>';
        $table .= '</table>';
        return $table;


    }

// Fonction pour la navigation qui va me permettre le read et le create et de revenir sur la page d'accueil ou de se déconnecter

function getNav(){
    $nav = '
    <nav>
        <ul class="nav">
         <li class="nav-item">
            <a class="nav-link" href="home.php">Accueil</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?action=read">Voir réalisations</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?action=create">Ajouter réalisation</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="deconnection.php">Se déconnecter</a>
          </li>
      </ul>
    </nav>
    ';

    return $nav;
}



// Fonction pour créer un formulaire qui va être utile pour la création et le update des réas

function getForm($realisation){
   $action = 'realisations.php?action=' . $_GET['action'];
   if( isset($_GET['id'])){
       $action = $action . '&id='. $_GET['id'];
   }

        $form = '
<h3>Utilisateurs</h3>
    <form method="post" action='.$action.' enctype="multipart/form-data">  
   
    <div class="form-group">
    <label for="image">Image</label>
    <input type="file" name="image" id="image" >
</div>
 <div class="form-group">
    <label for="nomImage">Nom de image</label>
    <input type="text" name="nomImage"  id="nomImage"  value="' . ($realisation ? $realisation['nomImage'] : '') . '" required>   
</div>
    <div class="form-group">
    <label for="titre">Titre</label>
    <input type="text" name="titre"  id="titre"  value="' . ($realisation ? $realisation['titre'] : '') . '" required>   
</div>
    <div class="form-group">
    <label for="lien">Lien</label>
    <input type="url" name="lien" id="lien"  value="' . ($realisation ? $realisation['lien'] : '') . '" required> 
</div>    
<div class="form-group">
    <label for="date">Date</label>
    <input type="date" name="date" id="date"  value="' . ($realisation ? $realisation['date'] : '') . '" required> 
</div> 
  <div class="form-group">
   <button class="submit" type="submit">Envoyer</button>  
  </div>
        </form>
       
    ';

      return $form;
 }



// Fonction pour vérifier que le formulaire est bien envoyé

function isFormSubmit() : bool {
    return isset($_POST['titre']); // Si on a au moins un POST['titre'] c'est que normalement le formulaire a été posté.
}

// Fonction pour vérifier que le formulaire est valide ensuite pour valider la taille du fichier et les extensiosn autorisées

function isFormValid() : bool {
    $valid =
        !empty($_POST['nomImage'])
        && !empty($_POST['titre'])
        && !empty($_POST['lien'])
        && !empty($_POST['date']) ;

    if ($valid && isset($_FILES['image'])AND $_FILES['image']['error'] == 0) {
            if ($_FILES['image']['size'] > 100000) {
                $valid = false;
                writeServiceMessage("dossier trop gros");
            }
            $infosfichier = pathinfo($_FILES['image']['name']);
            $extension_upload = $infosfichier['extension'];
            $extensions_autorisees= array('jpg','jpeg', 'gif', 'png');
            if (!in_array($extensions_autorisees, $extension_upload)){
                writeServiceMessage("Seulement ".implode(", ", $extensions_autorisees). " est autorisé");
                $valid=false;
            }
        }else if ($_FILES['image']['error'] == 1){
        $valid = false;
        $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png');
        writeServiceMessage("Seulement ".implode(", ", $extensions_autorisees). " est autorisé");

    }
        return $valid;
    }



// Fonction pour uploader le fichier et le stocker dans le serveur

function uploadFile() {
    if (isset($_FILES['image'])) {
        $filePath = './uploads/'.uniqid().basename($_FILES['image']['name']);
        if(move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
            return $filePath;
        }
    }
    return null;
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



//**********************************************AFFICHAGE*******************************


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
        '.$message.'
</div>';
}

echo $content;

echo '
</div>
</section>
</main>
</body>
</html>';
