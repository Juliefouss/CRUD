<?php
include 'connection-DB.php';
// Les headers avec les autorisations
header('Access-Control-Allow-Headers: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

$page= null;
if (isset($_GET['page'])){
    $page = $_GET['page'];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // requetes post pour envoyer
    $inputJSON = file_get_contents('php://input');
    $contactForm = json_decode($inputJSON, false);// decode car il envoit
// pour récolter les données du formulaire de contact du coté client
    $nom = $contactForm->nom;
    $prenom = $contactForm->prenom;
    $numero = $contactForm->numero;
    $mail = $contactForm->mail;
    $message = $contactForm->message;
    $date= date_format( new DateTime($contactForm->date), 'Y-m-d H:i:s');

// on fait la même requête que dans un crud avec les params pour insérer le message dans la base de données
    $request = $bdd->prepare('INSERT INTO `contact` (`nom`, `prenom`, `numero`, `mail`, `message`, `date`) 
                                    VALUES (:nom, :prenom, :numero, :mail, :message, :date) ');
    $params = ['nom' => $nom,
        'prenom' => $prenom,
        'numero' => $numero,
        'mail' => $mail,
        'message' => $message,
        'date' => $date
    ];
    $request->execute($params);

}else if ($_SERVER['REQUEST_METHOD'] === 'GET'){ // méthode get pour recevoir
    if ($page == 'realisations'){ // si la page choisie est realisations
        $request = $bdd ->prepare('SELECT * FROM `realisations`');
        $request->execute();
        $data = $request->fetchAll();
        echo json_encode($data); // encode car il recoit
    }else{
        if ($page == 'competences'){ // si la page choisie est compétences
            $request = $bdd ->prepare('SELECT * FROM `competences`');
            $request->execute();
            $data = $request->fetchAll();
            echo json_encode($data);
        }
    }
}
