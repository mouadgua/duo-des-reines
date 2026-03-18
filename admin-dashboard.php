<?php
session_start();

// --- LECTURE SÉCURISÉE DU FICHIER CONFIG.PHP ---
$env = @parse_ini_file('config.php');
$cloud_name = $env['CLOUDINARY_NAME'] ?? '';
$cloud_preset = $env['CLOUDINARY_PRESET'] ?? '';

require_once 'db.php';

// --- PROTECTION ---
if (!isset($_SESSION['admin_id'])) {
    header('Location: auth.php');
    exit();
}

$message = "";

// --- LOGIQUE DE SUPPRESSION (GET) ---
if (isset($_GET['del_service'])) {
    $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$_GET['del_service']]);
    header("Location: admin-dashboard.php?msg=serv_del"); exit();
}
if (isset($_GET['del_gallery'])) {
    $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$_GET['del_gallery']]);
    header("Location: admin-dashboard.php?msg=gal_del"); exit();
}
if (isset($_GET['del_cat'])) {
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$_GET['del_cat']]);
    header("Location: admin-dashboard.php?msg=cat_del"); exit();
}
if (isset($_GET['del_offer'])) {
    $pdo->prepare("DELETE FROM special_offers WHERE id = ?")->execute([$_GET['del_offer']]);
    header("Location: admin-dashboard.php?msg=off_del"); exit();
}
if (isset($_GET['del_pack'])) {
    $pdo->prepare("DELETE FROM packs WHERE id = ?")->execute([$_GET['del_pack']]);
    header("Location: admin-dashboard.php?msg=pack_del"); exit();
}

// --- LOGIQUE DE TRAITEMENT POST (AVEC REDIRECTION ANTI-DUPLICATION) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Promo Bar
    if (isset($_POST['update_promo'])) {
        $is_active = isset($_POST['promo_active']) ? 1 : 0;
        $pdo->prepare("UPDATE promotion_bar SET text_fr = ?, text_ar = ?, is_active = ? WHERE id = 1")
            ->execute([$_POST['promo_fr'], $_POST['promo_ar'], $is_active]);
        header("Location: admin-dashboard.php?msg=promo_ok"); exit();
    }

    // 2. Update About
    if (isset($_POST['update_about'])) {
        $stmt = $pdo->prepare("UPDATE about_section SET title_fr = ?, title_ar = ?, desc_fr = ?, desc_ar = ?, image_path = ? WHERE id = 1");
        $stmt->execute([$_POST['title_fr'], $_POST['title_ar'], $_POST['desc_fr'], $_POST['desc_ar'] ?? '', $_POST['about_image_url']]);
        header("Location: admin-dashboard.php?msg=about_ok"); exit();
    }

    // 3. Add Category
    if (isset($_POST['add_category']) && !empty($_POST['cat_fr'])) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['cat_fr'])));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        if($stmt->fetchColumn() > 0) $slug .= '-' . time();

        $pdo->prepare("INSERT INTO categories (name_fr, name_ar, slug) VALUES (?, ?, ?)")
            ->execute([$_POST['cat_fr'], $_POST['cat_ar'] ?? '', $slug]);
        header("Location: admin-dashboard.php?msg=cat_ok"); exit();
    }

    // 4. Add Service
    if (isset($_POST['add_service']) && !empty($_POST['s_title_fr'])) {
        $price = !empty($_POST['price']) ? $_POST['price'] : null;
        $discount = !empty($_POST['discount']) ? $_POST['discount'] : null;
        $img = !empty($_POST['s_img_url']) ? $_POST['s_img_url'] : null;
        
        $pdo->prepare("INSERT INTO services (category_id, title_fr, title_ar, desc_fr, desc_ar, price, discount_price, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$_POST['cat_id'], $_POST['s_title_fr'], $_POST['s_title_ar'] ?? '', $_POST['s_desc_fr'] ?? '', $_POST['s_desc_ar'] ?? '', $price, $discount, $img]);
        header("Location: admin-dashboard.php?msg=serv_ok"); exit();
    }

    // 5. Edit Service
    if (isset($_POST['edit_service'])) {
        $price = !empty($_POST['e_price']) ? $_POST['e_price'] : null;
        $discount = !empty($_POST['e_discount']) ? $_POST['e_discount'] : null;
        $pdo->prepare("UPDATE services SET title_fr = ?, title_ar = ?, category_id = ?, price = ?, discount_price = ? WHERE id = ?")
            ->execute([$_POST['e_title_fr'], $_POST['e_title_ar'], $_POST['e_cat_id'], $price, $discount, $_POST['service_id']]);
        header("Location: admin-dashboard.php?msg=serv_edit_ok"); exit();
    }

    // 6. Add Special Offer
    if (isset($_POST['add_offer'])) {
        $title_fr = !empty($_POST['off_title_fr']) ? $_POST['off_title_fr'] : null;
        $title_ar = !empty($_POST['off_title_ar']) ? $_POST['off_title_ar'] : null;
        $old_price = !empty($_POST['off_old_price']) ? $_POST['off_old_price'] : null;
        $new_price = !empty($_POST['off_new_price']) ? $_POST['off_new_price'] : null;
        $img_url = !empty($_POST['off_img_url']) ? $_POST['off_img_url'] : null;
        $expiry = !empty($_POST['off_expiry']) ? $_POST['off_expiry'] : null;

        $pdo->prepare("INSERT INTO special_offers (title_fr, title_ar, old_price, new_price, image_path, expiry_date, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)")
            ->execute([$title_fr, $title_ar, $old_price, $new_price, $img_url, $expiry]);
        header("Location: admin-dashboard.php?msg=off_ok"); exit();
    }

    // 7. Add Pack
    if (isset($_POST['add_pack'])) {
        $pdo->prepare("INSERT INTO packs (name_fr, name_ar, badge_fr, badge_ar, items_fr, items_ar, price, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)")
            ->execute([$_POST['p_name_fr'], $_POST['p_name_ar'], $_POST['p_badge_fr'], $_POST['p_badge_ar'], $_POST['p_items_fr'], $_POST['p_items_ar'], $_POST['p_price']]);
        header("Location: admin-dashboard.php?msg=pack_ok"); exit();
    }

    // 8. Add Gallery
    if (isset($_POST['add_gallery'])) {
        $pdo->prepare("INSERT INTO gallery (image_path, alt_fr, alt_ar) VALUES (?, ?, ?)")
            ->execute([$_POST['gal_url'], $_POST['gal_alt_fr'] ?? 'Spa', 'سبا']);
        header("Location: admin-dashboard.php?msg=gal_ok"); exit();
    }

    // 9. MISE À JOUR DU CONTACT & LOCALISATION
    if (isset($_POST['update_contact'])) {
        $stmt = $pdo->prepare("UPDATE site_settings SET address_fr = ?, address_ar = ?, phone_fixed = ?, phone_mobile = ?, email = ?, maps_iframe_url = ? WHERE id = 1");
        $stmt->execute([$_POST['address_fr'], $_POST['address_ar'], $_POST['phone_fixed'], $_POST['phone_mobile'], $_POST['email'], $_POST['maps_iframe_url']]);
        header("Location: admin-dashboard.php?msg=contact_ok"); exit();
    }
}

