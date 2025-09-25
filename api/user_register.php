<?php
// Fichier: /api/user_register.php
// Version corrigée avec appel API manuel.

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
require_once 'config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $nom = $input['nom'] ?? null;
    $prenom = $input['prenom'] ?? null;
    $mail = $input['mail'] ?? null;
    $password = $input['password'] ?? null;
    $tel = $input['tel'] ?? '';

    if (!$nom || !$prenom || !$mail || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Tous les champs sont obligatoires.']);
        exit;
    }

    $tableName = 'User';
    $formula = "LOWER({mail}) = '" . strtolower(addslashes($mail)) . "'";
    
    // Appel API manuel pour vérifier si l'email existe
    $url_check = AIRTABLE_API_URL . AIRTABLE_BASE_ID . '/' . rawurlencode($tableName) . '?filterByFormula=' . urlencode($formula);
    $ch_check = curl_init($url_check);
    curl_setopt($ch_check, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch_check, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . AIRTABLE_API_KEY]);
    $response_check = curl_exec($ch_check);
    curl_close($ch_check);
    $result_check = json_decode($response_check, true);

    if (isset($result_check['records']) && !empty($result_check['records'])) {
        http_response_code(409);
        echo json_encode(['error' => 'Un compte avec cette adresse email existe déjà.']);
        exit;
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $data = [
        'fields' => [
            'nom' => $nom,
            'prenom' => $prenom,
            'mail' => $mail,
            'numero de tel' => $tel,
            'Mot de passe aché' => $hashed_password
        ]
    ];
    
    // Utilisation de la fonction callAirtable pour créer (POST), car elle fonctionne pour cette méthode.
    $createResult = callAirtable('POST', $tableName, $data);

    if (isset($createResult['error'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la création du compte.', 'details' => $createResult]);
    } else {
        http_response_code(201);
        echo json_encode(['success' => 'Compte créé avec succès !']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Une erreur fatale est survenue sur le serveur.', 'message' => $e->getMessage()]);
}
?>
