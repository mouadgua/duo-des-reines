<?php
require_once 'db.php';

$user = 'admin-ddr'; // Votre identifiant
$pass = 'duo-des-reines-2020206@@'; // Votre mot de passe
$hashedPass = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
if($stmt->execute([$user, $hashedPass])) {
    echo "Compte administrateur créé avec succès !";
}
?>