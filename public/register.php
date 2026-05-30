<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $message = 'Tous les champs sont obligatoires.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'Cet email existe deja.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = strtolower($email) === getAdminEmail() ? 'admin' : 'member';
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hash, $role]);

            $newUserStmt = $pdo->prepare('SELECT id, name, email, role, is_premium FROM users WHERE email = ?');
            $newUserStmt->execute([$email]);
            $newUser = $newUserStmt->fetch();
            if ($newUser) {
                bootstrapAdminIfNeeded($pdo, $newUser);
            }

            header('Location: login.php');
            exit;
        }
    }
}

require_once __DIR__ . '/_top.php';
?>
<section class="card auth-panel">
  <h1>Inscription</h1>
  <?php if ($message !== ''): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>
  <form method="post">
    <div class="field"><label>Nom</label><input name="name" required /></div>
    <div class="field"><label>Email</label><input type="email" name="email" required /></div>
    <div class="field"><label>Mot de passe</label><input type="password" name="password" required /></div>
    <button class="btn" type="submit">Creer mon compte</button>
  </form>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
