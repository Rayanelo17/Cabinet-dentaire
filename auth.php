<?php
session_start();

// Connexion à la base de données
$host = "localhost";
$dbname = "cabinet_dentaire";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

if (isset($_POST['email'], $_POST['password'])) {
    $email = htmlspecialchars($_POST['email']);
    $passwordInput = $_POST['password'];

    // Cherche l'utilisateur dans la base
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($passwordInput, $user['password'])) {
        // Si l'email est celui de l'admin
        if ($email === 'dr@cabinet.com') { // Admin hardcodé ici
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role']; // 'admin'
            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            // Si c’est un patient, rediriger vers son tableau de bord
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role']; // 'patient'
            header("Location: ../patient/dashboard.php");
            exit();
        }
    } else {
        // Si l'email ou le mot de passe est incorrect
        $_SESSION['error'] = "Identifiants invalides.";
        header("Location: ../login.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Veuillez remplir tous les champs.";
    header("Location: ../login.php");
    exit();
}
?>
