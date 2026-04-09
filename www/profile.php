<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user  = getUser((int)$_SESSION['user_id']);
$error = null;
$flash = flashGet();

$countries = [
  'AF'=>'Afghanistan','ZA'=>'Afrique du Sud','AL'=>'Albanie','DZ'=>'Algérie',
  'DE'=>'Allemagne','AD'=>'Andorre','AO'=>'Angola','AR'=>'Argentine',
  'AM'=>'Arménie','AU'=>'Australie','AT'=>'Autriche','AZ'=>'Azerbaïdjan',
  'BE'=>'Belgique','BJ'=>'Bénin','BO'=>'Bolivie','BA'=>'Bosnie-Herzégovine',
  'BR'=>'Brésil','BG'=>'Bulgarie','BF'=>'Burkina Faso','BI'=>'Burundi',
  'CM'=>'Cameroun','CA'=>'Canada','CL'=>'Chili','CN'=>'Chine',
  'CY'=>'Chypre','CO'=>'Colombie','KM'=>'Comores','CG'=>'Congo',
  'CD'=>'Congo (RDC)','KR'=>'Corée du Sud','HR'=>'Croatie','CU'=>'Cuba',
  'DK'=>'Danemark','EG'=>'Égypte','AE'=>'Émirats arabes unis','EC'=>'Équateur',
  'ES'=>'Espagne','EE'=>'Estonie','ET'=>'Éthiopie','FI'=>'Finlande',
  'FR'=>'France','GA'=>'Gabon','GH'=>'Ghana','GR'=>'Grèce',
  'GT'=>'Guatemala','GN'=>'Guinée','HT'=>'Haïti','HN'=>'Honduras',
  'HU'=>'Hongrie','IN'=>'Inde','ID'=>'Indonésie','IQ'=>'Irak',
  'IR'=>'Iran','IE'=>'Irlande','IS'=>'Islande','IL'=>'Israël',
  'IT'=>'Italie','CI'=>"Côte d'Ivoire",'JM'=>'Jamaïque','JP'=>'Japon',
  'JO'=>'Jordanie','KZ'=>'Kazakhstan','KE'=>'Kenya','KW'=>'Koweït',
  'LB'=>'Liban','LY'=>'Libye','LT'=>'Lituanie','LU'=>'Luxembourg',
  'MK'=>'Macédoine du Nord','MG'=>'Madagascar','MY'=>'Malaisie','ML'=>'Mali',
  'MT'=>'Malte','MA'=>'Maroc','MR'=>'Mauritanie','MX'=>'Mexique',
  'MD'=>'Moldavie','MC'=>'Monaco','MN'=>'Mongolie','ME'=>'Monténégro',
  'MZ'=>'Mozambique','NA'=>'Namibie','NP'=>'Népal','NI'=>'Nicaragua',
  'NE'=>'Niger','NG'=>'Nigeria','NO'=>'Norvège','NZ'=>'Nouvelle-Zélande',
  'OM'=>'Oman','UG'=>'Ouganda','UZ'=>'Ouzbékistan','PK'=>'Pakistan',
  'PA'=>'Panama','PY'=>'Paraguay','NL'=>'Pays-Bas','PE'=>'Pérou',
  'PH'=>'Philippines','PL'=>'Pologne','PT'=>'Portugal','QA'=>'Qatar',
  'RO'=>'Roumanie','GB'=>'Royaume-Uni','RU'=>'Russie','RW'=>'Rwanda',
  'SA'=>'Arabie Saoudite','SN'=>'Sénégal','RS'=>'Serbie','SL'=>'Sierra Leone',
  'SK'=>'Slovaquie','SI'=>'Slovénie','SO'=>'Somalie','SD'=>'Soudan',
  'SS'=>'Soudan du Sud','SE'=>'Suède','CH'=>'Suisse','SY'=>'Syrie',
  'TJ'=>'Tadjikistan','TZ'=>'Tanzanie','TD'=>'Tchad','CZ'=>'Tchéquie',
  'TH'=>'Thaïlande','TG'=>'Togo','TN'=>'Tunisie','TR'=>'Turquie',
  'UA'=>'Ukraine','UY'=>'Uruguay','US'=>'États-Unis','VE'=>'Venezuela',
  'VN'=>'Viêt Nam','YE'=>'Yémen','ZM'=>'Zambie','ZW'=>'Zimbabwe',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username'         => trim($_POST['username']         ?? ''),
        'email'            => trim($_POST['email']            ?? ''),
        'first_name'       => trim($_POST['first_name']       ?? ''),
        'last_name'        => trim($_POST['last_name']        ?? ''),
        'birth_date'       => trim($_POST['birth_date']       ?? ''),
        'country'          => trim($_POST['country']          ?? ''),
        'current_password' => $_POST['current_password']      ?? '',
        'new_password'     => $_POST['new_password']          ?? '',
        'new_password2'    => $_POST['new_password2']         ?? '',
    ];

    if (empty($data['username']) || empty($data['email'])) {
        $error = "Nom d'utilisateur et email sont obligatoires.";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (!empty($data['new_password'])) {
        if (strlen($data['new_password']) < 8)
            $error = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        elseif ($data['new_password'] !== $data['new_password2'])
            $error = 'Les nouveaux mots de passe ne correspondent pas.';
        elseif (empty($data['current_password']))
            $error = 'Veuillez saisir votre mot de passe actuel.';
    }

    if (!$error) {
        $result = updateUser((int)$_SESSION['user_id'], $data);
        if ($result['success']) {
            flashSet('success', $result['message']);
            redirect('/profile.php');
        } else {
            $error = $result['message'];
        }
    }
    if (!$error) $user = getUser((int)$_SESSION['user_id']);
}

