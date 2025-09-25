<?php
// Fichier: /espace-animation.php
require_once 'partials/header.php';

// Sécurité : l'utilisateur doit être connecté et être un animateur.
if (!isset($_SESSION['user']) || !($_SESSION['user']['is_animateur'] ?? false)) {
    header('Location: login.php');
    exit;
}
?>

<title>Espace Animation - ColoMap</title>

<main class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-gray-900">
            Mon <span class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 bg-clip-text text-transparent">Espace Animation</span>
        </h1>
        <p class="mt-4 max-w-2xl mx-auto text-lg text-gray-500">
            Suivez l'avancement de vos candidatures et retrouvez les informations de vos prochains camps.
        </p>
    </div>

    <div id="loader" class="text-center py-10">
        <div class="loader inline-block"></div>
        <p class="mt-4 text-gray-600">Chargement de vos candidatures...</p>
    </div>

    <div id="content" class="hidden space-y-12">
        <div>
            <h2 class="text-2xl font-bold mb-6">Mes candidatures en attente</h2>
            <div id="pending-applications-list" class="space-y-6">
                </div>
        </div>

        <div>
            <h2 class="text-2xl font-bold mb-6">Mes camps acceptés</h2>
            <div id="accepted-applications-list" class="space-y-6">
                </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    const loader = document.getElementById('loader');
    const content = document.getElementById('content');
    const pendingList = document.getElementById('pending-applications-list');
    const acceptedList = document.getElementById('accepted-applications-list');

    try {
        const response = await fetch('api/get_my_applications.php');
        if (!response.ok) {
            throw new Error('Erreur lors de la récupération de vos candidatures.');
        }
        const data = await response.json();

        loader.classList.add('hidden');
        content.classList.remove('hidden');

        // Affichage des candidatures en attente
        if (data.pending.length === 0) {
            pendingList.innerHTML = `<div class="text-center py-10 bg-white rounded-lg shadow-md border"><p class="text-gray-500">Vous n'avez aucune candidature en attente.</p></div>`;
        } else {
            data.pending.forEach(app => {
                pendingList.innerHTML += createCampCard(app, 'pending');
            });
        }

        // Affichage des camps acceptés
        if (data.accepted.length === 0) {
            acceptedList.innerHTML = `<div class="text-center py-10 bg-white rounded-lg shadow-md border"><p class="text-gray-500">Vous n'avez pas encore été accepté à un camp.</p></div>`;
        } else {
            data.accepted.forEach(app => {
                acceptedList.innerHTML += createCampCard(app, 'accepted');
            });
        }

    } catch (error) {
        loader.innerHTML = `<p class="text-red-500 font-bold text-center py-10">${error.message}</p>`;
    }

    function createCampCard(app, status) {
        const statusInfo = status === 'pending'
            ? `<p class="text-sm font-semibold text-yellow-600">Statut : En attente</p>`
            : `<p class="text-sm font-semibold text-green-600">Statut : Accepté</p>`;

        return `
            <div class="bg-white rounded-xl shadow-lg border p-4 sm:p-6 flex flex-col sm:flex-row items-start gap-6">
                <img src="${app.camp_image_url}" alt="Image du camp" class="w-full sm:w-40 h-40 object-cover rounded-lg">
                <div class="flex-grow">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">${app.camp_nom}</h3>
                            <p class="text-sm text-gray-500">📍 ${app.camp_ville}</p>
                        </div>
                        ${statusInfo}
                    </div>
                    <div class="mt-4 border-t pt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="font-semibold text-gray-700">Infos Organisateur</p>
                            <p>${app.organisateur_nom}</p>
                            <p>${app.organisateur_mail}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-700">État des inscriptions</p>
                            <p>Jeunes inscrits : ${app.inscrits_enfants}</p>
                            <p>Animateurs inscrits : ${app.inscrits_animateurs}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
});
</script>
</body>
</html>