<?php
session_start();
require_once 'db.php';

$lang = isset($_GET['l']) && $_GET['l'] == 'ar' ? 'ar' : 'fr';

try {
    // 1. Récupérer toutes les catégories pour les filtres
    $queryCats = $pdo->query("SELECT * FROM categories");
    $categories = $queryCats->fetchAll();

    // 2. Récupérer tous les services individuels avec leur nom de catégorie
    $queryServices = $pdo->query("
        SELECT s.*, s.title_$lang AS title, s.desc_$lang AS description, c.name_$lang AS cat_name, c.slug AS cat_slug 
        FROM services s
        LEFT JOIN categories c ON s.category_id = c.id
        ORDER BY s.id DESC
    ");
    $allServices = $queryServices->fetchAll();

    // 3. Récupérer tous les Packs
    $queryPacks = $pdo->query("SELECT *, name_$lang AS name, items_$lang AS items, badge_$lang AS badge FROM packs WHERE is_active = 1 ORDER BY id DESC");
    $allPacks = $queryPacks->fetchAll();

    // 4. Récupérer les réglages
    $querySettings = $pdo->query("SELECT * FROM site_settings LIMIT 1");
    $siteSettings = $querySettings->fetch();

    $queryPromo = $pdo->query("SELECT * FROM promotion_bar LIMIT 1");
    $promoBar = $queryPromo->fetch();
    
    // Formatage du numéro WhatsApp (retirer les espaces)
    $wa_number = preg_replace('/[^0-9]/', '', $siteSettings['phone_mobile'] ?? '212661597594');
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Erreur de chargement des services.");
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang == 'ar' ? 'rtl' : 'ltr'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Soins & Packs | Duo des Reines</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --royal-gold: #D4AF37;
            --luxury-cream: #FDF8F5;
            --luxury-dark: #2D241E;
        }

        body { font-family: 'Poppins', sans-serif; background-color: var(--luxury-cream); color: var(--luxury-dark); }
        h1, h2, h3 { font-family: 'Playfair Display', serif; }

        /* Navigation */
        #navbar { background-color: rgba(253, 248, 245, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(212, 175, 55, 0.1); }
        .gold-gradient { background: linear-gradient(135deg, var(--royal-gold) 0%, #B68D40 100%); }

        /* Menu Mobile */
        #mobile-menu {
            position: fixed; inset: 0; background: var(--luxury-cream); z-index: 200;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2.5rem;
            transform: translateY(-100%); transition: transform 0.5s cubic-bezier(0.77, 0, 0.175, 1);
        }
        #mobile-menu.active { transform: translateY(0); }

        /* Filtres */
        .filter-btn {
            padding: 0.75rem 1.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.1em;
            text-transform: uppercase; transition: all 0.3s ease; border: 1px solid rgba(212, 175, 55, 0.3); background: transparent; color: var(--luxury-dark);
        }
        .filter-btn.active, .filter-btn:hover { background: var(--royal-gold); color: white; border-color: var(--royal-gold); }

        /* Cartes */
        .service-item-card {
            background: white; border-radius: 4px; overflow: hidden; height: 100%; display: flex; flex-direction: column;
            transition: all 0.5s ease; border: 1px solid rgba(0,0,0,0.03); box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }
        .service-item-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(212, 175, 55, 0.1); border-color: rgba(212, 175, 55, 0.3); }
        
        .pack-badge {
            background: var(--royal-gold); color: white; padding: 6px 16px; font-size: 10px; text-transform: uppercase;
            font-weight: bold; letter-spacing: 0.1em; position: absolute; top: 15px; right: 15px; z-index: 10; border-radius: 2px;
        }
    </style>
</head>

<body class="pb-24 lg:pb-0">

    <div id="mobile-menu">
        <button onclick="toggleMenu()" class="absolute top-10 right-10 text-3xl text-[var(--royal-gold)]">✕</button>
        <a href="index.php#about" class="text-3xl font-serif italic">L'Esprit</a>
        <a href="services.php" class="text-3xl font-serif italic text-[var(--royal-gold)]">Soins</a>
        <a href="gallery.php" class="text-3xl font-serif italic">Galerie</a>
        <a href="index.php#contact" class="text-3xl font-serif italic">Contact</a>
        <?php if(isset($_SESSION['admin_id'])): ?>
            <a href="admin-dashboard.php" class="text-sm tracking-widest uppercase font-bold text-red-500 mt-10 border border-red-500 px-6 py-2 rounded">Dashboard Admin</a>
        <?php endif; ?>
    </div>

    <?php 
    if ($promoBar && $promoBar['is_active']) {
        echo '<div class="bg-[#2D241E] text-white text-center py-2 text-[10px] uppercase tracking-widest font-semibold">';
        echo htmlspecialchars($promoBar['text_' . $lang] ?? '');
        echo '</div>';
    }
    ?>

    <nav class="sticky top-0 w-full z-[100] flex justify-between items-center px-8 py-5" id="navbar">
        <a href="index.php" id="nav-brand" class="flex flex-col">
            <span class="text-2xl font-bold tracking-tighter uppercase leading-none text-black">Duo des Reines</span>
            <span class="text-[9px] uppercase tracking-[0.3em] text-[#D4AF37]">Spa & Esthétique</span>
        </a>
        <div class="hidden lg:flex space-x-10 text-[11px] tracking-[0.2em] uppercase font-bold text-gray-800 items-center">
            <a href="index.php#about" class="hover:text-[#D4AF37] transition-colors">L'Esprit</a>
            <a href="services.php" class="text-[#D4AF37] border-b-2 border-[#D4AF37] pb-1">Soins</a>
            <a href="gallery.php" class="hover:text-[#D4AF37] transition-colors">Galerie</a>
            <a href="index.php#contact" class="hover:text-[#D4AF37] transition-colors">Contact</a>
            
            <?php if(isset($_SESSION['admin_id'])): ?>
                <a href="admin-dashboard.php" class="bg-red-50 text-red-600 px-4 py-2 rounded shadow-sm hover:bg-red-500 hover:text-white transition-all">⚙️ Admin</a>
            <?php endif; ?>
        </div>
        <a href="https://wa.me/<?php echo $wa_number; ?>" class="hidden lg:block gold-gradient text-white px-8 py-3 rounded-sm text-[10px] font-bold tracking-widest uppercase shadow-lg transition hover:shadow-xl hover:-translate-y-0.5">Réservation</a>
    </nav>

    <header class="max-w-7xl mx-auto px-8 mt-20 mb-16 text-center">
        <span class="text-[10px] uppercase tracking-[0.4em] text-[#D4AF37] font-bold mb-4 block">Notre Catalogue</span>
        <h1 class="text-5xl md:text-7xl italic mb-6">L'Art du Soin</h1>
        <p class="text-gray-500 max-w-2xl mx-auto tracking-wide text-sm">Explorez nos rituels de beauté individuels et nos formules exclusives pensées pour votre bien-être absolu.</p>
    </header>

    <section class="max-w-7xl mx-auto px-8 mb-16">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex flex-wrap justify-center md:justify-start gap-3" id="filtersContainer">
                <button class="filter-btn active" data-filter="all"><?php echo ($lang == 'ar') ? 'الكل' : 'Tout'; ?></button>
                <button class="filter-btn" data-filter="packs">Packs & Formules</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="filter-btn" data-filter="<?php echo htmlspecialchars($cat['slug'] ?? ''); ?>">
                        <?php echo htmlspecialchars($cat['name_' . $lang] ?? ''); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="relative w-full md:w-72">
                <input type="text" id="serviceSearch" placeholder="Rechercher un soin..." class="w-full border border-gray-300 rounded px-4 py-3 pl-10 text-sm outline-none focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37] transition bg-white">
                <span class="absolute left-3 top-3 text-gray-400">🔍</span>
            </div>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 mb-24" id="servicesGrid">
        
        <?php foreach ($allPacks as $pack): ?>
            <article class="service-item-card group relative" data-category="packs">
                <?php if (!empty($pack['badge'])): ?>
                    <div class="pack-badge"><?php echo htmlspecialchars($pack['badge'] ?? ''); ?></div>
                <?php endif; ?>
                
                <div class="p-10 flex flex-col h-full bg-[#1A1512] text-white">
                    <span class="text-[10px] text-[#D4AF37] uppercase tracking-widest font-bold mb-4 text-center">Formule Exclusive</span>
                    <h3 class="text-3xl italic mb-8 text-center"><?php echo htmlspecialchars($pack['name'] ?? ''); ?></h3>
                    
                    <ul class="space-y-4 mb-10 text-sm flex-grow text-gray-300">
                        <?php 
                        $items = explode(',', $pack['items'] ?? '');
                        foreach ($items as $item): 
                            if(trim($item) === '') continue; // Ignore les éléments vides
                        ?>
                            <li class="flex items-center justify-center">
                                <span class="text-[#D4AF37] mr-2 text-lg leading-none">•</span>
                                <?php echo htmlspecialchars(trim($item)); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="mt-auto text-center border-t border-white/10 pt-6">
                        <span class="text-3xl font-bold text-[#D4AF37] block mb-4"><?php echo number_format((float)$pack['price'], 0); ?> DH</span>
                        <a href="https://wa.me/<?php echo $wa_number; ?>?text=Bonjour, je souhaite réserver la formule : <?php echo urlencode($pack['name'] ?? ''); ?>" class="inline-block w-full bg-[#D4AF37] text-white px-6 py-4 text-[10px] uppercase font-bold tracking-widest hover:bg-white hover:text-[#1A1512] transition-colors">Réserver ce pack</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <?php foreach ($allServices as $service): 
            $hasImage = !empty($service['image_path']);
            $hasPrice = !empty($service['price']) || !empty($service['discount_price']);
            $catSlug = htmlspecialchars($service['cat_slug'] ?? '');
        ?>
            <article class="service-item-card group relative" data-category="<?php echo $catSlug; ?>">
                
                <?php if ($hasImage): ?>
                    <div class="h-60 overflow-hidden relative">
                        <img src="<?php echo htmlspecialchars($service['image_path'] ?? ''); ?>" alt="<?php echo htmlspecialchars($service['title'] ?? ''); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <?php if (!empty($service['discount_price'])): ?>
                            <span class="absolute top-4 left-4 bg-red-500 text-white text-[10px] font-bold px-3 py-1 uppercase tracking-widest shadow-lg">Promo</span>
                        <?php endif; ?>
                    </div>
                    <div class="p-8 flex flex-col flex-grow">
                        <span class="text-[10px] text-[#D4AF37] uppercase tracking-widest font-bold mb-2"><?php echo htmlspecialchars($service['cat_name'] ?? ''); ?></span>
                        <h3 class="text-2xl italic mb-3"><?php echo htmlspecialchars($service['title'] ?? ''); ?></h3>
                        <p class="text-sm text-gray-500 mb-6 flex-grow leading-relaxed"><?php echo htmlspecialchars($service['description'] ?? ''); ?></p>
                        
                        <div class="border-t border-gray-100 pt-5 flex justify-between items-end mt-auto">
                            <div>
                                <?php if ($hasPrice): ?>
                                    <?php if (!empty($service['discount_price'])): ?>
                                        <span class="text-2xl font-bold text-red-500 block leading-none"><?php echo htmlspecialchars($service['discount_price']); ?> DH</span>
                                        <span class="text-xs text-gray-400 line-through"><?php echo htmlspecialchars($service['price']); ?> DH</span>
                                    <?php else: ?>
                                        <span class="text-xl font-bold text-[#D4AF37]"><?php echo htmlspecialchars($service['price']); ?> DH</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <a href="https://wa.me/<?php echo $wa_number; ?>?text=Bonjour, je souhaite réserver le soin : <?php echo urlencode($service['title'] ?? ''); ?>" class="text-[10px] uppercase font-bold tracking-widest text-black hover:text-[#D4AF37] transition flex items-center gap-2">Réserver <span class="text-lg leading-none">→</span></a>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="p-10 flex flex-col h-full bg-[#FCF9F7] border border-gray-100 group-hover:bg-white group-hover:border-[#D4AF37]/30 transition-all">
                        <div class="flex-grow flex flex-col items-center justify-center text-center">
                            <span class="text-[10px] text-[#D4AF37] uppercase tracking-widest font-bold mb-4 opacity-70"><?php echo htmlspecialchars($service['cat_name'] ?? ''); ?></span>
                            <h3 class="text-3xl italic mb-4 text-[#2D241E]"><?php echo htmlspecialchars($service['title'] ?? ''); ?></h3>
                            <div class="w-10 h-[1px] bg-[#D4AF37] mb-6 opacity-30"></div>
                            <p class="text-sm text-gray-500 leading-relaxed mb-8"><?php echo htmlspecialchars($service['description'] ?? ''); ?></p>
                            
                            <?php if ($hasPrice): ?>
                                <div class="mb-8">
                                    <?php if (!empty($service['discount_price'])): ?>
                                        <span class="text-3xl font-bold text-red-500 mr-2"><?php echo htmlspecialchars($service['discount_price']); ?> <span class="text-sm">DH</span></span>
                                        <span class="text-sm text-gray-400 line-through"><?php echo htmlspecialchars($service['price']); ?> DH</span>
                                    <?php else: ?>
                                        <span class="text-3xl font-bold text-[#D4AF37]"><?php echo htmlspecialchars($service['price']); ?> <span class="text-sm">DH</span></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-auto w-full">
                            <a href="https://wa.me/<?php echo $wa_number; ?>?text=Bonjour, je souhaite réserver : <?php echo urlencode($service['title'] ?? ''); ?>" class="block text-center border border-[#D4AF37] text-[#D4AF37] px-6 py-4 text-[10px] uppercase font-bold tracking-widest hover:bg-[#D4AF37] hover:text-white transition">Réserver ce soin</a>
                        </div>
                    </div>
                <?php endif; ?>

            </article>
        <?php endforeach; ?>
    </main>

    <footer class="bg-[#1A1512] text-white pt-20 pb-10 border-t-4 border-[#D4AF37]">
        <div class="max-w-7xl mx-auto px-8 grid grid-cols-1 md:grid-cols-3 gap-12 text-center md:text-left">
            <div>
                <h2 class="text-3xl italic mb-6">Duo des Reines</h2>
                <p class="text-gray-400 text-sm leading-relaxed mb-6">L'art de l'esthétique et du bien-être, pensé pour sublimer chaque femme dans une atmosphère de pure détente.</p>
                <div class="flex justify-center md:justify-start gap-4">
                    <a href="#" class="w-10 h-10 rounded-full border border-gray-700 flex items-center justify-center hover:bg-[#D4AF37] hover:border-[#D4AF37] transition text-xl">📸</a>
                    <a href="#" class="w-10 h-10 rounded-full border border-gray-700 flex items-center justify-center hover:bg-[#D4AF37] hover:border-[#D4AF37] transition text-xl">📘</a>
                </div>
            </div>
            <div>
                <h3 class="text-[12px] uppercase tracking-widest font-bold text-[#D4AF37] mb-6">Nos Coordonnées</h3>
                <ul class="text-gray-400 text-sm space-y-4">
                    <li class="flex items-start justify-center md:justify-start gap-3">
                        <span>📍</span>
                        <span><?php echo htmlspecialchars($siteSettings['address_' . $lang] ?? ''); ?></span>
                    </li>
                    <li class="flex items-center justify-center md:justify-start gap-3">
                        <span>📞</span>
                        <span><?php echo htmlspecialchars($siteSettings['phone_mobile'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-center justify-center md:justify-start gap-3">
                        <span>✉️</span>
                        <span><?php echo htmlspecialchars($siteSettings['email'] ?? ''); ?></span>
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="text-[12px] uppercase tracking-widest font-bold text-[#D4AF37] mb-6">Heures d'Ouverture</h3>
                <ul class="text-gray-400 text-sm space-y-3">
                    <li class="flex justify-between border-b border-gray-800 pb-2"><span>Mardi - Dimanche</span> <span class="text-white">10:00 - 20:00</span></li>
                    <li class="flex justify-between pb-2"><span>Lundi</span> <span class="text-[#D4AF37]">Fermé</span></li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-8 mt-16 pt-8 border-t border-gray-800 text-center text-xs text-gray-500">
            &copy; <?php echo date('Y'); ?> Duo des Reines. Tous droits réservés.
        </div>
    </footer>

    <div class="fixed bottom-0 left-0 w-full bg-white/95 backdrop-blur-md border-t border-gray-100 flex justify-around items-center py-3 z-[110] lg:hidden shadow-[0_-10px_20px_rgba(0,0,0,0.05)]">
        <a href="?l=<?php echo $lang == 'fr' ? 'ar' : 'fr'; ?>" class="text-[10px] font-bold text-[#D4AF37] border border-[#D4AF37] px-3 py-1 rounded">
            <?php echo $lang == 'fr' ? 'AR' : 'FR'; ?>
        </a>
        <a href="https://wa.me/<?php echo $wa_number; ?>" class="gold-gradient p-4 rounded-full -mt-12 shadow-xl border-4 border-white text-white transition-transform active:scale-95">
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
            </svg>
        </a>
        <button onclick="toggleMenu()" class="flex flex-col items-center text-[10px] uppercase tracking-widest font-bold text-gray-800">
            <span class="text-xl leading-none">☰</span>
            MENU
        </button>
    </div>

    <script>
        function toggleMenu() {
            document.getElementById('mobile-menu').classList.toggle('active');
        }
        
        const searchInput = document.getElementById('serviceSearch');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const cards = document.querySelectorAll('.service-item-card');

        function performFiltering() {
            const query = searchInput.value.toLowerCase();
            const activeBtn = document.querySelector('.filter-btn.active');
            const filter = activeBtn ? activeBtn.dataset.filter : 'all';

            cards.forEach(card => {
                const text = card.innerText.toLowerCase();
                const category = card.dataset.category || '';
                
                if (text.includes(query) && (filter === 'all' || category === filter)) {
                    card.style.display = 'flex'; 
                    gsap.fromTo(card, { opacity: 0, y: 15 }, { opacity: 1, y: 0, duration: 0.4 });
                } else {
                    card.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', performFiltering);
        
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                performFiltering();
            });
        });
    </script>
</body>
</html>