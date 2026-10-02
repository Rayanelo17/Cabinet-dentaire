<?php
session_start();
header('Content-Type: application/json');
include("database.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

try {
    // 1. Revenus mensuels (6 derniers mois)
    $revenue_data = [];
    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(appointment_date, '%Y-%m') as month, SUM(amount) as total 
        FROM appointments 
        WHERE amount IS NOT NULL 
        GROUP BY month 
        ORDER BY month ASC
        LIMIT 6
    ");
    $stmt->execute();
    $res = $stmt->get_result();
    $revenue_labels = [];
    $revenue_values = [];
    while($row = $res->fetch_assoc()) {
        $revenue_labels[] = $row['month'];
        $revenue_values[] = floatval($row['total']);
    }

    // 2. Traitements par type
    $stmt2 = $conn->prepare("
        SELECT treatment_type, COUNT(*) as count 
        FROM appointments 
        WHERE treatment_type IS NOT NULL 
        GROUP BY treatment_type 
        ORDER BY count DESC
    ");
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $treatment_labels = [];
    $treatment_values = [];
    while($row = $res2->fetch_assoc()) {
        $treatment_labels[] = $row['treatment_type'];
        $treatment_values[] = intval($row['count']);
    }
    
    echo json_encode([
        "revenue" => [
            "labels" => $revenue_labels,
            "data" => $revenue_values
        ],
        "treatments" => [
            "labels" => $treatment_labels,
            "data" => $treatment_values
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
