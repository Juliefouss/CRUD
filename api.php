<?php
$bdd = new PDO('mysql:host=localhost;dbname=script-server-06-21;charset=utf8', 'root', '');
$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    $contactForm = json_decode($inputJSON, false);// car il envoit

    $nom = $contactForm->nom;
    $prenom = $contactForm->prenom;
    $numero = $contactForm->numero;
    $mail = $contactForm->mail;
    $message = $contactForm->message;
    $date= date_format( new DateTime($contactForm->date), 'Y-m-d H:i:s');


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
    if ($page == 'realisations'){
        $request = $bdd ->prepare('SELECT * FROM `realisations`');
        $request->execute();
        $data = $request->fetchAll();
        echo json_encode($data); // encore car il recoit
    }else{
        if ($page == 'competences'){
            $request = $bdd ->prepare('SELECT * FROM `competences`');
            $request->execute();
            $data = $request->fetchAll();
            echo json_encode($data);
        }
    }
}
