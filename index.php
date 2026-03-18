<?php
require_once 'db.php';

// Définition de la langue (fr par défaut)
$lang = isset($_GET['l']) && $_GET['l'] == 'ar' ? 'ar' : 'fr';

try {
  // 1. Récupération Section About
  $queryAbout = $pdo->query("SELECT * FROM about_section LIMIT 1");
  $aboutData = $queryAbout->fetch();

  // 2. Récupération Mot de la Gérante (Nouvelle Table)
  $queryGerante = $pdo->query("SELECT * FROM gerante_section LIMIT 1");
  $geranteData = $queryGerante->fetch();

  // 3. Récupération Contact & Localisation (Nouvelle Table site_settings)
  $querySettings = $pdo->query("SELECT * FROM site_settings LIMIT 1");
  $siteSettings = $querySettings->fetch();

  // Récupération de la barre de promotion
  $queryPromoBar = $pdo->query("SELECT * FROM promotion_bar LIMIT 1");
  $promoBar = $queryPromoBar->fetch();

  // 4. Récupération des services "Featured" (Timeline GSAP)
  $queryServices = $pdo->query("
        SELECT id, title_$lang AS title, desc_$lang AS description, image_path 
        FROM services 
        WHERE is_featured = 1 
        ORDER BY created_at ASC 
        LIMIT 4
    ");
  $featuredServices = $queryServices->fetchAll();
  // 5. Récupération des offres spéciales (Promotion section)
  $queryOffers = $pdo->query("
        SELECT title_$lang AS title, old_price, new_price, image_path 
        FROM special_offers 
        WHERE is_active = 1 
        AND (expiry_date >= CURDATE() OR expiry_date IS NULL)
    ");
  $specialOffers = $queryOffers->fetchAll();

  // 6. Récupération des photos de la galerie
  $queryGallery = $pdo->query("
        SELECT image_path, alt_$lang AS alt 
        FROM gallery 
        ORDER BY display_order ASC 
        LIMIT 3
    ");
  $galleryImages = $queryGallery->fetchAll();
} catch (PDOException $e) {
  // Log d'erreur discret
  error_log($e->getMessage());
  die("Une erreur de chargement est survenue.");
}
?>



<!doctype html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Duo des Reines | Spa & Esthétique Rabat</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap"
    rel="stylesheet" />

  <style>
    :root {
      --royal-gold: #d4af37;
      --luxury-cream: #fdf8f5;
      --luxury-dark: #2d241e;
      --soft-gold: #e6c98d;
    }

    body {
      font-family: "Poppins", sans-serif;
      background-color: var(--luxury-cream);
      color: var(--luxury-dark);
      overflow-x: hidden;
    }

    h1,
    h2,
    h3 {
      font-family: "Playfair Display", serif;
    }

    .gold-gradient {
      background: linear-gradient(135deg, var(--royal-gold) 0%, #b68d40 100%);
    }

    .hero-gradient-overlay {
      background: linear-gradient(to bottom,
          rgba(45, 36, 30, 0.4),
          rgba(212, 175, 55, 0.2));
    }

    /* Navbar Style */
    #navbar {
      background-color: var(--luxury-cream);
      transition: all 0.4s ease-in-out;
      border-bottom: 1px solid rgba(230, 201, 141, 0.1);
    }

    .nav-scrolled {
      background-color: rgba(253, 248, 245, 0.98) !important;
      padding: 0.5rem 1.5rem !important;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    /* Mobile Menu Overlay */
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

    .reveal {
      opacity: 0;
      transform: translateY(30px);
    }

    /* Timeline Horizontal Services */
    #services-pin-wrapper {
      background-color: var(--luxury-dark);
    }

    .services-horizontal-container {
      display: flex;
      width: 500%;
      height: 100vh;
      align-items: center;
      position: relative;
    }

    .timeline-progress-container {
      position: absolute;
      top: 65%;
      left: 5%;
      width: 90%;
      height: 2px;
      background: rgba(212, 175, 55, 0.1);
      z-index: 5;
    }

    .timeline-progress-bar {
      height: 100%;
      width: 0%;
      background: var(--royal-gold);
      box-shadow: 0 0 15px var(--royal-gold);
    }

    .service-panel {
      width: 100vw;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-shrink: 0;
      position: relative;
      padding: 0 5vw;
    }

    .luxury-card-giant {
      position: relative;
      width: 85%;
      height: 75%;
      max-width: 1200px;
      background-size: cover;
      background-position: center;
      display: flex;
      align-items: center;
      justify-content: flex-start;
      padding: 2rem;
      border-radius: 4px;
      overflow: hidden;
      box-shadow: 0 50px 100px rgba(0, 0, 0, 0.5);
    }

    .luxury-card-giant::before {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg,
          rgba(45, 36, 30, 0.95) 20%,
          rgba(45, 36, 30, 0.2) 100%);
    }

    .card-content-wrap {
      position: relative;
      z-index: 10;
      max-width: 600px;
    }

    .giant-id {
      font-family: "Playfair Display", serif;
      font-size: 6rem;
      color: var(--royal-gold);
      opacity: 0.15;
      line-height: 1;
      margin-bottom: -1rem;
    }

    .luxury-card-giant h3 {
      font-size: 3.5rem;
      line-height: 1.1;
      margin-bottom: 1.5rem;
      color: white;
    }

    @media (min-width: 1024px) {
      .luxury-card-giant {
        padding: 5rem;
      }

      .luxury-card-giant h3 {
        font-size: 4.5rem;
      }

      .giant-id {
        font-size: 8rem;
        margin-bottom: -2rem;
      }
    }

    .no-scrollbar::-webkit-scrollbar {
      display: none;
    }

    .no-scrollbar {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    @media (max-width: 1024px) {
      .about-image-full {
        width: 100vw !important;
        margin-left: calc(-50vw + 50%);
        margin-right: calc(-50vw + 50%);
      }
    }

    /* Styles Galerie Cinématique */
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

    /* Lightbox (Interface de zoom) */
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
  </style>
</head>

<body class="pb-20 lg:pb-0">
  <div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <button class="absolute top-10 right-10 text-white text-3xl">✕</button>
    <img id="lightbox-img" src="" alt="Zoom Image" />
  </div>
  <div id="mobile-menu">
    <button
      onclick="toggleMenu()"
      class="absolute top-10 right-10 text-2xl text-royal-gold">
      ✕
    </button>
    <a
      href="#about"
      onclick="toggleMenu()"
      class="text-3xl font-serif italic"
      data-key="nav_esprit">L'Esprit</a>
    <a
      href="#categories"
      onclick="toggleMenu()"
      class="text-3xl font-serif italic"
      data-key="nav_soins">Soins</a>
    <a
      href="#promotion"
      onclick="toggleMenu()"
      class="text-3xl font-serif italic"
      data-key="nav_offres">Offres</a>
    <a
      href="#gallery"
      onclick="toggleMenu()"
      class="text-3xl font-serif italic"
      data-key="nav_galerie">Galerie</a>
    <a
      href="#contact"
      onclick="toggleMenu()"
      class="text-3xl font-serif italic"
      data-key="nav_contact">Contact</a>
  </div>

  <?php include('./components/promobar.php') ?>

  <nav
    class="sticky top-0 w-full z-[100] flex justify-between items-center px-8 py-6 transition-all duration-500"
    id="navbar">
    <div id="nav-brand" class="flex flex-col">
      <span
        class="text-xl font-bold tracking-tighter uppercase leading-none text-black">Duo des Reines</span>
      <span
        class="text-[8px] uppercase tracking-[0.3em] text-[#D4AF37]"
        data-key="site_slogan">Spa & Esthétique</span>
    </div>
    <div
      class="hidden lg:flex space-x-12 text-[10px] tracking-[0.3em] uppercase font-semibold text-gray-700">
      <a
        href="#about"
        class="hover:text-royal-gold transition-colors"
        data-key="nav_esprit">L'Esprit</a>
      <a
        href="#categories"
        class="hover:text-royal-gold transition-colors"
        data-key="nav_soins">Soins</a>
      <a
        href="#promotion"
        class="hover:text-royal-gold transition-colors"
        data-key="nav_offres">Offres</a>
      <a
        href="#gallery"
        class="hover:text-royal-gold transition-colors"
        data-key="nav_galerie">Galerie</a>
      <a
        href="#contact"
        class="hover:text-royal-gold transition-colors"
        data-key="nav_contact">Contact</a>
    </div>
    <a
      href="https://wa.me/212661597594"
      class="hidden lg:block gold-gradient text-white px-8 py-3 rounded-sm text-[10px] font-bold tracking-widest uppercase shadow-xl transition hover:brightness-110"
      data-key="btn_reserve">Réservation</a>
  </nav>

  <div
    class="fixed bottom-0 left-0 w-full bg-white/95 backdrop-blur-md border-t border-gray-100 flex justify-around items-center py-3 z-[110] lg:hidden shadow-lg">
    <button
      onclick="toggleLang()"
      class="flex flex-col items-center text-[10px] uppercase tracking-widest font-bold text-[#D4AF37]">
      <span id="lang-indicator">FR</span>
    </button>
    <a
      href="https://wa.me/212661597594"
      class="gold-gradient p-4 rounded-full -mt-12 shadow-xl border-4 border-white text-white">
      <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
        <path
          d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
      </svg>
    </a>
    <button
      onclick="toggleMenu()"
      class="flex flex-col items-center text-[10px] uppercase tracking-widest font-bold text-gray-800"
      data-key="nav_menu">
      MENU
    </button>
  </div>

  <header
    class="h-[85vh] lg:h-screen relative overflow-hidden flex items-center justify-center">
    <img
      src="https://images.unsplash.com/photo-1544161515-4af6b1d462c2?auto=format&fit=crop&q=80"
      class="absolute inset-0 w-full h-full object-cover scale-110"
      id="hero-bg" />
    <div class="absolute inset-0 hero-gradient-overlay"></div>
    <div class="relative z-10 text-center px-6">
      <h1
        class="text-6xl lg:text-9xl text-white mb-6 italic reveal drop-shadow-lg">
        Duo des Reines
      </h1>
      <p
        class="text-white/90 text-[10px] lg:text-xl tracking-[0.5em] uppercase font-light reveal"
        data-key="hero_sub">
        L'éveil des sens au féminin
      </p>
    </div>
  </header>

  <section id="about" class="py-24 lg:py-32 px-8 max-w-7xl mx-auto flex flex-col lg:flex-row gap-16 lg:gap-24 items-center">
    <div class="reveal lg:w-1/2 order-1 lg:order-2">
      <span class="text-[#D4AF37] font-bold tracking-[0.4em] uppercase text-[10px] mb-4 block">
        Héritage & Passion
      </span>
      <h2 class="text-4xl lg:text-6xl mb-8 leading-tight italic text-black">
        <?php echo $aboutData['title_' . $lang]; ?>
      </h2>
      <p class="text-gray-500 text-lg leading-relaxed mb-10">
        <?php echo $aboutData['desc_' . $lang]; ?>
      </p>
      <a href="services.html" class="inline-block border-b border-[#D4AF37] pb-2 text-[10px] uppercase tracking-[0.3em] font-bold hover:text-[#D4AF37] transition-colors">
        Découvrir nos rituels
      </a>
    </div>
    <div class="reveal lg:w-1/2 order-2 lg:order-1 about-image-full">
      <img
        src="<?php echo $aboutData['image_path']; ?>"
        class="w-full h-[400px] lg:h-[600px] object-cover shadow-2xl rounded-sm"
        alt="Duo des Reines Spa" />
    </div>
  </section>

  <div id="services-pin-wrapper">
    <section id="categories" class="overflow-hidden">
      <div class="services-horizontal-container" id="horizontal-scroll">
        <div class="timeline-progress-container">
          <div class="timeline-progress-bar" id="progress-line"></div>
        </div>

        <div
          class="service-panel flex-col items-start bg-[#1a1512] text-white">
          <div class="px-10 md:px-32">
            <span
              class="text-gold font-bold tracking-[0.8em] uppercase text-xs mb-6 block reveal">Prestige</span>
            <h2
              class="text-5xl md:text-[8rem] italic leading-none font-serif"
              data-key="cat_title">
              L'Art de la<br />Renaissance
            </h2>
            <p
              class="text-gold mt-20 tracking-widest uppercase text-xs animate-pulse">
              Scroll Down →
            </p>
          </div>
        </div>

        <div class="service-panel">
          <div
            class="luxury-card-giant"
            style="
                background-image: url(&quot;https://images.unsplash.com/photo-1596178065887-1198b6148b2b?auto=format&fit=crop&q=80&quot;);
              ">
            <div class="card-content-wrap">
              <span class="giant-id">01</span>
              <h3 class="italic" data-key="cat_name_1">Hammam Royal</h3>
              <p data-key="cat_desc_1">
                Rituels de purification traditionnels au savon noir précieux
                et gommage aux senteurs d'Orient.
              </p>
              <div class="w-20 h-1 bg-gold mt-10"></div>
            </div>
          </div>
        </div>
        <div class="service-panel">
          <div
            class="luxury-card-giant"
            style="
                background-image: url(&quot;https://images.unsplash.com/photo-1544161515-4af6b1d462c2?auto=format&fit=crop&q=80&quot;);
              ">
            <div class="card-content-wrap">
              <span class="giant-id">02</span>
              <h3 class="italic" data-key="cat_name_2">Massages</h3>
              <p data-key="cat_desc_2">
                Une symphonie de bien-être : relaxant, pierres chaudes ou
                drainage lymphatique.
              </p>
              <div class="w-20 h-1 bg-gold mt-10"></div>
            </div>
          </div>
        </div>
        <div class="service-panel">
          <div
            class="luxury-card-giant"
            style="
                background-image: url(&quot;https://images.unsplash.com/photo-1614806687431-035900cc2f15?auto=format&fit=crop&q=80&quot;);
              ">
            <div class="card-content-wrap">
              <span class="giant-id">03</span>
              <h3 class="italic" data-key="cat_name_3">Esthétique</h3>
              <p data-key="cat_desc_3">
                Soin du visage haute performance et onglerie haute couture
                pour un éclat royal.
              </p>
              <div class="w-20 h-1 bg-gold mt-10"></div>
            </div>
          </div>
        </div>
        <div class="service-panel">
          <div
            class="luxury-card-giant"
            style="
                background-image: url(&quot;https://images.unsplash.com/photo-1560750588-73207b1ef5b8?auto=format&fit=crop&q=80&quot;);
              ">
            <div class="card-content-wrap">
              <span class="giant-id">04</span>
              <h3 class="italic" data-key="cat_name_4">Coiffure</h3>
              <p data-key="cat_desc_4">
                Colorations couture, rituels de soins profonds et brushings
                signatures.
              </p>
              <div class="w-20 h-1 bg-gold mt-10"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <section class="py-32 px-8 max-w-7xl mx-auto flex flex-col md:flex-row gap-20 items-center">
    <img
      src="<?php echo $geranteData['image_path']; ?>"
      class="w-64 h-64 md:w-80 md:h-80 rounded-full object-cover shadow-2xl reveal border-2 border-gold/20"
      alt="Gérante Duo des Reines" />
    <div class="reveal">
      <span class="text-gold font-bold tracking-[0.3em] uppercase text-[10px] mb-4 block">
        <?php echo $geranteData['tag_' . $lang]; ?>
      </span>
      <h3 class="text-4xl mb-6 italic">
        <?php echo $geranteData['title_' . $lang]; ?>
      </h3>
      <p class="text-gray-500 italic text-lg leading-relaxed max-w-2xl">
        "<?php echo $geranteData['quote_' . $lang]; ?>"
      </p>
    </div>
  </section>

  <section id="promotion" class="py-24 bg-[#1A1512] text-white">
    <div class="max-w-7xl mx-auto px-8 text-center md:text-left">
      <h2
        class="text-4xl lg:text-7xl mb-12 italic reveal"
        data-key="promo_head">
        L'Offre du Moment
      </h2>
      <div
        class="flex overflow-x-auto no-scrollbar snap-x snap-mandatory gap-6 pb-10">
        <div
          class="min-w-[85%] lg:min-w-[450px] snap-center bg-white/5 p-8 border border-white/10 rounded-sm reveal">
          <img
            src="https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&q=80"
            class="w-full h-56 object-cover mb-6 opacity-60"
            alt="Promo" />
          <h3 class="text-2xl mb-4 italic" data-key="promo_name_1">
            Rituel Hammam Signature
          </h3>
          <p class="text-gold text-2xl font-bold">
            450 DH
            <span class="text-sm line-through text-gray-600 ml-4">600 DH</span>
          </p>
        </div>
      </div>
    </div>
  </section>

  <section id="gallery" class="py-32 bg-white">
    <div class="max-w-7xl mx-auto px-8">
      <div class="text-center mb-20 reveal">
        <span
          class="text-gold font-bold tracking-[0.5em] uppercase text-[10px] mb-4 block"
          data-key="gal_title">Immersion Visuelle</span>
        <h2 class="text-5xl md:text-7xl italic text-[#2D241E]">La Galerie</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 h-auto md:h-[900px]">
        <div
          class="gallery-container md:col-span-2 md:row-span-2 reveal"
          onclick="
              openLightbox(
                'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&q=80',
              )
            ">
          <img
            src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&q=80"
            class="gallery-img"
            alt="Ambiance Spa" />
        </div>
        <div
          class="gallery-container reveal"
          onclick="
              openLightbox(
                'https://images.unsplash.com/photo-1531853121101-da94cda9405d?auto=format&fit=crop&q=80',
              )
            ">
          <img
            src="https://images.unsplash.com/photo-1531853121101-da94cda9405d?auto=format&fit=crop&q=80"
            class="gallery-img"
            alt="Soin Visage" />
        </div>
        <div
          class="gallery-container reveal"
          onclick="
              openLightbox(
                'https://images.unsplash.com/photo-1552693673-1bf958298935?auto=format&fit=crop&q=80',
              )
            ">
          <img
            src="https://images.unsplash.com/photo-1552693673-1bf958298935?auto=format&fit=crop&q=80"
            class="gallery-img"
            alt="Massage Room" />
        </div>
      </div>
    </div>
  </section>

  <section id="contact" class="py-24 px-8 bg-[#FDF8F5]">
    <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
      <div class="reveal">
        <h2 class="text-5xl italic mb-10 text-[#2D241E]">
          Contactez-nous
        </h2>
        <div class="space-y-6 text-gray-500 text-lg">
          <p>📍 <?php echo $siteSettings['address_' . $lang]; ?></p>

          <p>
            📞 <?php echo $siteSettings['phone_fixed']; ?>
            | 📱 <?php echo $siteSettings['phone_mobile']; ?>
          </p>

          <p>✉️ <?php echo $siteSettings['email']; ?></p>
        </div>
      </div>

      <div class="h-[400px] rounded-sm overflow-hidden shadow-xl reveal border border-gold/10">
        <iframe
          src="<?php echo $siteSettings['maps_iframe_url']; ?>"
          width="100%"
          height="100%"
          style="border: 0"
          allowfullscreen=""
          loading="lazy"></iframe>
      </div>
    </div>
  </section>

  <footer
    class="py-12 text-center text-[9px] tracking-[0.5em] uppercase text-gray-400 bg-[#FDF8F5]">
    <p>© 2026 Duo des Reines • Rabat • Designed by Mouad Guarraz</p>
  </footer>

  <script>
    gsap.registerPlugin(ScrollTrigger);

    // Menu Toggle
    function toggleMenu() {
      document.getElementById("mobile-menu").classList.toggle("active");
    }

    // Langue
    let currentLang = "fr";
    async function loadLanguage(lang) {
      try {
        const response = await fetch("lang.json");
        const data = await response.json();
        document.querySelectorAll("[data-key]").forEach((el) => {
          const key = el.getAttribute("data-key");
          if (data[lang] && data[lang][key]) el.innerText = data[lang][key];
        });
        document.getElementById("lang-indicator").innerText =
          lang.toUpperCase();
        document.documentElement.dir = lang === "ar" ? "rtl" : "ltr";
        currentLang = lang;
      } catch (e) {
        console.error("Erreur traduction:", e);
      }
    }

    function toggleLang() {
      loadLanguage(currentLang === "fr" ? "ar" : "fr");
    }

    // GSAP Animations
    window.addEventListener("load", () => {
      // Horizontal Scroll Services
      let sections = gsap.utils.toArray(".service-panel");
      let scrollTween = gsap.to(sections, {
        xPercent: -100 * (sections.length - 1),
        ease: "none",
        scrollTrigger: {
          trigger: "#categories",
          pin: true,
          scrub: 1,
          end: () =>
            "+=" + document.querySelector("#horizontal-scroll").offsetWidth,
        },
      });

      // Progress Line
      gsap.to("#progress-line", {
        width: "100%",
        ease: "none",
        scrollTrigger: {
          trigger: "#categories",
          start: "top top",
          scrub: true,
          end: () =>
            "+=" + document.querySelector("#horizontal-scroll").offsetWidth,
        },
      });

      // Parallax cards animation
      sections.forEach((panel, i) => {
        if (i === 0) return;
        const wrap = panel.querySelector(".card-content-wrap");
        if (wrap) {
          gsap.from(wrap, {
            x: 100,
            opacity: 0,
            duration: 1,
            scrollTrigger: {
              trigger: panel,
              containerAnimation: scrollTween,
              start: "left 70%",
              toggleActions: "play none none reverse",
            },
          });
        }
      });

      gsap.to("#hero-bg", {
        scale: 1,
        duration: 2.5,
        ease: "power2.out"
      });
      gsap.utils.toArray(".reveal").forEach((elem) => {
        gsap.to(elem, {
          scrollTrigger: {
            trigger: elem,
            start: "top 90%"
          },
          opacity: 1,
          y: 0,
          duration: 1.2,
          ease: "power2.out",
        });
      });
    });

    // Navbar Scroll Effect
    window.addEventListener("scroll", () => {
      const nav = document.getElementById("navbar");
      if (window.scrollY > 150) {
        nav.classList.add("nav-scrolled");
        document.body.classList.add("scrolled");
      } else {
        nav.classList.remove("nav-scrolled");
        document.body.classList.remove("scrolled");
      }
    });

    // Logique du Zoom (Lightbox)
    function openLightbox(src) {
      const lb = document.getElementById("lightbox");
      document.getElementById("lightbox-img").src = src;
      lb.style.display = "flex";
      gsap.to(lb, {
        opacity: 1,
        duration: 0.5,
        onComplete: () => lb.classList.add("active"),
      });
    }

    function closeLightbox() {
      const lb = document.getElementById("lightbox");
      lb.classList.remove("active");
      gsap.to(lb, {
        opacity: 0,
        duration: 0.3,
        onComplete: () => {
          lb.style.display = "none";
        },
      });
    }

    // Animation de révélation GSAP (à placer dans window.addEventListener('load'))
    gsap.utils.toArray(".gallery-container").forEach((img) => {
      gsap.from(img, {
        scrollTrigger: {
          trigger: img,
          start: "top 90%",
        },
        opacity: 0,
        scale: 0.9,
        y: 50,
        duration: 1.5,
        ease: "expo.out",
      });
    });
  </script>
</body>

</html>