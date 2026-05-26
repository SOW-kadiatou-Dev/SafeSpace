<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
requireAdmin();
require_once __DIR__ . '/_top.php';

$message = '';
$currentAdminEmail = getAdminEmail();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newEmail = strtolower(trim($_POST['admin_email'] ?? ''));
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email admin invalide.';
    } else {
        setAdminEmail($newEmail);

        $stmt = $pdo->prepare('UPDATE users SET role = CASE WHEN lower(email)=? THEN "admin" ELSE "member" END');
        $stmt->execute([$newEmail]);

        if (isset($_SESSION['user']['email']) && strtolower($_SESSION['user']['email']) === $newEmail) {
            $_SESSION['user']['role'] = 'admin';
        }

        $currentAdminEmail = $newEmail;
        $message = 'Email admin mis a jour.';
    }
}
?>
<section class="hero page-hero">
  <h1>Admin Settings</h1>
  <p class="muted">Definis ici ton compte admin principal.</p>
</section>

<?php if ($message !== ''): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>

<section class="card soft-card" style="margin-top:1rem;">
  <h2>Email admin principal</h2>
  <form method="post">
    <div class="field">
      <label>Email admin</label>
      <input type="email" name="admin_email" value="<?= e($currentAdminEmail) ?>" required />
    </div>
    <button class="btn" type="submit">Enregistrer</button>
  </form>
  <p class="muted" style="margin-top:.8rem;">Seul cet email verra Creator Studio et Creator Earnings.</p>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
