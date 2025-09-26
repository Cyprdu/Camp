<?php
// Fichier: /api/get_eligible_camps_for_animator.php (Corrigé et finalisé)
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['user']['id']) || !($_SESSION['user']['is_animateur'])) {
    echo json_encode([]);
    exit;
}

try {
    // 1. Récupérer les données de l'animateur connecté
    $userId = $_SESSION['user']['id'];
    $userRecord = callAirtable('GET', 'User', null, $userId);
    if (isset($userRecord['error'])) {
        echo json_encode(['error' => "Utilisateur non trouvé."]);
        exit;
    }
    $user = $userRecord['fields'];
    $user['id'] = $userRecord['id'];

    // 2. Récupérer TOUS les camps qui ont la "Gestion animateur" activée
    $formula = "{Gestion animateur} = 1";
    
    $campsResult = callAirtable('GET', 'Camps', ['filterByFormula' => $formula]);
    $allCamps = $campsResult['records'] ?? [];
    
    // 3. Récupérer les informations de TOUS les animateurs en une seule fois pour optimiser
    $allAnimatorsResult = callAirtable('GET', 'User', ['filterByFormula' => "{Annimateur} = 1", 'fields' => ['Sexe']]);
    $allAnimatorsData = [];
    if (!isset($allAnimatorsResult['error'])) {
        foreach($allAnimatorsResult['records'] as $anim) {
            $allAnimatorsData[$anim['id']] = $anim['fields'];
        }
    }

    $eligibleCamps = [];
    $today = new DateTime();
    $today->setTime(0, 0, 0); // Important pour comparer les dates correctement

    foreach ($allCamps as $campRecord) {
        $camp = $campRecord['fields'];
        $camp['id'] = $campRecord['id'];

        // --- NOUVELLE VÉRIFICATION DE DATE EN PHP ---
        // On vérifie si le camp est dans le futur
        if (empty($camp['Date début du camp'])) continue; 
        $campStartDate = new DateTime($camp['Date début du camp']);
        if ($campStartDate < $today) {
            continue; // Si le camp est déjà passé, on l'ignore et on passe au suivant
        }
        // --- FIN DE LA VÉRIFICATION DE DATE ---

        // A. Vérification de l'âge
        if ($camp['anim +18'] ?? false) {
            if (empty($user['Naissance'])) continue;
            $userBirthdate = new DateTime($user['Naissance']);
            $ageAtCampStart = $userBirthdate->diff($campStartDate)->y;
            if ($ageAtCampStart < 18) {
                continue; 
            }
        }
        
        // B. Vérification du BAFA
        if (($camp['BAFA ANIM'] ?? false) && !($user['BAFA'] ?? false)) {
            continue;
        }

        // C. Vérification des quotas
        $linkedAnimatorsIds = $camp['Annimateur'] ?? [];
        $totalQuota = $camp['quota max anim'] ?? 999;
        
        if (count($linkedAnimatorsIds) >= $totalQuota) {
            continue;
        }

        $maleAnimCount = 0;
        $femaleAnimCount = 0;
        foreach($linkedAnimatorsIds as $animId) {
            if(isset($allAnimatorsData[$animId])) {
                if(($allAnimatorsData[$animId]['Sexe'] ?? '') === 'Homme') $maleAnimCount++;
                if(($allAnimatorsData[$animId]['Sexe'] ?? '') === 'Femme') $femaleAnimCount++;
            }
        }
        
        $maleQuota = $camp['quota max anim GARCON'] ?? 0;
        $femaleQuota = $camp['quota max anim FILLE'] ?? 0;

        if (($user['Sexe'] ?? '') === 'Homme' && $maleQuota > 0 && $maleAnimCount >= $maleQuota) {
            continue;
        }
        if (($user['Sexe'] ?? '') === 'Femme' && $femaleQuota > 0 && $femaleAnimCount >= $femaleQuota) {
            continue;
        }

        // Si toutes les conditions sont passées, le camp est éligible
        $eligibleCamps[] = [
            'id' => $camp['id'],
            'nom' => $camp['nom'] ?? 'N/A',
            'ville' => $camp['Ville ou se déroule le camp'] ?? 'N/A',
            'prix' => $camp['prix anim'] ?? 0,
            'age_min' => $camp['Age min'] ?? 0,
            'age_max' => $camp['Age max'] ?? 0,
            'date_debut' => $camp['Date début du camp'] ?? '',
            'image_url' => $camp['illustration'][0]['url'] ?? 'https://placehold.co/600x400'
        ];
    }
    
    echo json_encode($eligibleCamps);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>