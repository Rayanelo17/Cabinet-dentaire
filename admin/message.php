<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cabinet_dentaire");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Messages reçus
$stmt_received = $conn->prepare("
    SELECT m.subject, m.content, m.created_at, u.first_name, u.last_name 
    FROM messages m 
    JOIN users u ON m.sender_id = u.user_id 
    WHERE m.receiver_id = ? 
    ORDER BY m.created_at DESC
");
$stmt_received->bind_param("i", $user_id);
$stmt_received->execute();
$received = $stmt_received->get_result();

// Messages envoyés
$stmt_sent = $conn->prepare("
    SELECT m.subject, m.content, m.created_at, u.first_name, u.last_name 
    FROM messages m 
    JOIN users u ON m.receiver_id = u.user_id 
    WHERE m.sender_id = ? 
    ORDER BY m.created_at DESC
");
$stmt_sent->bind_param("i", $user_id);
$stmt_sent->execute();
$sent = $stmt_sent->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messages</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f5f5f5;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #ccc;
            padding-bottom: 5px;
        }
        .message {
            background-color: #fff;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .subject {
            font-weight: bold;
            font-size: 16px;
        }
        .content {
            margin-top: 8px;
        }
        .meta {
            color: #666;
            font-size: 13px;
            margin-top: 10px;
        }
        .empty {
            font-style: italic;
            color: #999;
        }
    </style>
</head>
<body>

<h2>📥 Messages reçus</h2>
<?php
$hasContent = false;
while ($row = $received->fetch_assoc()) {
    if (!empty(trim($row['content']))) {
        $hasContent = true;
        echo '<div class="message">';
        echo '<div class="subject">Objet : ' . htmlspecialchars($row['subject']) . '</div>';
        echo '<div class="content">' . nl2br(htmlspecialchars($row['content'])) . '</div>';
        echo '<div class="meta">Envoyé par : ' . htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']) . ' | Le : ' . $row['created_at'] . '</div>';
        echo '</div>';
    }
}
if (!$hasContent) {
    echo '<p class="empty">Aucun message reçu avec contenu.</p>';
}
?>

<h2>📤 Messages envoyés</h2>
<?php
$hasContent = false;
while ($row = $sent->fetch_assoc()) {
    if (!empty(trim($row['content']))) {
        $hasContent = true;
        echo '<div class="message">';
        echo '<div class="subject">Objet : ' . htmlspecialchars($row['subject']) . '</div>';
        echo '<div class="content">' . nl2br(htmlspecialchars($row['content'])) . '</div>';
        echo '<div class="meta">Envoyé à : ' . htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']) . ' | Le : ' . $row['created_at'] . '</div>';
        echo '</div>';
    }
}
if (!$hasContent) {
    echo '<p class="empty">Aucun message envoyé avec contenu.</p>';
}
?>

</body>
</html>
