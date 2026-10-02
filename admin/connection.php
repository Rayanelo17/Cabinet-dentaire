<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$error = '';
//login/pass admin:dr@cabinet.com/admin123
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Connexion DB
    $conn = new mysqli("localhost", "root", "", "cabinet_dentaire");
    if ($conn->connect_error) die("Erreur DB: " . $conn->connect_error);

    // Requête préparée
    $stmt = $conn->prepare("SELECT user_id, password_hash, role, full_name FROM users WHERE email = ?");
    if (!$stmt) die("Erreur préparation: " . $conn->error);

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) die("Erreur exécution: " . $stmt->error);

    $result = $stmt->get_result(); 

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password_hash'])) {
            // Stocker les informations de l'utilisateur dans la session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $email;
            $_SESSION['full_name'] = $user['full_name'];

            // Redirection vers le tableau de bord
            if ($user['role'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: ../patient/dashboard.php");
            }
            exit();
        } else {
            $error = "Mot de passe incorrect";
        }
    } else {
        $error = "Email non trouvé";
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <style>
        .error { color: red; }
        pre { background: #f0f0f0; padding: 10px; }
    </style>
    <link rel="stylesheet" href="style(login).css">
</head>
<body>
    <h1>Connexion</h1>
    <form method="POST">
        <input type="email" name="email" placeholder="admin@cabinetdentaire.com" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <a href="inscription.php" class="text-link">Créer un compte</a>
    </form>

    <!-- Debug retiré -->
</body>
</html>
