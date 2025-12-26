<?php
session_start();
include("database.php"); // Connexion MySQL dans $conn

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$filter_date = '';

// Gestion du formulaire de filtrage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset'])) {
        // Réinitialiser : rediriger vers la même page sans POST
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (!empty($_POST['filter_date'])) {
        $filter_date = $_POST['filter_date'];
    }
}

// Préparer la requête en fonction du filtre
if ($filter_date) {
    $stmt = $conn->prepare("SELECT a.*, u.full_name 
                            FROM appointments a
                            JOIN users u ON a.patient_id = u.user_id
                            WHERE a.appointment_date = ?
                            ORDER BY a.appointment_time ASC");
    $stmt->bind_param("s", $filter_date);
} else {
    $stmt = $conn->prepare("SELECT a.*, u.full_name 
                            FROM appointments a
                            JOIN users u ON a.patient_id = u.user_id
                            ORDER BY a.appointment_date DESC, a.appointment_time ASC");
}
$stmt->execute();
$result = $stmt->get_result();

// Compter total patients (patients = users sans rôle ou rôle vide)
$patientsResult = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'patient'");
$patients = $patientsResult ? $patientsResult->fetch_assoc()['total'] : 0;

// Compter total rendez-vous avec notes non vides
$notesResult = $conn->query("SELECT COUNT(*) AS total FROM appointments WHERE notes IS NOT NULL AND notes != ''");
$notes = $notesResult ? $notesResult->fetch_assoc()['total'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    // Récupérer les données du formulaire
    $patient_id = $_POST['patient_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $notes = $_POST['notes'];

    // Insérer dans la base de données
    $sql = "INSERT INTO appointments (patient_id, appointment_date, appointment_time, notes) VALUES (?, ?, ?, ?)";
    // préparer, binder, exécuter...
    
    // Préparer un message de succès ou erreur
    $message = "Rendez-vous ajouté avec succès !";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard - Cabinet Dentaire</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">DentalFadili</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarAdmin">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php">Accueil</a>
        </li>
      </ul>
      <a href="logout.php" class="btn btn-outline-light ms-auto">Déconnexion</a>
    </div>
  </div>
</nav>

<div class="container py-4">
    <h2>Bienvenue, <strong>Dr.Hatim</strong></h2>
    <p>Voici un aperçu de votre activité.</p>

    <div class="row mb-4">
      <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
          <div class="card-body">
            <h5 class="card-title">Nombre total des Patients</h5>
            <p class="card-text fs-3"><?= htmlspecialchars($patients) ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-white bg-info mb-3">
          <div class="card-body">
            <h5 class="card-title">Rendez-vous avec notes</h5>
            <p class="card-text fs-3"><?= htmlspecialchars($notes) ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Formulaire de filtre -->
    <form method="POST" class="mb-3 d-flex align-items-center gap-2">
        <label for="filter_date" class="form-label mb-0">Filtrer par date :</label>
        <input type="date" name="filter_date" id="filter_date" class="form-control" style="max-width: 200px;" value="<?= htmlspecialchars($filter_date) ?>">
        <button type="submit" class="btn btn-primary">Filtrer</button>
        <button type="submit" name="reset" value="1" class="btn btn-secondary">Réinitialiser</button>
    </form>

    <h4>
      Rendez-vous <?= $filter_date ? "du " . date('d/m/Y', strtotime($filter_date)) : "tous" ?>
    </h4>

    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
          <tr>
            <th>Patient</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars(date('d/m/Y', strtotime($row['appointment_date']))) ?></td>
                <td><?= htmlspecialchars($row['appointment_time']) ?></td>
                <td><?= htmlspecialchars($row['notes']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center">Aucun rendez-vous.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
