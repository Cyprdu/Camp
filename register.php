<?php require_once 'partials/header.php'; ?>
<title>Inscription - TrouveTonCamp</title>

<main class="container mx-auto px-4 py-16 flex justify-center">
    <div class="w-full max-w-lg">
        <form id="register-form" class="bg-white shadow-lg rounded-xl px-8 pt-6 pb-8 mb-4">
            <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Créer un compte</h1>
            
            <div id="message-area" class="mb-4 text-center"></div>

            <div class="flex flex-wrap -mx-3 mb-4">
                <div class="w-full md:w-1/2 px-3 mb-4 md:mb-0">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="prenom">Prénom</label>
                    <input class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" id="prenom" type="text" required>
                </div>
                <div class="w-full md:w-1/2 px-3">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nom">Nom</label>
                    <input class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" id="nom" type="text" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="mail">Email</label>
                <input class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" id="mail" type="email" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="tel">Téléphone (Optionnel)</label>
                <input class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" id="tel" type="tel">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Mot de passe</label>
                <input class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" id="password" type="password" required>
            </div>
            
            <div class="flex items-center justify-between">
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none w-full transition duration-300" type="submit">
                    S'inscrire
                </button>
            </div>
            <p class="text-center text-gray-500 text-sm mt-6">
                Déjà un compte ? <a class="font-bold text-blue-600 hover:text-blue-800" href="login.php">Connectez-vous</a>
            </p>
        </form>
    </div>
</main>

<script>
document.getElementById('register-form').addEventListener('submit', async function(event) {
    event.preventDefault();

    const nom = document.getElementById('nom').value;
    const prenom = document.getElementById('prenom').value;
    const mail = document.getElementById('mail').value;
    const tel = document.getElementById('tel').value;
    const password = document.getElementById('password').value;
    const messageArea = document.getElementById('message-area');
    messageArea.innerHTML = '<p class="text-blue-500">Création du compte...</p>';

    try {
        const response = await fetch('api/user_register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nom, prenom, mail, tel, password })
        });

        const result = await response.json();

        if (response.ok) {
            messageArea.innerHTML = `<p class="text-green-500 font-bold">${result.success}</p>`;
            // Optionnel: rediriger vers la page de connexion après un court délai
            setTimeout(() => { window.location.href = 'login.php'; }, 2000);
        } else {
            messageArea.innerHTML = `<p class="text-red-500 font-bold">${result.error}</p>`;
        }
    } catch (error) {
        messageArea.innerHTML = `<p class="text-red-500 font-bold">Une erreur de communication est survenue.</p>`;
    }
});
</script>
</body>
</html>