// --- MESSAGES DE NOTIFICATION GET ---
if(isset($_GET['msg'])) {
    $msgs = [
        'serv_del' => "Le soin a été supprimé.",
        'gal_del'  => "La photo a été retirée.",
        'cat_del'  => "Catégorie supprimée.",
        'off_del'  => "L'offre a été retirée.",
        'pack_del' => "Le pack a été supprimé.",
        'promo_ok' => "Barre promotionnelle mise à jour !",
        'about_ok' => "L'histoire a été mise à jour !",
        'cat_ok'   => "Nouvelle catégorie ajoutée !",
        'serv_ok'  => "Le nouveau soin a été publié !",
        'serv_edit_ok' => "Le soin a été modifié !",
        'off_ok'   => "Nouvelle offre spéciale publiée !",
        'pack_ok'  => "Le nouveau pack a été créé !",
        'gal_ok'   => "Photo(s) ajoutée(s) à la photothèque !",
        'contact_ok' => "Coordonnées et localisation mises à jour avec succès !"
    ];
    $message = $msgs[$_GET['msg']] ?? '';
}

// --- RÉCUPÉRATION DES DONNÉES ---
$promo = $pdo->query("SELECT * FROM promotion_bar WHERE id = 1")->fetch() ?: ['text_fr'=>'', 'text_ar'=>'', 'is_active'=>0];
$about = $pdo->query("SELECT * FROM about_section WHERE id = 1")->fetch();
$siteSettings = $pdo->query("SELECT * FROM site_settings LIMIT 1")->fetch() ?: ['address_fr'=>'', 'address_ar'=>'', 'phone_fixed'=>'', 'phone_mobile'=>'', 'email'=>'', 'maps_iframe_url'=>''];
$stats_visits = $pdo->query("SELECT visit_count FROM site_stats WHERE page_name = 'index'")->fetchColumn() ?: 0;
$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
$services = $pdo->query("SELECT s.*, c.name_fr as cat_name FROM services s LEFT JOIN categories c ON s.category_id = c.id ORDER BY s.id DESC")->fetchAll();
$offers = $pdo->query("SELECT * FROM special_offers ORDER BY id DESC")->fetchAll();
$packs = $pdo->query("SELECT * FROM packs ORDER BY id DESC")->fetchAll();
$gallery = $pdo->query("SELECT * FROM gallery ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DDR Admin | Dashboard Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://upload-widget.cloudinary.com/global/all.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7f6; }
        .sidebar { background-color: #1a1614; transition: transform 0.3s ease-in-out; }
        .nav-btn.active { background: rgba(212, 175, 55, 0.1); color: #D4AF37; border-right: 4px solid #D4AF37; opacity: 1; }
        .glass-card { background: white; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        
        .input-premium { @apply w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37] outline-none transition-all; }
        .label-premium { @apply block text-[10px] font-bold uppercase text-slate-500 tracking-widest mb-2; }
        
        .fade-transition { transition: all 0.4s ease; }
        .toast-enter { transform: translateY(0); opacity: 1; }
        .toast-exit { transform: translateY(-20px); opacity: 0; }
        .zoom-cursor { cursor: zoom-in; }
        
        @media (max-width: 1024px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
        }
    </style>
</head>
<body class="flex min-h-screen">

    <div id="overlay" onclick="toggleMenu()" class="fixed inset-0 bg-black/40 z-[60] hidden transition-opacity lg:hidden"></div>

    <div id="admin-lightbox" class="fixed inset-0 bg-black/90 z-[200] hidden items-center justify-center opacity-0 fade-transition" onclick="closeAdminLightbox()">
        <button class="absolute top-8 right-8 text-white text-4xl hover:text-[#D4AF37] transition">×</button>
        <img id="admin-lightbox-img" src="" class="max-w-[90%] max-h-[90vh] object-contain rounded-lg shadow-2xl transform scale-95 fade-transition">
    </div>

    <div id="delete-modal" class="fixed inset-0 bg-black/60 z-[100] hidden items-center justify-center opacity-0 fade-transition">
        <div id="delete-modal-box" class="bg-white p-8 rounded-[2rem] shadow-2xl max-w-sm w-full mx-4 text-center transform scale-95 fade-transition">
            <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="text-red-500 text-2xl">⚠️</span>
            </div>
            <h3 class="text-xl font-bold mb-2">Êtes-vous sûr ?</h3>
            <p id="delete-modal-text" class="text-sm text-slate-500 mb-8">Cette action est irréversible.</p>
            <div class="flex gap-4 justify-center">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl text-[10px] uppercase tracking-widest hover:bg-slate-200">Annuler</button>
                <a id="confirm-delete-btn" href="#" class="flex-1 py-4 bg-red-500 text-white font-bold rounded-2xl text-[10px] uppercase tracking-widest hover:bg-red-600 shadow-lg block">Supprimer</a>
            </div>
        </div>
    </div>

    <div id="edit-service-modal" class="fixed inset-0 bg-black/60 z-[100] hidden items-center justify-center opacity-0 fade-transition">
        <div id="edit-modal-box" class="bg-white p-8 rounded-[2rem] shadow-2xl max-w-lg w-full mx-4 transform scale-95 fade-transition">
            <h3 class="text-xl font-bold mb-6 italic border-b border-slate-100 pb-4">Modifier le Soin</h3>
            <form method="POST" class="space-y-6">
                <input type="hidden" name="service_id" id="e_service_id">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="label-premium">Titre (FR)</label>
                        <input type="text" name="e_title_fr" id="e_title_fr" class="input-premium" required>
                    </div>
                    <div>
                        <label class="label-premium text-right">Titre (AR)</label>
                        <input type="text" name="e_title_ar" id="e_title_ar" class="input-premium text-right">
                    </div>
                </div>
                <div>
                    <label class="label-premium">Catégorie</label>
                    <select name="e_cat_id" id="e_cat_id" class="input-premium" required>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name_fr']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="label-premium">Prix normal (DH)</label>
                        <input type="number" name="e_price" id="e_price" class="input-premium">
                    </div>
                    <div>
                        <label class="label-premium text-[#D4AF37]">Prix réduit (DH)</label>
                        <input type="number" name="e_discount" id="e_discount" class="input-premium border-[#D4AF37]">
                    </div>
                </div>
                <div class="flex gap-4 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()" class="flex-1 py-4 bg-slate-100 text-slate-600 font-bold rounded-xl text-[10px] uppercase tracking-widest">Annuler</button>
                    <button type="submit" name="edit_service" class="flex-1 py-4 bg-[#D4AF37] text-white font-bold rounded-xl text-[10px] uppercase tracking-widest shadow-lg hover:bg-[#B8962D]">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <?php if($message): ?>
        <div id="toast-message" class="fixed top-8 left-1/2 transform -translate-x-1/2 lg:left-auto lg:right-8 lg:translate-x-0 bg-[#D4AF37] text-white px-8 py-4 rounded-2xl shadow-2xl z-[90] fade-transition toast-enter flex items-center gap-3">
            <span class="text-lg">✨</span>
            <p class="text-sm font-bold tracking-wide"><?= $message ?></p>
        </div>
    <?php endif; ?>

    <aside id="sidebar" class="fixed inset-y-0 left-0 w-72 sidebar text-white z-[70] lg:translate-x-0 flex flex-col shadow-2xl">
        <div class="p-8 border-b border-white/5 flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-tighter text-[#D4AF37]">DDR <span class="text-white">CMS</span></h1>
            <button onclick="toggleMenu()" class="lg:hidden text-white text-2xl">✕</button>
        </div>
        <nav class="flex-1 p-6 space-y-2 mt-4 overflow-y-auto">
            <button onclick="showSection('overview')" class="nav-btn w-full flex items-center gap-4 p-4 rounded-xl text-sm opacity-60 hover:opacity-100 transition active" id="btn-overview">📊 Dashboard</button>
            <button onclick="showSection('services')" class="nav-btn w-full flex items-center gap-4 p-4 rounded-xl text-sm opacity-60 hover:opacity-100 transition" id="btn-services">✨ Services & Catalogue</button>
            <button onclick="showSection('about')" class="nav-btn w-full flex items-center gap-4 p-4 rounded-xl text-sm opacity-60 hover:opacity-100 transition" id="btn-about">📝 Section About</button>
            <button onclick="showSection('gallery')" class="nav-btn w-full flex items-center gap-4 p-4 rounded-xl text-sm opacity-60 hover:opacity-100 transition" id="btn-gallery">📸 Galerie</button>
            
            <button onclick="showSection('contact')" class="nav-btn w-full flex items-center gap-4 p-4 rounded-xl text-sm opacity-60 hover:opacity-100 transition text-blue-300" id="btn-contact">📞 Contact & Map</button>
            
            <hr class="border-white/5 my-6">
            <a href="logout.php" class="flex items-center gap-4 p-4 text-xs text-red-400 opacity-60 hover:opacity-100 transition">Déconnexion</a>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col min-w-0 lg:ml-72 h-screen overflow-y-auto">
        <header class="p-6 lg:p-10 flex justify-between items-center bg-white/80 backdrop-blur-md sticky top-0 z-50">
            <div class="flex items-center gap-4">
                <button onclick="toggleMenu()" class="lg:hidden p-3 glass-card">☰</button>
                <h2 id="page-title" class="text-2xl font-bold italic text-slate-800 uppercase tracking-tighter">Vue d'ensemble</h2>
            </div>
            <div class="flex items-center gap-3 glass-card px-4 py-2 border border-slate-200">
                <span class="text-[10px] font-bold uppercase text-slate-400 hidden sm:inline">Admin Mouad</span>
                <div class="w-8 h-8 rounded-full bg-[#D4AF37] flex items-center justify-center text-white font-bold text-xs shadow-lg">M</div>
            </div>
        </header>

        <div class="p-6 lg:p-10 space-y-10">

            <section id="section-overview" class="tab-content">
                <div class="grid lg:grid-cols-2 gap-10">
                    <div class="glass-card p-10 border-l-8 border-[#D4AF37]">
                        <p class="text-[10px] font-bold uppercase text-slate-400 tracking-widest mb-2">Visites Totales</p>
                        <h3 class="text-6xl font-bold text-slate-800"><?= number_format($stats_visits) ?></h3>
                    </div>

                    <div class="glass-card p-8">
                        <h3 class="text-lg font-bold mb-6 italic border-b border-slate-100 pb-4">Bannière Promotionnelle</h3>
                        <form method="POST" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="label-premium">Texte de l'offre (FR)</label>
                                    <input type="text" name="promo_fr" value="<?= htmlspecialchars($promo['text_fr']) ?>" class="input-premium">
                                </div>
                                <div>
                                    <label class="label-premium text-right">نص العرض (AR)</label>
                                    <input type="text" name="promo_ar" value="<?= htmlspecialchars($promo['text_ar']) ?>" class="input-premium text-right">
                                </div>
                            </div>
                            <label class="flex items-center gap-3 cursor-pointer mt-4 bg-slate-50 p-4 rounded-xl border border-slate-200 w-max">
                                <input type="checkbox" name="promo_active" class="w-5 h-5 accent-[#D4AF37]" <?= $promo['is_active'] ? 'checked' : '' ?>>
                                <span class="text-sm font-bold text-slate-600">Activer la bannière sur le site</span>
                            </label>
                            <button type="submit" name="update_promo" class="bg-[#2D241E] text-white px-8 py-3 rounded-xl font-bold text-[10px] uppercase tracking-widest shadow-xl hover:bg-black mt-4">Enregistrer</button>
                        </form>
                    </div>
                </div>
            </section>

            <section id="section-services" class="tab-content hidden space-y-12">
                
                <div class="glass-card p-8 bg-slate-50 border-l-4 border-slate-800">
                    <h3 class="font-bold mb-6 italic text-lg uppercase tracking-tighter">1. Gestion des Catégories</h3>
                    <div class="grid lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-1">
                            <form method="POST" class="space-y-4 bg-white p-6 rounded-2xl border border-slate-200">
                                <div>
                                    <label class="label-premium">Nom Catégorie (FR)</label>
                                    <input type="text" name="cat_fr" placeholder="Ex: Massages" class="input-premium" required>
                                </div>
                                <div>
                                    <label class="label-premium text-right">اسم الفئة (AR)</label>
                                    <input type="text" name="cat_ar" placeholder="Ex: مساج" class="input-premium text-right">
                                </div>
                                <button type="submit" name="add_category" class="w-full bg-slate-800 text-white py-4 rounded-xl font-bold text-[10px] uppercase">Créer Catégorie</button>
                            </form>
                        </div>
                        <div class="lg:col-span-2 grid grid-cols-2 md:grid-cols-3 gap-4 h-fit">
                            <?php foreach($categories as $c): ?>
                                <div class="flex justify-between items-center bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-sm font-bold">
                                    <span><?= htmlspecialchars($c['name_fr']) ?></span>
                                    <button onclick="showDeleteModal('?del_cat=<?= $c['id'] ?>', 'category')" class="text-red-400 bg-red-50 w-6 h-6 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition">✕</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-8 border-l-4 border-[#D4AF37]">
                    <h3 class="font-bold mb-6 italic text-lg uppercase tracking-tighter">2. Catalogue des Soins</h3>
                    <form method="POST" class="space-y-6 bg-slate-50 p-6 rounded-2xl border border-slate-200 mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="label-premium">Catégorie</label>
                                <select name="cat_id" class="input-premium" required>
                                    <option value="">Sélectionner</option>
                                    <?php foreach($categories as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name_fr']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="label-premium">Titre du soin (FR)</label>
                                <input type="text" name="s_title_fr" class="input-premium" required>
                            </div>
                            <div>
                                <label class="label-premium text-right">اسم العناية (AR)</label>
                                <input type="text" name="s_title_ar" class="input-premium text-right">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="label-premium">Prix normal (DH)</label>
                                <input type="number" name="price" class="input-premium">
                            </div>
                            <div>
                                <label class="label-premium text-[#D4AF37]">Prix réduit (DH)</label>
                                <input type="number" name="discount" class="input-premium border-[#D4AF37]">
                            </div>
                            <div>
                                <label class="label-premium">Image Illustrative</label>
                                <button type="button" id="btn-service-img" onclick="uploadImg('service_img_url', 'btn-service-img')" class="w-full py-3 border-2 border-dashed border-slate-300 rounded-xl text-xs text-slate-500 font-bold hover:border-[#D4AF37] bg-white transition">📷 Ajouter Image</button>
                                <input type="hidden" name="s_img_url" id="service_img_url">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="label-premium">Description (FR)</label>
                                <textarea name="s_desc_fr" rows="3" class="input-premium"></textarea>
                            </div>
                            <div>
                                <label class="label-premium text-right">الوصف (AR)</label>
                                <textarea name="s_desc_ar" rows="3" class="input-premium text-right"></textarea>
                            </div>
                        </div>

                        <button type="submit" name="add_service" class="bg-[#D4AF37] text-white px-10 py-4 rounded-xl font-bold text-[10px] uppercase tracking-widest shadow-lg hover:bg-[#B8962D] w-full md:w-auto">Publier le Soin</button>
                    </form>

                    <table class="w-full text-left">
                        <thead class="text-[10px] uppercase text-slate-400 border-b border-slate-200">
                            <tr><th class="pb-4">Image</th><th class="pb-4">Soin & Catégorie</th><th class="pb-4">Prix</th><th class="pb-4 text-right">Actions</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php foreach($services as $s): ?>
                                <tr>
                                    <td class="py-4">
                                        <?php if($s['image_path']): ?>
                                            <img src="<?= htmlspecialchars($s['image_path']) ?>" onclick="openAdminLightbox(this.src)" class="w-12 h-12 rounded-lg object-cover border border-slate-200 shadow-sm zoom-cursor">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-slate-100 border border-slate-200 rounded-lg flex items-center justify-center"><span class="text-[8px] font-bold text-slate-400 uppercase">Aucune</span></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 font-semibold text-slate-700">
                                        <?= htmlspecialchars($s['title_fr']) ?><br>
                                        <span class="text-[10px] text-slate-400 font-normal uppercase bg-slate-100 px-2 py-0.5 rounded"><?= htmlspecialchars($s['cat_name']) ?></span>
                                    </td>
                                    <td class="py-4 font-bold">
                                        <?php if($s['discount_price']): ?>
                                            <span class="text-[#D4AF37]"><?= htmlspecialchars($s['discount_price']) ?> DH</span><br>
                                            <span class="line-through text-slate-400 text-xs"><?= htmlspecialchars($s['price']) ?> DH</span>
                                        <?php else: ?>
                                            <span class="text-[#D4AF37]"><?= htmlspecialchars($s['price'] ?? '--') ?> DH</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 text-right">
                                        <button type="button" onclick="openEditModal(<?= $s['id'] ?>, '<?= addslashes($s['title_fr']) ?>', '<?= addslashes($s['title_ar'] ?? '') ?>', <?= $s['price'] ?: "''" ?>, <?= $s['discount_price'] ?: "''" ?>, <?= $s['category_id'] ?>)" class="text-blue-500 hover:underline font-bold text-[10px] uppercase mr-4">Éditer</button>
                                        <button type="button" onclick="showDeleteModal('?del_service=<?= $s['id'] ?>', 'service')" class="text-red-400 hover:underline font-bold text-[10px] uppercase">Supprimer</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="glass-card p-8 border-l-4 border-emerald-500">
                    <h3 class="font-bold mb-6 italic text-lg uppercase tracking-tighter">3. Formules & Packs</h3>
                    <form method="POST" class="space-y-6 bg-slate-50 p-6 rounded-2xl border border-slate-200 mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="label-premium">Nom du Pack (FR)</label>
                                <input type="text" name="p_name_fr" class="input-premium" required>
                            </div>
                            <div>
                                <label class="label-premium text-right">اسم الباقة (AR)</label>
                                <input type="text" name="p_name_ar" class="input-premium text-right">
                            </div>
                            <div>
                                <label class="label-premium text-emerald-600">Prix Total (DH)</label>
                                <input type="number" name="p_price" class="input-premium border-emerald-300" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="label-premium">Badge FR (Optionnel, ex: VIP)</label>
                                <input type="text" name="p_badge_fr" class="input-premium">
                            </div>
                            <div>
                                <label class="label-premium text-right">الشارة AR (اختياري)</label>
                                <input type="text" name="p_badge_ar" class="input-premium text-right">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="label-premium">Contenu (Séparez par des virgules)</label>
                                <textarea name="p_items_fr" rows="3" placeholder="Soin visage, Massage relaxant, Thé..." class="input-premium" required></textarea>
                            </div>
                            <div>
                                <label class="label-premium text-right">المحتوى (افصل بفاصلة)</label>
                                <textarea name="p_items_ar" rows="3" class="input-premium text-right"></textarea>
                            </div>
                        </div>

                        <button type="submit" name="add_pack" class="bg-emerald-600 text-white px-10 py-4 rounded-xl font-bold text-[10px] uppercase tracking-widest shadow-lg hover:bg-emerald-700 w-full md:w-auto">Créer le Pack</button>
                    </form>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach($packs as $p): ?>
                            <div class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between shadow-sm relative overflow-hidden">
                                <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>
                                <div>
                                    <div class="flex justify-between items-start mb-4">
                                        <h4 class="font-bold text-lg uppercase"><?= htmlspecialchars($p['name_fr']) ?></h4>
                                        <?php if($p['badge_fr']): ?>
                                            <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-1 rounded uppercase"><?= htmlspecialchars($p['badge_fr']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-3xl font-bold text-emerald-600 mb-4"><?= htmlspecialchars($p['price']) ?> <span class="text-sm text-slate-400">DH</span></p>
                                    <ul class="text-sm text-slate-600 space-y-2 mb-6">
                                        <?php foreach(explode(',', $p['items_fr']) as $item): ?>
                                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> <?= htmlspecialchars(trim($item)) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <button onclick="showDeleteModal('?del_pack=<?= $p['id'] ?>', 'pack')" class="text-center block w-full py-3 bg-red-50 text-red-500 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-red-500 hover:text-white transition mt-auto border border-red-100">Supprimer</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="glass-card p-8 border-l-4 border-amber-500">
                    <h3 class="font-bold mb-6 italic text-lg uppercase tracking-tighter">4. Offres Spéciales (Flash)</h3>
                    
                    <div class="bg-amber-50 text-amber-800 p-4 rounded-xl text-xs mb-6 border border-amber-200">
                        💡 <b>Astuce :</b> Vous pouvez créer une offre 100% visuelle (sans texte ni prix). Remplissez simplement l'image.
                    </div>

                    <form method="POST" class="space-y-6 bg-slate-50 p-6 rounded-2xl border border-slate-200 mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="label-premium">Titre (Optionnel)</label>
                                <input type="text" name="off_title_fr" class="input-premium">
                            </div>
                            <div>
                                <label class="label-premium text-right">عنوان العرض</label>
                                <input type="text" name="off_title_ar" class="input-premium text-right">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="label-premium">Ancien Prix</label>
                                <input type="number" name="off_old_price" class="input-premium">
                            </div>
                            <div>
                                <label class="label-premium text-amber-600">Nouveau Prix</label>
                                <input type="number" name="off_new_price" class="input-premium border-amber-300">
                            </div>
                            <div>
                                <label class="label-premium">Date d'expiration</label>
                                <input type="date" name="off_expiry" class="input-premium text-slate-500">
                            </div>
                            <div>
                                <label class="label-premium">Image de l'offre</label>
                                <button type="button" id="btn-offer-img" onclick="uploadImg('offer_img_url', 'btn-offer-img')" class="w-full py-3 border-2 border-dashed border-slate-300 rounded-xl text-xs text-slate-500 font-bold hover:border-amber-500 bg-white transition">🖼️ Choisir Image</button>
                                <input type="hidden" name="off_img_url" id="offer_img_url">
                            </div>
                        </div>
                        <button type="submit" name="add_offer" class="bg-amber-500 text-white px-10 py-4 rounded-xl font-bold text-[10px] uppercase tracking-widest shadow-lg hover:bg-amber-600 w-full md:w-auto">Lancer l'offre</button>
                    </form>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php foreach($offers as $o): ?>
                            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden flex flex-col shadow-sm">
                                
                                <?php if($o['image_path']): ?>
                                    <div class="h-40 bg-slate-100 relative">
                                        <img src="<?= htmlspecialchars($o['image_path']) ?>" onclick="openAdminLightbox(this.src)" class="w-full h-full object-cover zoom-cursor">
                                        <?php if($o['expiry_date']): ?>
                                            <div class="absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-1 rounded-full text-[10px] font-bold shadow-sm">
                                                Expire: <?= date('d/m', strtotime($o['expiry_date'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="p-4 flex-1 flex flex-col justify-between">
                                    <div>
                                        <?php if($o['title_fr']): ?>
                                            <h4 class="font-bold text-sm mb-1"><?= htmlspecialchars($o['title_fr']) ?></h4>
                                        <?php else: ?>
                                            <h4 class="font-bold italic text-sm text-slate-400 mb-1">Offre 100% Visuelle</h4>
                                        <?php endif; ?>

                                        <?php if($o['new_price']): ?>
                                            <p class="text-xs font-bold mt-2">
                                                <span class="text-amber-500 text-lg"><?= htmlspecialchars($o['new_price']) ?> DH</span> 
                                                <?php if($o['old_price']): ?><span class="line-through text-slate-400 ml-1"><?= htmlspecialchars($o['old_price']) ?> DH</span><?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" onclick="showDeleteModal('?del_offer=<?= $o['id'] ?>', 'offer')" class="mt-4 text-center block w-full py-2 bg-red-50 text-red-500 rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-red-500 hover:text-white transition border border-red-100">Retirer</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </section>

            <section id="section-about" class="tab-content hidden">
                <div class="glass-card p-8 lg:p-12 max-w-5xl mx-auto">
                    <h3 class="text-2xl font-bold mb-10 italic border-b border-slate-200 pb-4">Modifier Héritage & Passion</h3>
                    <form method="POST" class="space-y-8 bg-slate-50 p-8 rounded-3xl border border-slate-200">
                        <div class="grid lg:grid-cols-2 gap-8">
                            <div>
                                <label class="label-premium">Titre (Français)</label>
                                <input type="text" name="title_fr" value="<?= htmlspecialchars($about['title_fr'] ?? '') ?>" class="input-premium">
                            </div>
                            <div>
                                <label class="label-premium text-right">العنوان (العربية)</label>
                                <input type="text" name="title_ar" value="<?= htmlspecialchars($about['title_ar'] ?? '') ?>" class="input-premium text-right">
                            </div>
                        </div>
                        
                        <div class="grid lg:grid-cols-2 gap-8">
                            <div>
                                <label class="label-premium">Notre Histoire (FR)</label>
                                <textarea name="desc_fr" rows="6" class="input-premium leading-relaxed"><?= htmlspecialchars($about['desc_fr'] ?? '') ?></textarea>
                            </div>
                            <div>
                                <label class="label-premium text-right">قصتنا (AR)</label>
                                <textarea name="desc_ar" rows="6" class="input-premium text-right leading-relaxed"><?= htmlspecialchars($about['desc_ar'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-6 pt-6 border-t border-slate-200">
                            <button type="submit" name="update_about" class="bg-[#2D241E] text-white px-12 py-5 rounded-2xl font-bold text-[11px] uppercase tracking-[0.2em] shadow-xl hover:bg-black transition-all">Enregistrer</button>
                            <button type="button" id="btn-about-img" onclick="uploadImg('about_img_url', 'btn-about-img')" class="bg-white border border-slate-300 text-slate-600 px-12 py-5 rounded-2xl text-[11px] font-bold uppercase tracking-widest hover:bg-slate-100 transition-colors">📷 Changer l'image illustrative</button>
                            <input type="hidden" name="about_image_url" id="about_img_url" value="<?= htmlspecialchars($about['image_path'] ?? '') ?>">
                        </div>
                    </form>
                </div>
            </section>

            <section id="section-contact" class="tab-content hidden">
                <div class="glass-card p-8 lg:p-12 max-w-5xl mx-auto">
                    <h3 class="text-2xl font-bold mb-10 italic border-b border-slate-200 pb-4">Coordonnées & Localisation</h3>
                    <form method="POST" class="space-y-8 bg-slate-50 p-8 rounded-3xl border border-slate-200">
                        <div class="grid lg:grid-cols-2 gap-8">
                            <div>
                                <label class="label-premium">Adresse complète (Français)</label>
                                <input type="text" name="address_fr" value="<?= htmlspecialchars($siteSettings['address_fr'] ?? '') ?>" class="input-premium">
                            </div>
                            <div>
                                <label class="label-premium text-right">العنوان (العربية)</label>
                                <input type="text" name="address_ar" value="<?= htmlspecialchars($siteSettings['address_ar'] ?? '') ?>" class="input-premium text-right">
                            </div>
                        </div>
                        
                        <div class="grid lg:grid-cols-3 gap-8">
                            <div>
                                <label class="label-premium">Tél Mobile (WhatsApp)</label>
                                <input type="text" name="phone_mobile" value="<?= htmlspecialchars($siteSettings['phone_mobile'] ?? '') ?>" class="input-premium" placeholder="ex: 212600000000">
                            </div>
                            <div>
                                <label class="label-premium">Téléphone Fixe</label>
                                <input type="text" name="phone_fixed" value="<?= htmlspecialchars($siteSettings['phone_fixed'] ?? '') ?>" class="input-premium">
                            </div>
                            <div>
                                <label class="label-premium">Adresse Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($siteSettings['email'] ?? '') ?>" class="input-premium">
                            </div>
                        </div>

                        <div>
                            <label class="label-premium">Lien d'intégration Google Maps (Iframe SRC)</label>
                            <input type="text" name="maps_iframe_url" value="<?= htmlspecialchars($siteSettings['maps_iframe_url'] ?? '') ?>" class="input-premium" placeholder="https://www.google.com/maps/embed?pb=...">
                            <p class="text-xs text-slate-400 mt-2">Allez sur Google Maps > Partager > Intégrer une carte. Copiez uniquement le lien à l'intérieur de <code class="bg-white border px-1 rounded text-red-500">src="..."</code>.</p>
                        </div>

                        <div class="flex pt-6 border-t border-slate-200">
                            <button type="submit" name="update_contact" class="bg-[#2D241E] text-white px-12 py-5 rounded-2xl font-bold text-[11px] uppercase tracking-[0.2em] shadow-xl hover:bg-black transition-all w-full md:w-auto">Mettre à jour les contacts</button>
                        </div>
                    </form>
                </div>
            </section>

            <section id="section-gallery" class="tab-content hidden">
                <div class="glass-card p-8 lg:p-12 max-w-6xl mx-auto">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 border-b border-slate-200 pb-6 gap-4">
                        <h3 class="text-2xl font-bold italic">Photothèque</h3>
                        <button type="button" onclick="openGalleryWidget()" class="bg-[#2D241E] text-white px-8 py-4 rounded-2xl font-bold text-[11px] uppercase tracking-widest shadow-xl hover:bg-black transition-all">📸 Ajouter des photos</button>
                    </div>
                    
                    <form id="gallery-form" method="POST" class="hidden">
                        <input type="hidden" name="gal_url" id="gal_url">
                        <input type="hidden" name="gal_alt_fr" id="gal_alt_fr">
                        <button type="submit" name="add_gallery" id="submit-gallery"></button>
                    </form>

                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                        <?php foreach($gallery as $img): ?>
                            <div class="relative group aspect-[4/5] rounded-[2rem] overflow-hidden shadow-sm border border-slate-200 bg-slate-100">
                                <img src="<?= htmlspecialchars($img['image_path']) ?>" onclick="openAdminLightbox(this.src)" class="w-full h-full object-cover transition duration-500 group-hover:scale-110 zoom-cursor">
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                    <button type="button" onclick="showDeleteModal('?del_gallery=<?= $img['id'] ?>', 'gallery')" class="bg-white text-red-500 px-4 py-2 rounded-full shadow-xl font-bold text-[10px] uppercase tracking-widest hover:scale-105 transition-transform">Supprimer</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <script>
        const CLOUD_NAME = "<?= $cloud_name ?>";
        const PRESET = "<?= $cloud_preset ?>";

        function toggleMenu() {
            const menu = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            menu.classList.toggle('open');
            overlay.classList.toggle('hidden');
        }

        function showSection(id) {
            document.querySelectorAll('.tab-content').forEach(s => s.classList.add('hidden'));
            document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('section-' + id).classList.remove('hidden');
            document.getElementById('btn-' + id).classList.add('active');
            
            const titles = { 
                'overview': 'Vue d\'ensemble', 
                'about': 'Notre Histoire', 
                'services': 'Services & Catalogue', 
                'gallery': 'Photothèque',
                'contact': 'Contact & Localisation'
            };
            document.getElementById('page-title').innerText = titles[id];

            if (window.innerWidth < 1024) {
                document.getElementById('sidebar').classList.remove('open');
                document.getElementById('overlay').classList.add('hidden');
            }
        }

        // LIGHTBOX ADMIN (ZOOM)
        function openAdminLightbox(src) {
            const lb = document.getElementById('admin-lightbox');
            const img = document.getElementById('admin-lightbox-img');
            img.src = src;
            lb.classList.remove('hidden');
            lb.classList.add('flex');
            setTimeout(() => {
                lb.classList.remove('opacity-0');
                img.classList.remove('scale-95');
            }, 10);
        }

        function closeAdminLightbox() {
            const lb = document.getElementById('admin-lightbox');
            const img = document.getElementById('admin-lightbox-img');
            lb.classList.add('opacity-0');
            img.classList.add('scale-95');
            setTimeout(() => {
                lb.classList.add('hidden');
                lb.classList.remove('flex');
            }, 400);
        }

        // EDIT SERVICE MODAL
        function openEditModal(id, title_fr, title_ar, price, discount, cat_id) {
            document.getElementById('e_service_id').value = id;
            document.getElementById('e_title_fr').value = title_fr;
            document.getElementById('e_title_ar').value = title_ar;
            document.getElementById('e_price').value = price;
            document.getElementById('e_discount').value = discount;
            document.getElementById('e_cat_id').value = cat_id;

            const modal = document.getElementById('edit-service-modal');
            const box = document.getElementById('edit-modal-box');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => { modal.classList.remove('opacity-0'); box.classList.remove('scale-95'); }, 10);
        }

        function closeEditModal() {
            const modal = document.getElementById('edit-service-modal');
            const box = document.getElementById('edit-modal-box');
            modal.classList.add('opacity-0');
            box.classList.add('scale-95');
            setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 400); 
        }

        // DELETE MODAL
        function showDeleteModal(url, type) {
            const modal = document.getElementById('delete-modal');
            const box = document.getElementById('delete-modal-box');
            const btn = document.getElementById('confirm-delete-btn');
            const text = document.getElementById('delete-modal-text');

            if(type === 'service') text.innerText = "Retirer ce soin du catalogue ?";
            if(type === 'category') text.innerText = "Supprimer cette catégorie définitivement ?";
            if(type === 'offer') text.innerText = "Retirer cette offre spéciale ?";
            if(type === 'pack') text.innerText = "Supprimer ce pack ?";
            if(type === 'gallery') text.innerText = "Supprimer cette photo ?";

            btn.href = url;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => { modal.classList.remove('opacity-0'); box.classList.remove('scale-95'); }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('delete-modal');
            const box = document.getElementById('delete-modal-box');
            modal.classList.add('opacity-0');
            box.classList.add('scale-95');
            setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 400); 
        }

        // TICKET NOTIFICATION
        window.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('toast-message');
            if (toast) {
                setTimeout(() => {
                    toast.classList.replace('toast-enter', 'toast-exit');
                    setTimeout(() => toast.remove(), 400); 
                }, 4000);

                const url = new URL(window.location);
                if(url.searchParams.has('msg')) {
                    url.searchParams.delete('msg');
                    window.history.replaceState({}, document.title, url);
                }
            }
        });

        // WIDGETS CLOUDINARY
        function uploadImg(inputId, btnId) {
            cloudinary.openUploadWidget({
                cloudName: CLOUD_NAME, uploadPreset: PRESET, sources: ['local', 'url'], multiple: false
            }, (error, result) => {
                if (!error && result.event === "success") {
                    document.getElementById(inputId).value = result.info.secure_url;
                    const btn = document.getElementById(btnId);
                    btn.innerHTML = "✅ Image Ajoutée";
                    btn.classList.add('bg-green-50', 'text-green-600', 'border-green-300');
                    btn.classList.remove('text-slate-500', 'border-slate-300');
                }
            });
        }

        function openGalleryWidget() {
            cloudinary.openUploadWidget({
                cloudName: CLOUD_NAME, uploadPreset: PRESET, sources: ['local', 'url'], multiple: true
            }, (error, result) => {
                if (!error && result.event === "success") {
                    document.getElementById('gal_url').value = result.info.secure_url;
                    document.getElementById('gal_alt_fr').value = result.info.original_filename;
                    document.getElementById('submit-gallery').click();
                }
            });
        }
    </script>
</body>
</html>