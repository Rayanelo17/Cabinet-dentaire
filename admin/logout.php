<?php
session_start();        // Démarre la session
session_unset();        // Supprime toutes les variables de session
session_destroy();      // Détruit la session

// Redirection vers la page de connexion (ou accueil)
header("Location: connection.php");
exit;
?>
