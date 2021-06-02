<?php
session_start();

// pour fermer la session en cours et rediriger vers la page index.
unset($_SESSION);
session_destroy();
header("location:index.php");

