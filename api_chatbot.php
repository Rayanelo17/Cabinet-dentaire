<?php
// c:/xampp/htdocs/Cabinet dentaire/api_chatbot.php
header('Content-Type: application/json');

// Recevoir le message posté
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if (!isset($input['message'])) {
    echo json_encode(["reply" => "Erreur : Aucun message reçu."]);
    exit();
}

$userMessage = $input['message'];

// Configuration pour Ollama en local
$ollamaUrl = 'http://localhost:11434/api/generate';
$model = 'llama3'; // ou 'mistral' selon ce qui est installé
$systemPrompt = "Tu es un assistant virtuel intelligent pour un cabinet dentaire. 
Ton rôle est de faire un pré-diagnostic très rapide et poli.
Si le patient mentionne une douleur forte, un gonflement ou un saignement, conseille-lui de prendre un rendez-vous d'URGENCE immédiatement.
Sinon, si c'est pour un contrôle ou un nettoyage, conseille-lui un rendez-vous de ROUTINE.
Sois toujours empathique, concis (2 phrases maximum) et parle en français. Ne propose pas de médicaments.";

// Préparation de la requête pour Ollama
$data = [
    "model" => $model,
    "system" => $systemPrompt,
    "prompt" => $userMessage,
    "stream" => false
];

$ch = curl_init($ollamaUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    echo json_encode(["reply" => "Désolé, l'assistant est temporairement indisponible (Ollama est-il lancé ?). Veuillez prendre rendez-vous directement."]);
    exit();
}
curl_close($ch);

if ($httpCode === 200) {
    $responseData = json_decode($response, true);
    $reply = $responseData['response'];
    echo json_encode(["reply" => $reply]);
} else {
    echo json_encode(["reply" => "Erreur lors de la communication avec le modèle IA."]);
}
?>
