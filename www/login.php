<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) redirect('/profile.php');

$error    = null;
$disabled = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $result = login($identifier, $password);
        if ($result['success']) {
            redirect('/profile.php');
        } else {
            $error    = $result['message'];
            $disabled = isset($result['disabled']) && $result['disabled'];
        }
    }
}

$flash = flashGet();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — Mon Formulaire</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:        #f9f9f8;
    --white:     #ffffff;
    --border:    #e8e6e1;
    --border2:   #d4d0c8;
    --text:      #1a1a1a;
    --muted:     #6b6b6b;
    --light:     #9b9b9b;
    --accent:    #7c6aff;
    --accent-h:  #6a58ee;
    --error-bg:  #fff5f3;
    --error-bd:  #f5c6bb;
    --error-tx:  #c0392b;
    --success-bg:#f0faf4;
    --success-bd:#b7dfc7;
    --success-tx:#276749;
    --radius:    10px;
    --shadow:    0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
  }

  html, body { height: 100%; }

  body {
    background: var(--bg);
    font-family: 'Inter', sans-serif;
    color: var(--text);
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  /* Top bar */
  header {
    height: 52px;
    background: var(--white);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    padding: 0 24px;
    flex-shrink: 0;
  }
  .logo {
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
    text-decoration: none;
    letter-spacing: -0.2px;
  }

  /* Center content */
  main {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 24px;
  }

  .card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: var(--shadow);
    padding: 40px;
    width: 100%;
    max-width: 400px;
  }

  .card-header { margin-bottom: 28px; }
  .card-header h1 {
    font-size: 22px;
    font-weight: 600;
    color: var(--text);
    letter-spacing: -0.4px;
    margin-bottom: 6px;
  }
  .card-header p {
    font-size: 14px;
    color: var(--muted);
  }

  .alert {
    padding: 14px 16px;
    border-radius: var(--radius);
    font-size: 13.5px;
    margin-bottom: 20px;
    line-height: 1.6;
  }
  .alert-error    { background: #fff5f3; border: 1px solid #f5c6bb; color: #c0392b; }
  .alert-success  { background: #f0faf4; border: 1px solid #b7dfc7; color: #276749; }
  .alert-disabled {
    background: #fffbeb; border: 1px solid #fcd34d; color: #92400e;
    border-left: 4px solid #f59e0b;
  }
  .alert-disabled strong { display: block; font-size: 14px; margin-bottom: 4px; }
  .alert-disabled a { color: #92400e; font-weight: 600; }

  .field { margin-bottom: 16px; }
  .field label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--text);
    margin-bottom: 6px;
  }
  .field input {
    width: 100%;
    padding: 10px 13px;
    background: var(--white);
    border: 1px solid var(--border2);
    border-radius: var(--radius);
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--text);
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .field input::placeholder { color: #bbb; }
  .field input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(124,106,255,0.12);
  }

  .btn {
    width: 100%;
    padding: 11px;
    border: none;
    border-radius: var(--radius);
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.15s, transform 0.1s;
    margin-top: 8px;
  }
  .btn-primary {
    background: var(--accent);
    color: #fff;
  }
  .btn-primary:hover  { background: var(--accent-h); }
  .btn-primary:active { transform: scale(0.99); }

  .divider {
    display: flex; align-items: center; gap: 12px;
    margin: 20px 0; color: var(--light); font-size: 12px;
  }
  .divider::before, .divider::after {
    content: ''; flex: 1; height: 1px; background: var(--border);
  }

  .btn-secondary {
    background: transparent;
    color: var(--text);
    border: 1px solid var(--border2);
  }
  .btn-secondary:hover { background: var(--bg); }

  .footer-note {
    text-align: center;
    font-size: 12.5px;
    color: var(--muted);
    margin-top: 20px;
  }
  .footer-note a { color: var(--accent); text-decoration: none; font-weight: 500; }
  .footer-note a:hover { text-decoration: underline; }
</style>
</head>
<body>

<header>
  <a class="logo" href="/">Mon Formulaire</a>
</header>

<main>
  <div class="card">
    <div class="card-header">
      <h1>Connexion</h1>
      <p>Entrez vos identifiants pour continuer</p>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
        <?= htmlspecialchars($flash['message']) ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <?php if ($disabled): ?>
        <div class="alert alert-disabled">
          <strong>Compte désactivé</strong>
          Votre compte a été suspendu. Veuillez contacter un administrateur pour le réactiver.
        </div>
      <?php else: ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="field">
        <label for="identifier">Email ou nom d'utilisateur</label>
        <input id="identifier" type="text" name="identifier"
               placeholder="vous@exemple.com"
               value="<?= sanitize($_POST['identifier'] ?? '') ?>" required autofocus>
      </div>
      <div class="field">
        <label for="password">Mot de passe</label>
        <input id="password" type="password" name="password"
               placeholder="••••••••" required>
      </div>
      <button class="btn btn-primary" type="submit">Se connecter</button>
    </form>

    <div class="divider">ou</div>
    <a class="btn btn-secondary" href="/register.php" style="display:block;text-align:center;text-decoration:none;padding:11px;">
      Créer un compte
    </a>

    <p class="footer-note">Pas encore inscrit ? <a href="/register.php">S'inscrire gratuitement</a></p>
  </div>
</main>

</body>
</html>
