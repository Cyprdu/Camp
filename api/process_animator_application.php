<?php
// Fichier: /api/process_animator_application.php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['user']['id'])) { http_response_code(403); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$campId = $input['campId'] ?? null;
$motivation = $input['motivation'] ?? '';

if (empty($campId) || empty($motivation)) {
    http_response_code(400);
    echo json_encode(['error' => 'Données manquantes.']);
    exit;
}

try {
    $data = [
        'fields' => [
            'Candidat' => [$_SESSION['user']['id']],
            'Camp' => [$campId],
            'Motivation' => $motivation,
            'Statut' => 'En attente'
        ]
    ];

    $result = callAirtable('POST', 'Candidatures', $data);

    if (isset($result['error'])) {
        throw new Exception("Erreur lors de l'envoi de la candidature.");
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>