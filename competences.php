<?php

session_start();
$bdd = new PDO('mysql:host=localhost;dbname=script-server-06-21;charset=utf8', 'root', '');
$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = 'read';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
}
$content = '';

if ($action == 'read') {
    if (isset($_GET['nom'])) {
        $request = $bdd->prepare('SELECT * FROM `competences` WHERE `nom` =:nom');
        $request->bindParam('nom', $_GET['nom']);
        $request->execute();
    } else {
        $request = $bdd->prepare('SELECT * FROM `competences`');
        $request->execute();
    }
    $lines = $request->fetchAll();
    $content = getTable($lines);

} else if ($action == 'create') {
    if (isFormSubmit()) {
        if (isFormValid()) {
            $request = $bdd->prepare('INSERT INTO `competences` (`nom`, `pourcentage`, `level1` , `level2`,`level3`, `level4`, `level5`)VALUES  (:nom, :pourcentage,:level1, :level2, :level3, :level4, :level5)');
            // on doit définir les différents paramètres pour pouvoir executer
            $params = ['nom' => $_POST ['nom'],
                'pourcentage' => $_POST ['pourcentage'],
                'level1' => $_POST['level1'],
                'level2' => $_POST['level2'],
                'level3' => $_POST['level3'],
                'level4' => $_POST['level4'],
                'level5' => $_POST['level5']
            ];
            if ($request->execute($params)) {
                writeServiceMessage('Merci '.$_POST['nom'].' a été ajoutée');
                header('location:competences.php');
                die(); // pas besoin  de le rediriger vers le formulaire puisque c'est déjà ce qui se passe donc on met un die
            }

        }

    }
    $content = getForm(null);
} else if ($action == 'delete') {
    if (!isset ($_GET['id'])) {
        http_response_code(400);// mauvaise requête
        $content = 'Mauvaise requête, il faut sélectionner un id <a href="competences.php">Retour</a>';
    } else {
        $request = $bdd->prepare('DELETE FROM `competences` WHERE `id`=:id');
        $params = ['id' => $_GET['id']];
        if ($request->execute($params)) {
            writeServiceMessage('compétence'.$_POST['nom'].' a été supprimée');
            header('location:competences.php');
            die();
        }
    }
} else if ($action == 'update') {
    if (!isset ($_GET['id'])) {
        http_response_code(400);
        $content = 'Il faut choisir la réalisation à modifier <a href="competences.php">Retour</a>';
    } else {
        if (isFormSubmit()) {
            if (isFormValid()) {
                $request = $bdd->prepare('UPDATE `competences` SET `nom`=:nom, `pourcentage`=:pourcentage, `level1`=:level1 , `level2`=:level2, `level3`=:level3, `level4`=:level4, `level5`=:level5 WHERE `id`=:id');
                $params = [
                    'id' => $_GET['id'],
                    'nom' => $_POST ['nom'],
                    'pourcentage' => $_POST ['pourcentage'],
                    'level1' => $_POST['level1'],
                    'level2' => $_POST['level2'],
                    'level3' => $_POST['level3'],
                    'level4' => $_POST['level4'],
                    'level5' => $_POST['level5']
                ];
                if ($request->execute($params)) {
                    writeServiceMessage('Merci '.$_POST['nom'].' a été mise à jour');
                    header('location:competences.php');
                    die();
                }

            }
        }
        $request = $bdd->prepare('SELECT * FROM `competences` WHERE `id`=:id');
        $request->execute(['id' => $_GET['id']]);
        $lines = $request->fetchAll();
        if (!count($lines)) {
            http_response_code(404);
            $content = 'Donnée introuvable <a href="competences.php">Retour</a>';
        } else {
            $content = getForm($lines[0]);
        }

    }
}

function getTable($lines)
{
    $table = '<h3>Competences </h3>';
    $table .= '<table>';
    $table .= '<thead>
      <tr><th>Id</th><th>Nom</th><th>Pourcentage</th><th>level1</th><th>Level2</th><th>Level3</th><th>Level4</th><th>Level5</th></tr></thead>';
    $table .= '<tbody>';
    foreach ($lines as $line) {
        $table .= '<tr>';
        $table .= '<td>' . $line['id'] . '</td>';
        $table .= '<td> ' . $line['nom'] . '</td>';
        $table .= '<td> ' . $line['pourcentage'] . '</td>';
        $table .= '<td> ' . $line['level1'] . '</td>';
        $table .= '<td>' . $line['level2'] . '</td>';
        $table .= '<td> ' . $line['level3'] . '</td>';
        $table .= '<td>' . $line['level4'] . '</td>';
        $table .= '<td>' . $line['level5'] . '</td>';
        $table .= '<td><a href="?action=delete&id=' . $line['id'] . '"><i class="fas fa-trash"></i></a></td>';
        $table .= '<td><a href="?action=update&id=' . $line['id'] . '"><i class="far fa-edit"></i></a></td>';
        $table .= '</tr>';
    }
    $table .= '</tbody>';
    $table .= '</table>';
    return $table;


}

function getForm($competences): string
{

    $form = '
<h3>Utilisateurs</h3>
    <form method="post" >
    <div class="form-group">
    <label for="nom">Nom</label>
    <input type="text" name="nom" value="' . ($competences ? $competences['nom'] : '') . '" required>   
</div>
    <div class="form-group">
    <label for="pourcentage">Pourcentage</label>
    <select name="pourcentage">
    <option value="20">20</option>
    <option value="40">40</option>
    <option value="60">60</option>
    <option value="80">80</option>
    <option value="100">100</option>
</select>
</div>    
<div class="form-group">
    <label for="level1" >Level1</label>
     <select name="level1">
    <option value="progress20">progress20</option>
    <option value="rest20">rest20</option>
</select>
</div>    
  
<div class="form-group">
    <label for="level2" class="left">Level2</label>
     <select name="level2">
    <option value="progress20">progress20</option>
    <option value="rest20">rest20</option>
</select>
</div>  
<div class="form-group">
    <label for="level3" class="left">Level3</label>
     <select name="level3">
    <option value="progress20">progress20</option>
    <option value="rest20">rest20</option>
</select>
</div>
<div class="form-group">
    <label for="level4" class="left">Level4</label>
    <select name="level4">
    <option value="progress20">progress20</option>
    <option value="rest20">rest20</option>
</select>
</div>    
 <div class="form-group">
    <label for="level5" class="left">Level5</label>
    <select name="level5">
    <option value="progress20">progress20</option>
    <option value="rest20">rest20</option>
</select>
</div>  

<div class="form-group">
   <button class="submit" type="submit">Envoyer</button>  
</div>

';
    return $form;
}


function isFormSubmit(): bool
{
    return isset($_POST['nom']); // Je ne fais que sur le titre car le formulaire est complété et envoyé
}

// On va créer une fonction pour vérifié la validité du formulaire
function isFormValid(): bool
{
    return
        !empty($_POST['nom'])
        && !empty($_POST['pourcentage'])
        && !empty($_POST['level1'])
        && !empty($_POST['level2'])
        && !empty($_POST['level3'])
        && !empty($_POST['level4'])
        && !empty($_POST['level5']);
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


function getNav()
{
    $nav = '
    <nav>
        <ul class="nav">
        <li class="nav-item" >
        <a class="nav-link" href="home.php">Accueil</a>
         </li>
          <li class="nav-item">
            <a class="nav-link" href="?action=read">Voir les compétences</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?action=create">Ajouter une compétence</a>
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
				<span>jf</span> Webdevelopper</h1>
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
</div>
</section>
</main>
</body>
</html>';

