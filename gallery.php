<?php
require_once 'db.php';

// Définition de la langue (fr par défaut)
$lang = isset($_GET['l']) && $_GET['l'] == 'ar' ? 'ar' : 'fr';

try {
    // Récupération de toutes les photos de la galerie classées par ordre
    $queryGallery = $pdo->query("
        SELECT image_path, alt_$lang AS alt 
        FROM gallery 
        ORDER BY display_order ASC
    ");
    $galleryImages = $queryGallery->fetchAll();

    // Récupération des réglages pour la navigation et le footer
    $querySettings = $pdo->query("SELECT * FROM site_settings LIMIT 1");
    $siteSettings = $querySettings->fetch();
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Erreur lors du chargement de la galerie.");
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie | Duo des Reines</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --royal-gold: #D4AF37;
            --luxury-cream: #FDF8F5;
            --luxury-dark: #2D241E;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--luxury-cream);
            color: var(--luxury-dark);
            overflow-x: hidden;
        }

        h1,
        h2 {
            font-family: 'Playfair Display', serif;
        }

        .gold-gradient {
            background: linear-gradient(135deg, var(--royal-gold) 0%, #B68D40 100%);
        }

        /* Navbar */
        #navbar {
            background-color: var(--luxury-cream);
            border-bottom: 1px solid rgba(212, 175, 55, 0.05);
        }

        /* Galerie Cinématique */
        .gallery-container {
            position: relative;
            overflow: hidden;
            border-radius: 4px;
            cursor: zoom-in;
            height: 100%;
        }

        .gallery-img {
            transition: transform 1.2s cubic-bezier(0.2, 1, 0.3, 1);
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-container:hover .gallery-img {
            transform: scale(1.08);
        }

        .lightbox {
            position: fixed;
            inset: 0;
            background: rgba(26, 21, 18, 0.98);
            z-index: 300;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
        }

        .lightbox img {
            max-width: 90%;
            max-height: 85vh;
            border-radius: 2px;
            transform: scale(0.9);
            transition: transform 0.4s ease;
        }

        .lightbox.active img {
            transform: scale(1);
        }

        #mobile-menu {
            position: fixed;
            inset: 0;
            background: var(--luxury-cream);
            z-index: 200;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2.5rem;
            transform: translateY(-100%);
            transition: transform 0.5s cubic-bezier(0.77, 0, 0.175, 1);
        }

        #mobile-menu.active {
            transform: translateY(0);
        }
    </style>
</head>

<body class="pb-24 lg:pb-0">

    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <button class="absolute top-10 right-10 text-white text-3xl">✕</button>
        <img id="lightbox-img" src="" alt="Zoom">
    </div>

    <div id="mobile-menu">
        <button onclick="toggleMenu()" class="absolute top-10 right-10 text-2xl text-royal-gold">✕</button>
        <a href="index.html#about" class="text-3xl font-serif italic">L'Esprit</a>
        <a href="services.html" class="text-3xl font-serif italic">Soins</a>
        <a href="gallery.html" class="text-3xl font-serif italic">Galerie</a>
        <a href="index.html#contact" class="text-3xl font-serif italic">Contact</a>
    </div>

    <div class="bg-[#FDF8F5] border-b border-[#E6C98D]/10 py-2 px-4 text-center">
        <p class="text-[10px] tracking-[0.3em] uppercase font-bold text-[#D4AF37] animate-pulse">✨ Galerie Royale ✨</p>
    </div>

    <nav class="sticky top-0 w-full z-[100] flex justify-between items-center px-8 py-6" id="navbar">
        <div id="nav-brand" class="flex flex-col">
            <span class="text-xl font-bold tracking-tighter uppercase leading-none text-black">Duo des Reines</span>
            <span class="text-[8px] uppercase tracking-[0.3em] text-[#D4AF37]">Spa & Esthétique</span>
        </div>
        <div class="hidden lg:flex space-x-12 text-[10px] tracking-[0.3em] uppercase font-semibold text-gray-700">
            <a href="index.html#about" class="hover:text-royal-gold transition-colors">L'Esprit</a>
            <a href="services.html" class="hover:text-royal-gold transition-colors">Soins</a>
            <a href="gallery.html" class="text-royal-gold">Galerie</a>
            <a href="index.html#contact" class="hover:text-royal-gold transition-colors">Contact</a>
        </div>
        <a href="https://wa.me/212661597594" class="hidden lg:block gold-gradient text-white px-8 py-3 rounded-sm text-[10px] font-bold tracking-widest uppercase shadow-xl">Réservation</a>
    </nav>

    <section id="gallery" class="py-20 px-8 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 reveal">
                <h1 class="text-5xl md:text-7xl italic mb-4">Immersion Visuelle</h1>
                <p class="text-[#D4AF37] uppercase tracking-[0.3em] text-[10px] font-bold">Découvrez l'univers Duo des Reines</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($galleryImages)): ?>
                    <p class="col-span-full text-center text-gray-400 italic">Aucune photo dans la galerie pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($galleryImages as $index => $image): ?>
                        <div class="gallery-container group relative aspect-[4/5] overflow-hidden rounded-sm cursor-zoom-in reveal"
                            onclick="openLightbox('<?php echo $image['image_path']; ?>')">

                            <img
                                src="<?php echo $image['image_path']; ?>"
                                alt="<?php echo htmlspecialchars($image['alt']); ?>"
                                class="gallery-img w-full h-full object-cover">

                            <div class="absolute inset-0 bg-[#2D241E]/40 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                                <div class="border border-white/30 px-6 py-3">
                                    <span class="text-white text-[10px] uppercase tracking-[0.3em]">Agrandir</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="fixed bottom-0 left-0 w-full bg-white/95 backdrop-blur-md border-t border-gray-100 flex justify-around items-center py-3 z-[110] lg:hidden shadow-lg">
        <div class="text-[10px] font-bold text-[#D4AF37]">FR</div>
        <a href="https://wa.me/212661597594" class="gold-gradient p-4 rounded-full -mt-12 shadow-xl border-4 border-white text-white">
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
            </svg>
        </a>
        <button onclick="toggleMenu()" class="flex flex-col items-center text-[10px] uppercase tracking-widest font-bold text-gray-800">MENU</button>
    </div>

    <script>
        gsap.registerPlugin(ScrollTrigger);

        function toggleMenu() {
            document.getElementById('mobile-menu').classList.toggle('active');
        }

        function openLightbox(src) {
            const lb = document.getElementById('lightbox');
            document.getElementById('lightbox-img').src = src;
            lb.style.display = 'flex';
            gsap.to(lb, {
                opacity: 1,
                duration: 0.5,
                onComplete: () => lb.classList.add('active')
            });
        }

        function closeLightbox() {
            const lb = document.getElementById('lightbox');
            lb.classList.remove('active');
            gsap.to(lb, {
                opacity: 0,
                duration: 0.3,
                onComplete: () => {
                    lb.style.display = 'none';
                }
            });
        }
        gsap.utils.toArray(".gallery-container").forEach((img) => {
            gsap.from(img, {
                scrollTrigger: {
                    trigger: img,
                    start: "top 90%"
                },
                opacity: 0,
                scale: 0.9,
                y: 50,
                duration: 1.5
            });
        });
    </script>
</body>

</html>