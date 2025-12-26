<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
   

    if ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        $conn = new mysqli("localhost", "root", "", "cabinet_dentaire");
        if ($conn->connect_error) {
            die("Erreur DB: " . $conn->connect_error);
        }

        $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "Cet email est déjà utilisé";
            $stmt_check->close();
        } else {
            $stmt_check->close();
            $role = (strpos($email, '@cabinetdentaire.com') !== false) ? 'admin' : 'patient';
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $full_name, $email, $password_hash, $role);
            if ($stmt_insert->execute()) {
                $success = "Inscription réussie! Vous pouvez maintenant vous connecter.";
            } else {
                $error = "Erreur lors de l'inscription: " . $conn->error;
            }
            $stmt_insert->close();
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
            padding: 20px;
        }

        form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        h2 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .form-group {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group input,
        input[type="email"],
        input[type="password"] {
            flex: 1;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            background-color: #f9f9f9;
            transition: all 0.3s;
        }

        input:focus {
            border-color: #3498db;
            outline: none;
            background-color: #fff;
        }

        button {
            padding: 15px;
            background-color: #3498db;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: #2980b9;
        }

        .error {
            color: #e74c3c;
            font-size: 14px;
            text-align: center;
            margin-bottom: 10px;
        }

        .success {
            color: #27ae60;
            font-size: 14px;
            text-align: center;
            margin-bottom: 10px;
        }

        .text-link {
            text-decoration: none;
            color: #3498db;
            font-size: 14px;
            display: block;
            text-align: center;
            margin-top: 15px;
        }

        .text-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            form {
                width: 90%;
            }

            h2 {
                font-size: 20px;
            }

            input,
            button {
                padding: 12px;
                font-size: 14px;
            }

            .form-group {
                flex-direction: column;
            }

            .form-group input {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <h2>Inscription</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <input type="text" name="full_name" placeholder="Nom Complet" required>
            
        </div>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <input type="password" name="confirm_password" placeholder="Confirmez le mot de passe" required>
        <button type="submit">S'inscrire</button>
        <a class="text-link" href="connection.php">Déjà inscrit ? Se connecter</a>
    </form>
</body>
</html>
