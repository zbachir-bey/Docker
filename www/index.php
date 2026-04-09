<?php
require_once __DIR__ . '/includes/auth.php';
isLoggedIn() ? redirect('/profile.php') : redirect('/login.php');
