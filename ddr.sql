-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 18 mars 2026 à 02:27
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ddr`
--

-- --------------------------------------------------------

--
-- Structure de la table `about_section`
--

DROP TABLE IF EXISTS `about_section`;
CREATE TABLE IF NOT EXISTS `about_section` (
  `id` int NOT NULL DEFAULT '1',
  `title_fr` varchar(255) DEFAULT NULL,
  `title_ar` varchar(255) DEFAULT NULL,
  `desc_fr` text,
  `desc_ar` text,
  `image_path` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `about_section`
--

INSERT INTO `about_section` (`id`, `title_fr`, `title_ar`, `desc_fr`, `desc_ar`, `image_path`, `updated_at`) VALUES
(1, 'Sublimer la femme depuis plus de 6 ans.', 'تجميل المرأة لأكثر من 6 سنوات.', 'Situé à Rabat, Duo des Reines est un sanctuaire où les rituels ancestraux rencontrent l\'expertise moderne.', 'يقع في الرباط، دو دي رين هو ملاذ تلتقي فيه الطقوس القديمة مع الخبرة الحديثة.', 'https://images.unsplash.com/photo-1600334129128-685c5582fd35?auto=format&fit=crop&q=80', '2026-03-17 21:42:06');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name_fr` varchar(100) NOT NULL,
  `name_ar` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `gallery`
--

DROP TABLE IF EXISTS `gallery`;
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` int NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) NOT NULL,
  `alt_fr` varchar(255) DEFAULT NULL,
  `alt_ar` varchar(255) DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `gallery`
--

INSERT INTO `gallery` (`id`, `image_path`, `alt_fr`, `alt_ar`, `display_order`, `created_at`) VALUES
(1, 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80', 'Intérieur luxueux du spa', 'التصميم الداخلي الفاخر للمنتجع', 1, '2026-03-17 21:56:12'),
(2, 'https://images.unsplash.com/photo-1544161515-4af6b1d462c2?q=80', 'Soin aux pierres chaudes', 'علاج بالأحجار الساخنة', 2, '2026-03-17 21:56:12'),
(3, 'https://images.unsplash.com/photo-1596178065887-1198b6148b2b?q=80', 'Espace Hammam Traditionnel', 'منطقة الحمام التقليدي', 3, '2026-03-17 21:56:12'),
(4, 'https://images.unsplash.com/photo-1614806687431-035900cc2f15?q=80', 'Détail décoration orientale', 'تفاصيل الديكور الشرقي', 4, '2026-03-17 21:56:12');

-- --------------------------------------------------------

--
-- Structure de la table `gerante_section`
--

DROP TABLE IF EXISTS `gerante_section`;
CREATE TABLE IF NOT EXISTS `gerante_section` (
  `id` int NOT NULL DEFAULT '1',
  `tag_fr` varchar(50) DEFAULT 'Bienvenue',
  `tag_ar` varchar(50) DEFAULT 'مرحباً',
  `title_fr` varchar(255) DEFAULT NULL,
  `title_ar` varchar(255) DEFAULT NULL,
  `quote_fr` text,
  `quote_ar` text,
  `image_path` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `gerante_section`
--

INSERT INTO `gerante_section` (`id`, `tag_fr`, `tag_ar`, `title_fr`, `title_ar`, `quote_fr`, `quote_ar`, `image_path`, `updated_at`) VALUES
(1, 'Bienvenue', 'مرحباً', 'Chaque femme est une reine chez nous.', 'كل امرأة هي ملكة عندنا', 'Duo des Reines est né d\'une volonté simple : offrir un espace de déconnexion totale, où le luxe rencontre la tradition.', 'وُلد \"دو دي رين\" من رغبة بسيطة: توفير مساحة لفصل كامل عن ضجيج العالم، حيث يلتقي الفخامة بالتقاليد.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&q=80', '2026-03-17 21:45:13');

-- --------------------------------------------------------

--
-- Structure de la table `packs`
--

DROP TABLE IF EXISTS `packs`;
CREATE TABLE IF NOT EXISTS `packs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name_fr` varchar(255) NOT NULL,
  `name_ar` varchar(255) NOT NULL,
  `badge_fr` varchar(50) DEFAULT NULL,
  `badge_ar` varchar(50) DEFAULT NULL,
  `items_fr` text,
  `items_ar` text,
  `price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `promotion_bar`
--

DROP TABLE IF EXISTS `promotion_bar`;
CREATE TABLE IF NOT EXISTS `promotion_bar` (
  `id` int NOT NULL DEFAULT '1',
  `text_fr` varchar(255) DEFAULT NULL,
  `text_ar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `promotion_bar`
--

INSERT INTO `promotion_bar` (`id`, `text_fr`, `text_ar`, `is_active`, `updated_at`) VALUES
(1, '✨ Offre Spéciale : -20% sur tous les Rituels Hammam ✨', '✨ عرض خاص: -20% على جميع طقوس الحمام ✨', 1, '2026-03-17 21:34:58');

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `title_fr` varchar(255) NOT NULL,
  `title_ar` varchar(255) NOT NULL,
  `desc_fr` text,
  `desc_ar` text,
  `price` decimal(10,2) DEFAULT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int NOT NULL DEFAULT '1',
  `address_fr` varchar(255) DEFAULT NULL,
  `address_ar` varchar(255) DEFAULT NULL,
  `phone_fixed` varchar(20) DEFAULT NULL,
  `phone_mobile` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `maps_iframe_url` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `site_settings`
--

INSERT INTO `site_settings` (`id`, `address_fr`, `address_ar`, `phone_fixed`, `phone_mobile`, `email`, `maps_iframe_url`) VALUES
(1, 'Rue d\'Oran, Avenue Moulay Hassan, Rabat', 'زنقة وهران، شارع مولاي الحسن، الرباط', '05 37 76 19 18', '06 61 59 75 94', 'duodesreines@outlook.com', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3306.9602418520263!2d-6.8378601!3d34.0192534!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda76b8801905387%3A0xc073384f5064789d!2sDuo%20des%20Reines!5e0!3m2!1sfr!2sma!4v1710000000000');

-- --------------------------------------------------------

--
-- Structure de la table `special_offers`
--

DROP TABLE IF EXISTS `special_offers`;
CREATE TABLE IF NOT EXISTS `special_offers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title_fr` varchar(255) DEFAULT NULL,
  `title_ar` varchar(255) DEFAULT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `new_price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin-ddr', '$2y$10$DcM/wkDYFldDLdeWCEXk5Oh2ytvirc7F0UVTVP8FoLuwD5cVRCbjO', '2026-03-17 22:21:13');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
