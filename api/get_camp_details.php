<?php
// Fichier: /api/get_camp_details.php
// Version finale, sécurisée et "défensive" pour éviter les erreurs JSON.

session_start();
header('Content-Type: application/json');
require_once 'config.php';

// Validation de l'entrée.
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de camp manquant.']);
    exit;
}

$campId = $_GET['id'];

try {
    // --- Étape 1: Récupérer les détails du camp ---
    $campRecord = callAirtable('GET', 'Camps', null, $campId);
    
    if (isset($campRecord['error'])) {
        http_response_code(404);
        echo json_encode(['error' => 'Camp introuvable.']);
        exit;
    }
    
    $fields = $campRecord['fields'];

    // --- Étape 2: Incrémenter les vues (inchangé) ---
    $currentViews = $fields['Vues'] ?? 0;
    try {
        callAirtable('PATCH', 'Camps', ['fields' => ['Vues' => ($currentViews + 1)]], $campId);
    } catch (Exception $e) { /* On ignore cette erreur */ }

    // --- Étape 3: Formater la réponse JSON de manière sécurisée ---
    $campDetails = [
        'id' => $campRecord['id'],
        'nom' => $fields['nom'] ?? 'N/A',
        'description' => !empty($fields['Déscription']) ? nl2br(htmlspecialchars($fields['Déscription'])) : '',
        'ville' => $fields['Ville ou se déroule le camp'] ?? 'N/A',
        'prix' => $fields['Prix conseillé'] ?? 0,
        'age_min' => $fields['Age min'] ?? 0,
        'age_max' => $fields['Age max'] ?? 0,
        'date_debut' => $fields['Date début du camp'] ?? null,
        'date_fin' => $fields['Date fin du camp'] ?? null,
        
        // CORRECTION DE SÉCURITÉ : On vérifie si le champ 'illustration' existe et n'est pas vide avant d'accéder à son contenu.
        'image_url' => !empty($fields['illustration']) ? $fields['illustration'][0]['url'] : 'https://placehold.co/1200x600/e2e8f0/cbd5e0?text=Image+manquante',
        
        'inscription_en_ligne' => $fields['Inscription en ligne'] ?? false,
        'inscription_hors_ligne' => $fields['Inscription hors ligne'] ?? false,
        
        // CORRECTION DE SÉCURITÉ : Même vérification pour le PDF.
        'pdf_url' => !empty($fields["dossier d'inscription"]) ? $fields["dossier d'inscription"][0]['url'] : null,
        
        'adresse_retour' => $fields['adresse retour dossier'] ?? null,
        
        // CORRECTION DE SÉCURITÉ : On vérifie si l'organisateur est lié.
        'organisateur_id' => !empty($fields['Organisme']) ? $fields['Organisme'][0] : null,
        
        'inscrits' => $fields['Inscrit'] ?? [],
        'remise' => (int)($fields['Remise si plusieurs enfants'] ?? 0),

        // On lit directement les champs depuis Airtable comme demandé.
        'quota_max_filles' => (int)($fields['MAX FILLE'] ?? 0),
        'quota_max_garcons' => (int)($fields['MAX GARCON'] ?? 0),
        'filles_inscrites' => (int)($fields['Fille inscrit'] ?? 0),
        'garcons_inscrits' => (int)($fields['Garçon inscrit'] ?? 0),

        // Calculs simples qui ne risquent pas d'échouer.
        'places_restantes' => (int)($fields['quota'] ?? 0) - (isset($fields['Inscrit']) ? count($fields['Inscrit']) : 0),
        'vues' => $currentViews + 1,
        'likes' => isset($fields['User Favories']) ? count($fields['User Favories']) : 0,
    ];

    echo json_encode($campDetails);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>