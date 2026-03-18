<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Duo des Reines</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F8F9FA; }
        .sidebar { background-color: #2D241E; }
        .nav-link:hover { background-color: rgba(212, 175, 55, 0.1); color: #D4AF37; }
        .nav-link.active { background-color: #D4AF37; color: white; }
        .card { background: white; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="sidebar w-64 text-white hidden lg:flex flex-col fixed inset-y-0">
        <div class="p-8 text-center border-b border-white/10">
            <h1 class="text-xl font-bold tracking-tighter uppercase leading-none">Duo des Reines</h1>
            <span class="text-[8px] uppercase tracking-[0.3em] text-[#D4AF37]">Administration</span>
        </div>
        <nav class="flex-1 p-4 space-y-2 mt-4">
            <a href="#" class="nav-link active flex items-center p-3 rounded-lg transition" onclick="showSection('overview')">
                <span class="mr-3">📊</span> Vue d'ensemble
            </a>
            <a href="#" class="nav-link flex items-center p-3 rounded-lg transition" onclick="showSection('about')">
                <span class="mr-3">📝</span> Section About
            </a>
            <a href="#" class="nav-link flex items-center p-3 rounded-lg transition" onclick="showSection('services')">
                <span class="mr-3">✨</span> Services & Packs
            </a>
            <a href="#" class="nav-link flex items-center p-3 rounded-lg transition" onclick="showSection('gallery')">
                <span class="mr-3">📸</span> Galerie Photos
            </a>
            <a href="#" class="nav-link flex items-center p-3 rounded-lg transition" onclick="showSection('offers')">
                <span class="mr-3">🏷️</span> Offres & Promos
            </a>
        </nav>
        <div class="p-4 border-t border-white/10">
            <a href="index.html" class="flex items-center p-3 text-gray-400 hover:text-white">
                <span class="mr-3">🌐</span> Voir le site
            </a>
        </div>
    </aside>

    <main class="flex-1 lg:ml-64 p-8">
        
        <header class="flex justify-between items-center mb-10">
            <h2 id="section-title" class="text-2xl font-semibold text-gray-800">Vue d'ensemble</h2>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500">Bienvenue, Mouad</span>
                <div class="w-10 h-10 rounded-full bg-[#D4AF37] flex items-center justify-center text-white font-bold">M</div>
            </div>
        </header>

        <section id="overview-section" class="admin-section">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="card p-6 border-l-4 border-[#D4AF37]">
                    <p class="text-gray-500 text-sm">Services Actifs</p>
                    <h3 class="text-3xl font-bold">24</h3>
                </div>
                <div class="card p-6 border-l-4 border-red-500">
                    <p class="text-gray-500 text-sm">Promotions en cours</p>
                    <h3 class="text-3xl font-bold">5</h3>
                </div>
                <div class="card p-6 border-l-4 border-green-500">
                    <p class="text-gray-500 text-sm">Photos Galerie</p>
                    <h3 class="text-3xl font-bold">12</h3>
                </div>
            </div>
        </section>

        <section id="about-section" class="admin-section hidden">
            <div class="card p-8 max-w-4xl">
                <h3 class="text-lg font-medium mb-6">Modifier la section "À Propos"</h3>
                <form class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre de la section</label>
                        <input type="text" class="w-full border border-gray-300 p-3 rounded-md focus:ring-[#D4AF37] focus:border-[#D4AF37]" value="Sublimer la femme depuis plus de 6 ans.">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea rows="4" class="w-full border border-gray-300 p-3 rounded-md">Situé à Rabat, Duo des Reines est un sanctuaire où les rituels ancestraux rencontrent l'expertise moderne.</textarea>
                    </div>
                    <button type="submit" class="bg-[#2D241E] text-white px-6 py-3 rounded-md hover:bg-black transition">Enregistrer les modifications</button>
                </form>
            </div>
        </section>

        <section id="services-section" class="admin-section hidden">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium">Gestion des Services & Packs</h3>
                <button class="bg-[#D4AF37] text-white px-4 py-2 rounded-md text-sm">+ Nouveau Service</button>
            </div>
            <div class="card overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-4 text-xs uppercase text-gray-500">Service / Pack</th>
                            <th class="p-4 text-xs uppercase text-gray-500">Catégorie</th>
                            <th class="p-4 text-xs uppercase text-gray-500">Prix</th>
                            <th class="p-4 text-xs uppercase text-gray-500">Réduction</th>
                            <th class="p-4 text-xs uppercase text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="p-4 font-medium">Hammam Royal</td>
                            <td class="p-4"><span class="px-2 py-1 bg-blue-50 text-blue-600 text-[10px] rounded-full uppercase">Hammam</span></td>
                            <td class="p-4">300 DH</td>
                            <td class="p-4 text-red-500">-10%</td>
                            <td class="p-4">
                                <button class="text-blue-600 mr-3">Modifier</button>
                                <button class="text-red-600">Supprimer</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="p-4 font-medium">Pack Sérénité</td>
                            <td class="p-4"><span class="px-2 py-1 bg-purple-50 text-purple-600 text-[10px] rounded-full uppercase">Pack</span></td>
                            <td class="p-4">550 DH</td>
                            <td class="p-4 text-gray-400">Aucune</td>
                            <td class="p-4">
                                <button class="text-blue-600 mr-3">Modifier</button>
                                <button class="text-red-600">Supprimer</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="gallery-section" class="admin-section hidden">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="relative group aspect-square rounded-lg overflow-hidden border-2 border-dashed border-gray-300 flex items-center justify-center cursor-pointer hover:border-[#D4AF37]">
                    <span class="text-gray-400">+ Ajouter une photo</span>
                </div>
                <div class="relative aspect-square rounded-lg overflow-hidden card group">
                    <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                        <button class="bg-red-500 text-white p-2 rounded-full text-xs">Supprimer</button>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <script>
        function showSection(sectionId) {
            // Cacher toutes les sections
            document.querySelectorAll('.admin-section').forEach(s => s.classList.add('hidden'));
            // Retirer la classe active de tous les liens
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            
            // Afficher la section demandée
            document.getElementById(sectionId + '-section').classList.remove('hidden');
            // Mettre à jour le titre
            const titles = {
                'overview': 'Vue d\'ensemble',
                'about': 'Gestion Section About',
                'services': 'Gestion Services & Packs',
                'gallery': 'Gestion Galerie Photos',
                'offers': 'Gestion Offres & Promos'
            };
            document.getElementById('section-title').innerText = titles[sectionId];
            
            // Animation GSAP
            gsap.from('#' + sectionId + '-section', { opacity: 0, y: 10, duration: 0.4 });
        }
    </script>
</body>
</html>