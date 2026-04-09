<?php
require_once __DIR__ . '/../config/database.php';

session_start();

// Empêcher le cache navigateur sur toutes les pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

function redirect(string $path): void {
    header("Location: $path");
    exit;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(): void {
    if (!isLoggedIn()) redirect('/login.php');
}

function requireAdmin(): void {
    if (!isLoggedIn()) redirect('/login.php');
    if (!isAdmin()) redirect('/profile.php');
}

function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function flashSet(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flashGet(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function register(string $username, string $email, string $password, array $extra = []): array {
    $db = getDB();

    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => "Nom d'utilisateur ou email déjà utilisé."];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $stmt = $db->prepare("
        INSERT INTO users (username, email, password, first_name, last_name, birth_date, country, role)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'user')
    ");
    $stmt->execute([
        $username,
        $email,
        $hash,
        $extra['first_name'] ?? null,
        $extra['last_name']  ?? null,
        $extra['birth_date'] ?? null,
        $extra['country']    ?? null,
    ]);

    return ['success' => true, 'id' => (int)$db->lastInsertId()];
}

function login(string $identifier, string $password): array {
    $db = getDB();

    $stmt = $db->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) LIMIT 1");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Identifiants incorrects.'];
    }

    if (!empty($user['disabled_at'])) {
        return ['success' => false, 'disabled' => true, 'message' => 'Compte désactivé.'];
    }

    // Stocker le rôle en session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role']    = $user['role'];
    return ['success' => true, 'user' => $user];
}

function getUser(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function updateUser(int $id, array $data): array {
    $db = getDB();

    $stmt = $db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$data['username'], $data['email'], $id]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => "Nom d'utilisateur ou email déjà utilisé."];
    }

    $fields = [
        'username'   => $data['username'],
        'email'      => $data['email'],
        'first_name' => $data['first_name'] ?? null,
        'last_name'  => $data['last_name']  ?? null,
        'birth_date' => $data['birth_date'] ?? null,
        'country'    => $data['country']    ?? null,
    ];

    if (!empty($data['new_password'])) {
        if (!password_verify($data['current_password'], getUser($id)['password'])) {
            return ['success' => false, 'message' => 'Mot de passe actuel incorrect.'];
        }
        $fields['password'] = password_hash($data['new_password'], PASSWORD_BCRYPT, ['cost' => 12]);
    }

    $setParts = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($fields)));
    $fields['id'] = $id;
    $stmt = $db->prepare("UPDATE users SET $setParts WHERE id = :id");
    $stmt->execute($fields);

    return ['success' => true, 'message' => 'Profil mis à jour avec succès !'];
}
