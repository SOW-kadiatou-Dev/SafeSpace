<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../config/helpers.php';
bootstrapAdminFromCurrentUser();
$pdoReady = isset($pdo) && $pdo instanceof PDO;
if ($pdoReady) {
    syncCurrentUserState($pdo);
}
$user = currentUser();
$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? 'index.php');
if ($current === '') {
    $current = 'index.php';
}
function isActive(string $file, string $current): string {
    return $file === $current ? 'is-active' : '';
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SafeSpace</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <svg class="noise-defs" aria-hidden="true" width="0" height="0" focusable="false">
    <filter id="noise">
      <feTurbulence type="fractalNoise" baseFrequency="0.65" numOctaves="2" stitchTiles="stitch"/>
    </filter>
  </svg>
  <div class="cursor-glow" aria-hidden="true"></div>
  <header class="site-header">
    <div class="container nav-wrap">
      <nav class="nav" aria-label="Navigation principale">
        <a class="<?= isActive('index.php', $current) ?>" href="index.php">Accueil</a>
        <a class="<?= isActive('feed.php', $current) ?>" href="feed.php">Communaute</a>
        <a class="<?= isActive('share.php', $current) ?>" href="share.php">Partager</a>
        <a class="<?= isActive('inspiration.php', $current) ?>" href="inspiration.php">Inspiration</a>
        <?php if ($user && isAdmin($user)): ?>
          <a class="<?= isActive('admin.php', $current) ?>" href="admin.php">Mon espace admin</a>
          <a class="<?= isActive('creator.php', $current) ?>" href="creator.php">Creator Studio</a>
          <a class="<?= isActive('creator_earnings.php', $current) ?>" href="creator_earnings.php">Creator Earnings</a>
          <a class="<?= isActive('admin_settings.php', $current) ?>" href="admin_settings.php">Admin Settings</a>
        <?php endif; ?>
        <?php if ($user): ?>
          <a class="<?= isActive('logout.php', $current) ?>" href="logout.php">Deconnexion</a>
        <?php else: ?>
          <a class="<?= isActive('login.php', $current) ?>" href="login.php">Connexion</a>
          <a class="<?= isActive('register.php', $current) ?>" href="register.php">Inscription</a>
        <?php endif; ?>
        <a class="nav-premium <?= isActive('pricing.php', $current) ?>" href="pricing.php">PREMIUM</a>
      </nav>
    </div>
  </header>
  <main class="container site-main">
