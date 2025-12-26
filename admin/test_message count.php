<?php
include('database.php'); // ta connexion

$result = $conn->query("SELECT COUNT(*) AS total FROM messages");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total messages = " . $row['total'];
} else {
    echo "Erreur SQL : " . $conn->error;
}
