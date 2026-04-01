<?php
// 1. Configuration de la base de données
// Attention : 'db-1' doit correspondre au nom du service dans ton docker-compose.yml
$host = 'db-1'; 
$db   = 'nom_de_ta_base'; // Remplace par le nom de ta base
$user = 'root';           // Ton utilisateur (souvent root en dev)
$pass = 'ton_mot_de_passe'; // Ton mot de passe défini dans le Docker
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// 2. Récupération des données du formulaire
// On vérifie que les champs ne sont pas vides
if (!empty($_POST['login']) && !empty($_POST['mdp'])) {
    $login_saisi = $_POST['login'];
    $mdp_saisi = $_POST['mdp'];

    // 3. Requête pour chercher l'utilisateur
    // On utilise des requêtes préparées pour la sécurité
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = ?");
    $stmt->execute([$login_saisi]);
    $user = $stmt->fetch();

    // 4. Vérification
    // Note : Ici on compare en clair. En production, on utilise password_verify()
    if ($user && $mdp_saisi === $user['password']) {
        // SUCCÈS
        echo "<h1>Connexion réussie !</h1>";
        echo "<p>Bienvenue, " . htmlspecialchars($user['username']) . ".</p>";
    } else {
        // ÉCHEC
        echo "<h1>Accès refusé</h1>";
        echo "<p>Identifiants incorrects.</p>";
        echo '<a href="index.html">Réessayer</a>';
    }
} else {
    echo "Veuillez remplir tous les champs.";
}
?>