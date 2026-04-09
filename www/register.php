<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) redirect('/profile.php');

$error = null;

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
    $username   = trim($_POST['username']   ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   = $_POST['password']        ?? '';
    $password2  = $_POST['password2']       ?? '';
    $birth_date = trim($_POST['birth_date'] ?? '');
    $country    = trim($_POST['country']    ?? '');

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $password2) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        $error = "Nom d'utilisateur invalide (3–50 caractères, lettres, chiffres, _).";
    } else {
        $result = register($username, $email, $password, [
            'first_name' => trim($_POST['first_name'] ?? '') ?: null,
            'last_name'  => trim($_POST['last_name']  ?? '') ?: null,
            'birth_date' => $birth_date ?: null,
            'country'    => $country    ?: null,
        ]);
        if ($result['success']) {
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['role']    = 'user';
            flashSet('success', 'Compte créé avec succès ! Bienvenue.');
            redirect('/profile.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription — Mon Formulaire</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --bg: #f9f9f8; --white: #ffffff;
    --border: #e8e6e1; --border2: #d4d0c8;
    --text: #1a1a1a; --muted: #6b6b6b; --light: #9b9b9b;
    --accent: #7c6aff; --accent-h: #6a58ee;
    --error-bg: #fff5f3; --error-bd: #f5c6bb; --error-tx: #c0392b;
    --radius: 10px;
    --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
  }
  html, body { height: 100%; }
  body { background: var(--bg); font-family: 'Inter', sans-serif; color: var(--text); display: flex; flex-direction: column; min-height: 100vh; }
  header { height: 52px; background: var(--white); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 24px; flex-shrink: 0; }
  .logo { font-size: 15px; font-weight: 600; color: var(--text); text-decoration: none; }
  main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 24px; }
  .card { background: var(--white); border: 1px solid var(--border); border-radius: 16px; box-shadow: var(--shadow); padding: 40px; width: 100%; max-width: 460px; }
  .card-header { margin-bottom: 28px; }
  .card-header h1 { font-size: 22px; font-weight: 600; letter-spacing: -0.4px; margin-bottom: 6px; }
  .card-header p  { font-size: 14px; color: var(--muted); }
  .alert { padding: 12px 14px; border-radius: var(--radius); font-size: 13.5px; margin-bottom: 20px; line-height: 1.5; }
  .alert-error { background: var(--error-bg); border: 1px solid var(--error-bd); color: var(--error-tx); }
  .row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .field { margin-bottom: 14px; }
  .field label { display: block; font-size: 13px; font-weight: 500; color: var(--text); margin-bottom: 6px; }
  .field input,
  .field select {
    width: 100%; padding: 10px 13px;
    background: var(--white); border: 1px solid var(--border2);
    border-radius: var(--radius); font-family: 'Inter', sans-serif;
    font-size: 14px; color: var(--text); outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .field input::placeholder { color: #bbb; }
  .field input:focus,
  .field select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(124,106,255,0.12); }
  .select-wrap { position: relative; }
  .select-wrap select { appearance: none; -webkit-appearance: none; padding-right: 32px; }
  .select-wrap::after {
    content: ''; position: absolute; right: 13px; top: 50%;
    transform: translateY(-50%); pointer-events: none;
    width: 0; height: 0;
    border-left: 4px solid transparent; border-right: 4px solid transparent;
    border-top: 5px solid var(--muted);
  }
  .hint { font-size: 11.5px; color: var(--light); margin-top: 4px; }
  .strength { display: flex; gap: 4px; margin-top: 8px; }
  .strength-bar { height: 3px; flex: 1; border-radius: 2px; background: var(--border); transition: background 0.25s; }
  .btn { width: 100%; padding: 11px; border: none; border-radius: var(--radius); font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.15s, transform 0.1s; margin-top: 8px; }
  .btn-primary { background: var(--accent); color: #fff; }
  .btn-primary:hover  { background: var(--accent-h); }
  .btn-primary:active { transform: scale(0.99); }
  .footer-note { text-align: center; font-size: 12.5px; color: var(--muted); margin-top: 20px; }
  .footer-note a { color: var(--accent); text-decoration: none; font-weight: 500; }
  .footer-note a:hover { text-decoration: underline; }
  @media (max-width: 480px) { .row { grid-template-columns: 1fr; } .card { padding: 28px 24px; } }
</style>
</head>
<body>
<header><a class="logo" href="/">Mon Formulaire</a></header>
<main>
  <div class="card">
    <div class="card-header">
      <h1>Créer un compte</h1>
      <p>Rejoignez-nous en quelques secondes</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">

      <div class="row">
        <div class="field">
          <label>Prénom</label>
          <input type="text" name="first_name" placeholder="Jean" value="<?= sanitize($_POST['first_name'] ?? '') ?>">
        </div>
        <div class="field">
          <label>Nom</label>
          <input type="text" name="last_name" placeholder="Dupont" value="<?= sanitize($_POST['last_name'] ?? '') ?>">
        </div>
      </div>

      <div class="field">
        <label>Nom d'utilisateur <span style="color:var(--accent)">*</span></label>
        <input type="text" name="username" placeholder="jean_dupont" required value="<?= sanitize($_POST['username'] ?? '') ?>">
        <p class="hint">Lettres, chiffres et _ uniquement (3–50 caractères)</p>
      </div>

      <div class="field">
        <label>Adresse email <span style="color:var(--accent)">*</span></label>
        <input type="email" name="email" placeholder="jean@exemple.com" required value="<?= sanitize($_POST['email'] ?? '') ?>">
      </div>

      <div class="row">
        <div class="field">
          <label>Date de naissance</label>
          <input type="date" name="birth_date" max="<?= date('Y-m-d') ?>" value="<?= sanitize($_POST['birth_date'] ?? '') ?>">
        </div>
        <div class="field">
          <label>Pays de résidence</label>
          <div class="select-wrap">
            <select name="country">
              <option value="">— Sélectionner —</option>
              <?php foreach ($countries as $code => $name): ?>
                <option value="<?= $code ?>" <?= ($_POST['country'] ?? '') === $code ? 'selected' : '' ?>>
                  <?= htmlspecialchars($name) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="field">
        <label>Mot de passe <span style="color:var(--accent)">*</span></label>
        <input type="password" name="password" placeholder="Minimum 8 caractères" required oninput="strength(this.value)">
        <div class="strength">
          <div class="strength-bar" id="s1"></div>
          <div class="strength-bar" id="s2"></div>
          <div class="strength-bar" id="s3"></div>
          <div class="strength-bar" id="s4"></div>
        </div>
      </div>

      <div class="field">
        <label>Confirmer le mot de passe <span style="color:var(--accent)">*</span></label>
        <input type="password" name="password2" placeholder="••••••••" required>
      </div>

      <button class="btn btn-primary" type="submit">Créer mon compte</button>
    </form>

    <p class="footer-note">Déjà un compte ? <a href="/login.php">Se connecter</a></p>
  </div>
</main>
<script>
function strength(v) {
  let s = 0;
  if (v.length >= 8) s++;
  if (v.length >= 12) s++;
  if (/[A-Z]/.test(v) && /[0-9]/.test(v)) s++;
  if (/[^A-Za-z0-9]/.test(v)) s++;
  const c = ['#e74c3c','#e67e22','#f1c40f','#27ae60'];
  ['s1','s2','s3','s4'].forEach((id,i) => {
    document.getElementById(id).style.background = i < s ? c[s-1] : 'var(--border)';
  });
}
</script>
</body>
</html>
