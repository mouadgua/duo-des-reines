<?php
require_once 'db.php';

$lang = isset($_GET['l']) && $_GET['l'] == 'ar' ? 'ar' : 'fr';

try {
    // 1. Récupérer toutes les catégories pour les filtres
    $queryCats = $pdo->query("SELECT * FROM categories");
    $categories = $queryCats->fetchAll();

    // 2. Récupérer tous les services individuels avec leur nom de catégorie
    $queryServices = $pdo->query("
        SELECT s.*, s.title_$lang AS title, s.desc_$lang AS description, c.slug AS cat_slug 
        FROM services s
        LEFT JOIN categories c ON s.category_id = c.id
        ORDER BY s.id DESC
    ");
    $allServices = $queryServices->fetchAll();

    // 3. Récupérer tous les Packs
    $queryPacks = $pdo->query("SELECT *, name_$lang AS name, items_$lang AS items, badge_$lang AS badge FROM packs WHERE is_active = 1");
    $allPacks = $queryPacks->fetchAll();

    // 4. Récupérer les réglages (pour la navbar/barre promo)
    $querySettings = $pdo->query("SELECT * FROM site_settings LIMIT 1");
    $siteSettings = $querySettings->fetch();

    $queryPromo = $pdo->query("SELECT * FROM promotion_bar LIMIT 1");
    $promoBar = $queryPromo->fetch();
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Erreur de chargement des services.");
}
?>


<!DOCTYPE html>
<html lang="fr">

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

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--luxury-cream);
            color: var(--luxury-dark);
        }

        h1,
        h2,
        h3 {
            font-family: 'Playfair Display', serif;
        }

        /* Navbar & Navigation Styles */
        #navbar {
            background-color: var(--luxury-cream);
            border-bottom: 1px solid rgba(212, 175, 55, 0.05);
        }

        .gold-gradient {
            background: linear-gradient(135deg, var(--royal-gold) 0%, #B68D40 100%);
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

        /* Filtres & Cartes */
        .filter-btn {
            padding: 0.6rem 1.5rem;
            border-radius: 2px;
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            transition: all 0.4s ease;
            border: 1px solid rgba(212, 175, 55, 0.2);
            background: white;
        }

        .filter-btn.active {
            background: var(--luxury-dark);
            color: white;
            border-color: var(--luxury-dark);
        }

        .service-item-card {
            background: white;
            border-radius: 2px;
            overflow: hidden;
            height: 100%;
            transition: all 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid rgba(212, 175, 55, 0.05);
        }

        .service-item-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            border-color: var(--royal-gold);
        }

        .pack-badge {
            background: var(--royal-gold);
            color: white;
            padding: 4px 12px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
        }
    </style>
</head>

<body class="pb-24 lg:pb-0">

    <div id="mobile-menu">
        <button onclick="toggleMenu()" class="absolute top-10 right-10 text-2xl text-royal-gold">✕</button>
        <a href="index.html#about" class="text-3xl font-serif italic">L'Esprit</a>
        <a href="services.html" class="text-3xl font-serif italic">Soins</a>
        <a href="gallery.html" class="text-3xl font-serif italic">Galerie</a>
        <a href="index.html#contact" class="text-3xl font-serif italic">Contact</a>
    </div>

    <?php include('./components/promobar.php') ?>

    <nav class="sticky top-0 w-full z-[100] flex justify-between items-center px-8 py-6" id="navbar">
        <div id="nav-brand" class="flex flex-col">
            <span class="text-xl font-bold tracking-tighter uppercase leading-none text-black">Duo des Reines</span>
            <span class="text-[8px] uppercase tracking-[0.3em] text-[#D4AF37]">Spa & Esthétique</span>
        </div>
        <div class="hidden lg:flex space-x-12 text-[10px] tracking-[0.3em] uppercase font-semibold text-gray-700">
            <a href="index.html#about" class="hover:text-royal-gold transition-colors">L'Esprit</a>
            <a href="services.html" class="text-royal-gold">Soins</a>
            <a href="gallery.html" class="hover:text-royal-gold transition-colors">Galerie</a>
            <a href="index.html#contact" class="hover:text-royal-gold transition-colors">Contact</a>
        </div>
        <a href="https://wa.me/212661597594" class="hidden lg:block gold-gradient text-white px-8 py-3 rounded-sm text-[10px] font-bold tracking-widest uppercase shadow-xl transition hover:brightness-110">Réservation</a>
    </nav>

    <header class="max-w-7xl mx-auto px-8 my-16 text-center">
        <h1 class="text-5xl md:text-7xl italic mb-6">L'Art du Soin</h1>
        <p class="text-gray-500 max-w-2xl mx-auto tracking-wide">Explorez nos rituels individuels et nos packs exclusifs.</p>
    </header>

    <section class="max-w-7xl mx-auto px-8 mb-12">
        <div class="flex flex-wrap gap-3" id="filtersContainer">
            <button class="filter-btn active" data-filter="all">
                <?php echo ($lang == 'ar') ? 'الكل' : 'Tout'; ?>
            </button>

            <?php foreach ($categories as $cat): ?>
                <button class="filter-btn" data-filter="<?php echo $cat['slug']; ?>">
                    <?php echo $cat['name_' . $lang]; ?>
                </button>
            <?php endforeach; ?>

            <button class="filter-btn" data-filter="promotion">
                <?php echo ($lang == 'ar') ? 'العروض' : 'Promotions'; ?>
            </button>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 mb-20" id="servicesGrid">
        <?php foreach ($allPacks as $pack): ?>
            <article class="service-item-card group relative" data-category="pack">
                <div class="pack-badge"><?php echo $pack['badge']; ?></div>
                <div class="p-10 flex flex-col h-full <?php echo ($pack['id'] % 2 == 0) ? 'bg-[#1A1512] text-white' : ''; ?>">
                    <h3 class="text-3xl italic mb-6"><?php echo $pack['name']; ?></h3>
                    <ul class="space-y-4 mb-10 text-sm flex-grow <?php echo ($pack['id'] % 2 == 0) ? 'text-gray-400' : 'text-gray-500'; ?>">
                        <?php
                        $items = explode(',', $pack['items']);
                        foreach ($items as $item): ?>
                            <li class="flex items-center">
                                <span class="w-1.5 h-1.5 bg-[#D4AF37] rounded-full mr-3"></span>
                                <?php echo trim($item); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-6">
                        <span class="text-2xl font-bold text-[#D4AF37]"><?php echo number_format($pack['price'], 0); ?> DH</span>
                        <a href="https://wa.me/<?php echo $siteSettings['phone_mobile']; ?>" class="text-[10px] uppercase font-bold tracking-widest border-b border-[#D4AF37] pb-1">Réserver</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </main>

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
        function toggleMenu() {
            document.getElementById('mobile-menu').classList.toggle('active');
        }
        const searchInput = document.getElementById('serviceSearch');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const cards = document.querySelectorAll('.service-item-card');

        function performFiltering() {
            const query = searchInput.value.toLowerCase();
            const filter = document.querySelector('.filter-btn.active').dataset.filter;
            cards.forEach(card => {
                const text = card.innerText.toLowerCase();
                const categories = card.dataset.category;
                if (text.includes(query) && (filter === 'all' || categories.includes(filter))) {
                    card.style.display = 'block';
                    gsap.fromTo(card, {
                        opacity: 0,
                        y: 20
                    }, {
                        opacity: 1,
                        y: 0,
                        duration: 0.5
                    });
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