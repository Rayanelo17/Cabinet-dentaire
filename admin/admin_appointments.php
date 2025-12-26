<?php
session_start();
include("database.php");

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Gestion des actions (ajout, modification, suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            $patient_id = $_POST['patient_id'];
            $appointment_date = $_POST['appointment_date'];
            $appointment_time = $_POST['appointment_time'];
            $notes = $_POST['notes'];

            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, appointment_date, appointment_time, notes) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $patient_id, $appointment_date, $appointment_time, $notes);
            $stmt->execute();
            $stmt->close();

        } elseif ($action === 'edit') {
            $appointment_id = $_POST['appointment_id'];
            $patient_id = $_POST['patient_id'];
            $appointment_date = $_POST['appointment_date'];
            $appointment_time = $_POST['appointment_time'];
            $notes = $_POST['notes'];

            $stmt = $conn->prepare("UPDATE appointments SET patient_id = ?, appointment_date = ?, appointment_time = ?, notes = ? WHERE appointment_id = ?");
            $stmt->bind_param("isssi", $patient_id, $appointment_date, $appointment_time, $notes, $appointment_id);
            $stmt->execute();
            $stmt->close();

        } elseif ($action === 'delete') {
            $appointment_id = $_POST['appointment_id'];
            $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: admin_appointments.php"); // Évite la double soumission au refresh
    exit();
}

// Récupérer tous les rendez-vous
$query = "SELECT a.*, u.full_name FROM appointments a JOIN users u ON a.patient_id = u.user_id ORDER BY a.appointment_date DESC, a.appointment_time ASC";
$result = $conn->query($query);

// Récupérer tous les patients pour la sélection dans formulaire
$patientsResult = $conn->query("SELECT user_id, full_name FROM users WHERE role IS NULL OR role = '' ORDER BY full_name");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Gestion des Rendez-vous - Admin</title>
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
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Accueil</a></li>
        <li class="nav-item"><a class="nav-link active" href="admin_appointments.php">Gestion Rendez-vous</a></li>
      </ul>
      <a href="logout.php" class="btn btn-outline-light ms-auto">Déconnexion</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h2>Gestion des Rendez-vous</h2>

  <!-- Bouton Ajouter -->
  <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAdd">Ajouter un Rendez-vous</button>

  <!-- Tableau des rendez-vous -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th>Patient</th>
          <th>Date</th>
          <th>Heure</th>
          <th>Notes</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['full_name']) ?></td>
              <td><?= htmlspecialchars($row['appointment_date']) ?></td>
              <td><?= htmlspecialchars($row['appointment_time']) ?></td>
              <td><?= htmlspecialchars($row['notes']) ?></td>
              <td>
                <button class="btn btn-primary btn-sm btn-edit" 
                  data-id="<?= $row['appointment_id'] ?>"
                  data-patient_id="<?= $row['patient_id'] ?>"
                  data-date="<?= $row['appointment_date'] ?>"
                  data-time="<?= $row['appointment_time'] ?>"
                  data-notes="<?= htmlspecialchars($row['notes']) ?>"
                  data-bs-toggle="modal" data-bs-target="#modalEdit">Modifier</button>
                <form method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>" />
                  <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5" class="text-center">Aucun rendez-vous.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Ajouter -->
<div class="modal fade" id="modalAdd" tabindex="-1" aria-labelledby="modalAddLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content" action="ajouter_rendezvous.php">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAddLabel">Ajouter un rendez-vous</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="add" />
        
        <!-- Champ full_name pour saisir le nom complet du patient -->
        <div class="mb-3">
          <label for="full_name_add" class="form-label">Nom complet du patient</label>
          <input type="text" name="full_name" id="full_name_add" class="form-control" placeholder="Nom et prénom" required>
        </div>
        
        <div class="mb-3">
          <label for="appointment_date_add" class="form-label">Date du rendez-vous</label>
          <input type="date" name="appointment_date" id="appointment_date_add" class="form-control" required>
        </div>
        
        <div class="mb-3">
          <label for="appointment_time_add" class="form-label">Heure du rendez-vous</label>
          <input type="time" name="appointment_time" id="appointment_time_add" class="form-control" required>
        </div>
        
        <div class="mb-3">
          <label for="notes_add" class="form-label">Message / Notes</label>
          <textarea name="notes" id="notes_add" class="form-control" rows="3" placeholder="Message ou notes"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Ajouter</button>
      </div>
    </form>
  </div>
</div>


<!-- Modal Modifier -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content" id="formEdit">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditLabel">Modifier le Rendez-vous</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="edit" />
        <input type="hidden" name="appointment_id" id="appointment_id_edit" />
        <div class="mb-3">
          <label for="patient_id_edit" class="form-label">Patient</label>
          <select class="form-select" name="patient_id" id="patient_id_edit" required>
            <option value="" disabled>Choisir un patient</option>
            <?php 
            // Reset result pointer to fetch patients again
            $patientsResult->data_seek(0);
            while ($patient = $patientsResult->fetch_assoc()): ?>
              <option value="<?= $patient['user_id'] ?>"><?= htmlspecialchars($patient['full_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="appointment_date_edit" class="form-label">Date</label>
          <input type="date" class="form-control" name="appointment_date" id="appointment_date_edit" required />
        </div>
        <div class="mb-3">
          <label for="appointment_time_edit" class="form-label">Heure</label>
          <input type="time" class="form-control" name="appointment_time" id="appointment_time_edit" required />
        </div>
        <div class="mb-3">
          <label for="notes_edit" class="form-label">Notes</label>
          <textarea class="form-control" name="notes" id="notes_edit" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Remplir le modal de modification quand on clique sur Modifier
document.querySelectorAll('.btn-edit').forEach(button => {
  button.addEventListener('click', () => {
    const id = button.dataset.id;
    const patientId = button.dataset.patient_id;
    const date = button.dataset.date;
    const time = button.dataset.time;
    const notes = button.dataset.notes;

    document.getElementById('appointment_id_edit').value = id;
    document.getElementById('patient_id_edit').value = patientId;
    document.getElementById('appointment_date_edit').value = date;
    document.getElementById('appointment_time_edit').value = time;
    document.getElementById('notes_edit').value = notes;
  });
});
</script>

</body>
</html>
