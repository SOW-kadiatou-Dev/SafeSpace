<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
requireAdmin();
require_once __DIR__ . '/_top.php';

$stats = [
    'users_total' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'admins_total' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE lower(role) = 'admin'")->fetchColumn(),
    'messages_total' => (int)$pdo->query('SELECT COUNT(*) FROM private_messages')->fetchColumn(),
];

$users = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC LIMIT 30')->fetchAll();
$messages = $pdo->query('SELECT id, conversation_key, sender_pseudo, content, status, created_at FROM private_messages ORDER BY id DESC LIMIT 30')->fetchAll();
?>

<section class="hero page-hero">
  <h1>Dashboard Admin</h1>
  <p class="muted">Vue centralisee des comptes, roles et messages prives.</p>
  <div class="row cta-row">
    <a class="btn" href="admin_settings.php">Parametres admin</a>
    <a class="btn btn-outline" href="index.php">Retour accueil</a>
  </div>
</section>

<section class="metrics-row">
  <article class="metric-card">
    <span class="muted">Utilisateurs</span>
    <strong class="metric-number"><?= $stats['users_total'] ?></strong>
  </article>
  <article class="metric-card">
    <span class="muted">Admins</span>
    <strong class="metric-number"><?= $stats['admins_total'] ?></strong>
  </article>
  <article class="metric-card">
    <span class="muted">Messages prives</span>
    <strong class="metric-number"><?= $stats['messages_total'] ?></strong>
  </article>
  <article class="metric-card">
    <span class="muted">Charge recente</span>
    <strong class="metric-number"><?= count($users) + count($messages) ?></strong>
  </article>
</section>

<section class="card soft-card" style="margin-top:1rem; overflow:auto;">
  <h2>Utilisateurs recents</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Email</th>
        <th>Role</th>
        <th>Creation</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$users): ?>
        <tr><td colspan="5" class="muted">Aucun utilisateur.</td></tr>
      <?php else: ?>
        <?php foreach ($users as $u): ?>
          <?php $role = strtolower((string)($u['role'] ?? 'member')); ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= e((string)$u['name']) ?></td>
            <td><?= e((string)$u['email']) ?></td>
            <td>
              <span class="chip"><?= e($role) ?></span>
            </td>
            <td><?= e((string)$u['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</section>

<section class="card soft-card" style="margin-top:1rem; overflow:auto;">
  <h2>Messages prives recents</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Conversation</th>
        <th>Expediteur</th>
        <th>Message</th>
        <th>Statut</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$messages): ?>
        <tr><td colspan="6" class="muted">Aucun message.</td></tr>
      <?php else: ?>
        <?php foreach ($messages as $m): ?>
          <?php $status = strtolower((string)($m['status'] ?? 'published')); ?>
          <tr>
            <td><?= (int)$m['id'] ?></td>
            <td><?= e((string)$m['conversation_key']) ?></td>
            <td><?= e((string)$m['sender_pseudo']) ?></td>
            <td><?= nl2br(e((string)$m['content'])) ?></td>
            <td><span class="chip"><?= e($status) ?></span></td>
            <td><?= e((string)$m['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</section>

<?php require_once __DIR__ . '/_bottom.php'; ?>
