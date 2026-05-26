<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT id, name, email, password_hash, role, is_premium, premium_expires_at FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $dbUser = $stmt->fetch();

    if (!$dbUser || !password_verify($password, $dbUser['password_hash'])) {
        $message = 'Identifiants invalides.';
    } else {
        $effectiveRole = strtolower((string)($dbUser['role'] ?? 'member'));
        if (!in_array($effectiveRole, ['member', 'creator', 'admin'], true)) {
            $effectiveRole = 'member';
        }

        if (strtolower($dbUser['email']) === getAdminEmail()) {
            $effectiveRole = 'admin';
        }

        if ($effectiveRole !== 'admin') {
            $dbUser = bootstrapAdminIfNeeded($pdo, $dbUser);
        $effectiveRole = strtolower((string)($dbUser['role'] ?? 'member'));
        }

        if ($effectiveRole !== $dbUser['role']) {
            $up = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
            $up->execute([$effectiveRole, $dbUser['id']]);
        }

        $_SESSION['user'] = [
            'id' => (int)$dbUser['id'],
            'name' => $dbUser['name'],
            'email' => $dbUser['email'],
            'role' => $effectiveRole,
            'is_premium' => (int)$dbUser['is_premium'],
            'premium_expires_at' => $dbUser['premium_expires_at'] ?? null
        ];
        header('Location: index.php');
        exit;
    }
}

require_once __DIR__ . '/_top.php';
?>
<section class="card auth-panel">
  <h1>Connexion</h1>
  <?php if ($message !== ''): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>
  <form method="post">
    <div class="field"><label>Email</label><input type="email" name="email" required /></div>
    <div class="field"><label>Mot de passe</label><input type="password" name="password" required /></div>
    <button class="btn" type="submit">Se connecter</button>
  </form>
  <p class="muted" style="margin-top:.8rem;"><a href="forgot_password.php">Mot de passe oublié ?</a></p>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
