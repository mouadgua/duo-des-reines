<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Recherche de l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Authentification réussie
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_user'] = $user['username'];

            header('Location: admin-dashboard.php');
            exit();
        } else {
            $error = "Identifiants invalides.";
            header('Location: login.php?error=' . urlencode($error));
            exit();
        }
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administration | Duo des Reines</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

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
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .login-card {
            background: white;
            padding: 3rem;
            border-radius: 4px;
            box-shadow: 0 20px 50px rgba(45, 36, 30, 0.05);
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(212, 175, 55, 0.1);
            opacity: 0;
            transform: translateY(20px);
        }

        .gold-button {
            background: linear-gradient(135deg, var(--royal-gold) 0%, #B68D40 100%);
            color: white;
            transition: all 0.3s ease;
        }

        .gold-button:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(182, 141, 64, 0.2);
        }

        input:focus {
            outline: none;
            border-color: var(--royal-gold) !important;
        }
    </style>
</head>

<body>

    <div class="login-card" id="authForm">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-serif italic mb-2">Duo des Reines</h1>
            <p class="text-[10px] uppercase tracking-[0.3em] text-[#D4AF37]">Espace Privé Administrateur</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-50 text-red-500 text-[10px] p-3 mb-6 rounded-sm border border-red-100 uppercase tracking-widest text-center">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="auth.php" method="POST" class="space-y-6">
            <div>
                <label class="block text-[11px] uppercase tracking-widest font-semibold mb-2 text-gray-400">Identifiant</label>
                <input type="text" name="username" required
                    class="w-full border-b border-gray-200 py-3 text-sm bg-transparent transition-all focus:border-[#D4AF37] outline-none"
                    placeholder="Votre nom d'utilisateur">
            </div>

            <div>
                <label class="block text-[11px] uppercase tracking-widest font-semibold mb-2 text-gray-400">Mot de passe</label>
                <input type="password" name="password" required
                    class="w-full border-b border-gray-200 py-3 text-sm bg-transparent transition-all focus:border-[#D4AF37] outline-none"
                    placeholder="••••••••">
            </div>

            <button type="submit" class="w-full gold-button py-4 rounded-sm text-[11px] uppercase font-bold tracking-[0.2em] mt-8 shadow-lg">
                S'authentifier
            </button>
        </form>

        <div class="mt-12 text-center">
            <a href="index.html" class="text-[10px] uppercase tracking-widest text-gray-400 hover:text-gold transition-colors">
                ← Retour au site public
            </a>
        </div>
    </div>

    <script>
        // Animation d'entrée GSAP
        window.addEventListener('load', () => {
            gsap.to("#authForm", {
                opacity: 1,
                y: 0,
                duration: 1.2,
                ease: "power3.out"
            });
        });
    </script>
</body>

</html>