$initials = strtoupper(
    mb_substr($user['first_name'] ?: $user['username'], 0, 1) .
    mb_substr($user['last_name'] ?? '', 0, 1)
);
$fullname = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['username'];
$currentCountry = $user['country'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon Profil — Mon Formulaire</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --bg: #f9f9f8; --white: #ffffff;
    --border: #e8e6e1; --border2: #d4d0c8;
    --text: #1a1a1a; --muted: #6b6b6b; --light: #9b9b9b;
    --accent: #7c6aff; --accent-h: #6a58ee; --accent-light: #f0eeff;
    --error-bg: #fff5f3; --error-bd: #f5c6bb; --error-tx: #c0392b;
    --success-bg: #f0faf4; --success-bd: #b7dfc7; --success-tx: #276749;
    --radius: 10px;
    --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
  }
  html, body { height: 100%; }
  body { background: var(--bg); font-family: 'Inter', sans-serif; color: var(--text); min-height: 100vh; }
  header { height: 52px; background: var(--white); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; position: sticky; top: 0; z-index: 50; }
  .logo { font-size: 15px; font-weight: 600; color: var(--text); text-decoration: none; }
  .nav-right { display: flex; align-items: center; gap: 16px; }
  .nav-user { font-size: 13px; color: var(--muted); }
  .nav-user strong { color: var(--text); font-weight: 500; }
  .btn-logout { font-size: 13px; font-weight: 500; color: var(--muted); text-decoration: none; padding: 6px 14px; border: 1px solid var(--border2); border-radius: 8px; transition: all 0.15s; }
  .btn-logout:hover { background: var(--bg); color: var(--text); }
  .page { max-width: 720px; margin: 0 auto; padding: 40px 24px 80px; }
  .profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 32px; }
  .avatar { width: 64px; height: 64px; border-radius: 50%; background: var(--accent-light); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 600; color: var(--accent); flex-shrink: 0; }
  .profile-info h1 { font-size: 20px; font-weight: 600; letter-spacing: -0.3px; margin-bottom: 3px; }
  .profile-info p  { font-size: 13.5px; color: var(--muted); }
  .alert { padding: 12px 14px; border-radius: var(--radius); font-size: 13.5px; margin-bottom: 24px; line-height: 1.5; }
  .alert-error   { background: var(--error-bg);   border: 1px solid var(--error-bd);   color: var(--error-tx); }
  .alert-success { background: var(--success-bg); border: 1px solid var(--success-bd); color: var(--success-tx); }
  .section { background: var(--white); border: 1px solid var(--border); border-radius: 12px; margin-bottom: 16px; box-shadow: var(--shadow); }
  .section-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; }
  .section-header h2 { font-size: 14px; font-weight: 600; }
  .section-header p  { font-size: 12.5px; color: var(--muted); margin-top: 1px; }
  .section-icon { width: 32px; height: 32px; border-radius: 8px; background: var(--accent-light); color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
  .section-body { padding: 24px; }
  .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
  .col-full { grid-column: 1 / -1; }
  .field label { display: block; font-size: 12.5px; font-weight: 500; color: var(--text); margin-bottom: 6px; }
  .field input, .field select {
    width: 100%; padding: 9px 12px;
    background: var(--white); border: 1px solid var(--border2);
    border-radius: var(--radius); font-family: 'Inter', sans-serif;
    font-size: 13.5px; color: var(--text); outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .field input::placeholder { color: #bbb; }
  .field input:focus, .field select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(124,106,255,0.10); }
  .select-wrap { position: relative; }
  .select-wrap select { appearance: none; -webkit-appearance: none; padding-right: 28px; }
  .select-wrap::after { content: ''; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-left: 4px solid transparent; border-right: 4px solid transparent; border-top: 5px solid var(--muted); pointer-events: none; }
  .section-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
  .section-footer p { font-size: 12px; color: var(--light); }
  .btn-save { padding: 9px 24px; border: none; border-radius: var(--radius); background: var(--accent); color: #fff; font-family: 'Inter', sans-serif; font-size: 13.5px; font-weight: 500; cursor: pointer; transition: background 0.15s; }
  .btn-save:hover { background: var(--accent-h); }
  .security-tip { font-size: 12.5px; color: var(--muted); background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; line-height: 1.5; }
  .gap-top { margin-top: 16px; }
  @media (max-width: 600px) { .grid-2, .grid-3 { grid-template-columns: 1fr; } .section-footer { flex-direction: column; } .btn-save { width: 100%; } }
</style>
</head>
<body>

<header>
  <a class="logo" href="/">Mon Formulaire</a>
  <div class="nav-right">
    <span class="nav-user">Connecté en tant que <strong><?= sanitize($user['username']) ?></strong></span>
    <a class="btn-logout" href="/logout.php">Déconnexion</a>
  </div>
</header>

<div class="page">

  <div class="profile-header">
    <div class="avatar"><?= htmlspecialchars($initials) ?></div>
    <div class="profile-info">
      <h1><?= sanitize($fullname) ?></h1>
      <p>@<?= sanitize($user['username']) ?> · Membre depuis <?= (new DateTime($user['created_at']))->format('d/m/Y') ?></p>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
      <?= htmlspecialchars($flash['message']) ?>
    </div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="">

    <!-- Informations personnelles -->
    <div class="section">
      <div class="section-header">
        <div class="section-icon">👤</div>
        <div>
          <h2>Informations personnelles</h2>
          <p>Vos données de profil</p>
        </div>
      </div>
      <div class="section-body">

        <!-- Prénom / Nom -->
        <div class="grid-2">
          <div class="field">
            <label>Prénom</label>
            <input type="text" name="first_name" placeholder="Jean" value="<?= sanitize($user['first_name'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Nom</label>
            <input type="text" name="last_name" placeholder="Dupont" value="<?= sanitize($user['last_name'] ?? '') ?>">
          </div>
        </div>

        <!-- Username / Email -->
        <div class="grid-2 gap-top">
          <div class="field">
            <label>Nom d'utilisateur *</label>
            <input type="text" name="username" required value="<?= sanitize($user['username']) ?>">
          </div>
          <div class="field">
            <label>Adresse email *</label>
            <input type="email" name="email" required value="<?= sanitize($user['email']) ?>">
          </div>
        </div>

        <!-- Date de naissance / Pays -->
        <div class="grid-2 gap-top">
          <div class="field">
            <label>Date de naissance</label>
            <input type="date" name="birth_date" max="<?= date('Y-m-d') ?>" value="<?= sanitize($user['birth_date'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Pays de résidence</label>
            <div class="select-wrap">
              <select name="country">
                <option value="">— Sélectionner —</option>
                <?php foreach ($countries as $code => $name): ?>
                  <option value="<?= $code ?>" <?= $currentCountry === $code ? 'selected' : '' ?>>
                    <?= htmlspecialchars($name) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Mot de passe -->
    <div class="section">
      <div class="section-header">
        <div class="section-icon">🔒</div>
        <div>
          <h2>Mot de passe</h2>
          <p>Laisser vide pour ne pas modifier</p>
        </div>
      </div>
      <div class="section-body">
        <p class="security-tip">Pour changer votre mot de passe, saisissez d'abord votre mot de passe actuel puis le nouveau.</p>
        <div class="grid-3">
          <div class="field">
            <label>Mot de passe actuel</label>
            <input type="password" name="current_password" placeholder="••••••••">
          </div>
          <div class="field">
            <label>Nouveau mot de passe</label>
            <input type="password" name="new_password" placeholder="Min. 8 caractères">
          </div>
          <div class="field">
            <label>Confirmer</label>
            <input type="password" name="new_password2" placeholder="••••••••">
          </div>
        </div>
      </div>
      <div class="section-footer">
        <p>* Champs obligatoires · Mots de passe chiffrés en bcrypt</p>
        <button class="btn-save" type="submit">Enregistrer les modifications</button>
      </div>
    </div>

  </form>
</div>

</body>
</html>
