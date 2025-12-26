<?php
$conn = new mysqli("localhost", "root", "", "cabinet_dentaire");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$service = $_POST['service'] ?? '';
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($name) || empty($email) || empty($phone) || empty($date) || empty($time)) {
    echo "donnée manquante";
    exit();
}

// Vérification du nombre de rendez-vous pour ce jour
$stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count >= 12) {
    echo "complet";
    exit();
}

// Vérification du créneau horaire
$stmt = $conn->prepare("SELECT appointment_id FROM appointments WHERE appointment_date = ? AND appointment_time = ?");
$stmt->bind_param("ss", $date, $time);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo "existe";
    exit();
}
$stmt->close();

// Vérifier si utilisateur existe
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id);
if ($stmt->fetch()) {
    $stmt->close(); // patient existe
} else {
    $stmt->close();
    // Nouveau patient
$password = password_hash("rdvauto123", PASSWORD_DEFAULT);
$role = 'patient';

$stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role, phone) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $email, $password, $role, $phone);
if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
} else {
    echo "erreur_utilisateur";
    exit();
}
$stmt->close();
}

// Enregistrer le rendez-vous
$stmt = $conn->prepare("INSERT INTO appointments (patient_id, appointment_date, appointment_time, phone, notes) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $user_id, $date, $time, $phone, $message);
if ($stmt->execute()) {
    echo "success";
} else {
    echo "Erreur SQL : " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
