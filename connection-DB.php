<?php
// Je crée cette page pour me connecter à la base de données pour ne pas multiplier le code
// connection à la base de données soit locale soit en ligne
$bdd = new PDO('mysql:host=localhost;dbname=fouss;charset=utf8', 'fouss', 'A!GX6rGWHq');
//$bdd = new PDO('mysql:host=localhost;dbname=script-server-06-21;charset=utf8', 'root', '');
    // Pour pouvoir afficher les erreurs sql , on ne peut le faire que dans le cadre du développement
$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
