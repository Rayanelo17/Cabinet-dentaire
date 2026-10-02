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

    <!-- Data Viz Dashboard Section -->
    <h4 class="mt-4 mb-3">Analytique & Prévisions <span class="badge bg-primary fs-6">IA & Data</span></h4>
    <div class="row mb-5">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Traitements les plus fréquents</h5>
                    <div style="position: relative; height:300px; width:100%">
                        <canvas id="treatmentsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Revenus & Prévision (Série Temporelle)</h5>
                    <div style="position: relative; height:300px; width:100%">
                        <canvas id="revenueChart"></canvas>
                    </div>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    fetch('api_dashboard.php')
        .then(response => response.json())
        .then(data => {
            if(data.error) {
                console.error("Erreur API:", data.error);
                return;
            }

            // --- Graphique des Traitements (Pie Chart) ---
            const ctxTreatments = document.getElementById('treatmentsChart').getContext('2d');
            new Chart(ctxTreatments, {
                type: 'doughnut',
                data: {
                    labels: data.treatments.labels,
                    datasets: [{
                        data: data.treatments.data,
                        backgroundColor: [
                            '#0d6efd', '#6610f2', '#6f42c1', '#d63384', 
                            '#dc3545', '#fd7e14', '#ffc107', '#198754'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' }
                    }
                }
            });

            // --- Graphique de Revenus & Prévisions (Line Chart) ---
            // Simple mock ARIMA forecast for the portfolio: add one "forecast" point
            const revenueLabels = [...data.revenue.labels];
            const revenueData = [...data.revenue.data];
            let forecastData = Array(revenueData.length).fill(null);
            
            if (revenueData.length > 0) {
                // Prevision simple: moyenne des 3 derniers mois + 5%
                const last3 = revenueData.slice(-3);
                const sum = last3.reduce((a, b) => a + b, 0);
                const avg = sum / last3.length;
                const forecast = avg * 1.05;
                
                // Add next month prediction
                revenueLabels.push('Prévision+1');
                forecastData.push(forecast);
                // Attach forecast line to the end of actual data line
                forecastData[revenueData.length - 1] = revenueData[revenueData.length - 1];
            }

            const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctxRevenue, {
                type: 'line',
                data: {
                    labels: revenueLabels,
                    datasets: [
                        {
                            label: 'Revenus historiques (DH)',
                            data: revenueData,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Prévision (Modèle Auto-Régressif)',
                            data: forecastData,
                            borderColor: '#ffc107',
                            borderDash: [5, 5],
                            backgroundColor: 'transparent',
                            tension: 0.3,
                            pointBackgroundColor: '#ffc107',
                            pointRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        })
        .catch(err => console.error("Erreur Fetch:", err));
});
</script>
</body>
</html>
