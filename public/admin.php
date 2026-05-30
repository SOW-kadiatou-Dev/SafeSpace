<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
requireAdmin();
$user = currentUser();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'publish') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $tier = ($_POST['tier'] ?? 'free') === 'premium' ? 'premium' : 'free';
        $isPublished = ($_POST['visibility'] ?? 'draft') === 'published' ? 1 : 0;

        if ($title !== '' && $content !== '') {
            $stmt = $pdo->prepare('INSERT INTO motivation_posts (user_id, title, content, category, tier, is_published) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$user['id'], $title, $content, $category !== '' ? $category : null, $tier, $isPublished]);
            $message = $isPublished ? 'Publication anonyme en ligne.' : 'Brouillon enregistré.';
        }
    }
}

$view = $_GET['view'] ?? 'form';

if ($view === 'users') {
    $stats = [
        'users_total' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'admins_total' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE lower(role) = 'admin'")->fetchColumn(),
        'messages_total' => (int)$pdo->query('SELECT COUNT(*) FROM private_messages')->fetchColumn(),
    ];
    $users = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC LIMIT 30')->fetchAll();
    $messages = $pdo->query('SELECT id, conversation_key, sender_pseudo, content, status, created_at FROM private_messages ORDER BY id DESC LIMIT 30')->fetchAll();
} else {
    $stmt = $pdo->prepare('SELECT id, title, content, category, tier, is_published, created_at FROM motivation_posts WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$user['id']]);
    $myPosts = $stmt->fetchAll();
}

require_once __DIR__ . '/_top.php';
?>

<?php if ($view === 'users'): ?>
<section class="hero page-hero">
  <h1>Espace Administration - SafeSpace</h1>
  <p class="muted">Vue centralisée des comptes utilisateurs, rôles administratifs et messages privés du système.</p>
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
    <span class="muted">Messages privés</span>
    <strong class="metric-number"><?= $stats['messages_total'] ?></strong>
  </article>
  <article class="metric-card">
    <span class="muted">Activité récente</span>
    <strong class="metric-number"><?= count($users) + count($messages) ?></strong>
  </article>
</section>

<section class="card soft-card" style="margin-top:1rem; overflow:auto;">
  <h2>Utilisateurs récents</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Création</th>
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
  <h2>Messages privés récents</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Conversation</th>
        <th>Expéditeur</th>
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

<?php else: ?>
<section class="hero page-hero">
  <h1>Espace Administration - SafeSpace</h1>
  <p class="muted">Publiez des messages de prévention, des conseils de sécurité ou des annonces système pour la communauté.</p>
</section>

<?php if ($message !== ''): ?>
  <div class="alert"><?= e($message) ?></div>
<?php endif; ?>

<section class="card soft-card" style="margin-top:1rem;">
  <h2>Créer une nouvelle publication anonyme</h2>
  <form method="post">
    <input type="hidden" name="action" value="publish" />
    <div class="field">
      <label>Titre de la publication</label>
      <input name="title" required placeholder="Ex: Conseils pour gérer l'anxiété au quotidien" />
    </div>
    <div class="field">
      <label>Catégorie</label>
      <input name="category" placeholder="Ex: conseil, alerte, ressources..." />
    </div>
    <div class="field">
      <label>Contenu de la publication (prévention, informations, etc.)</label>
      <textarea name="content" required placeholder="Écrivez votre message de prévention ou annonce système ici..."></textarea>
    </div>
    <div class="field">
      <label>Niveau d'accès</label>
      <select name="tier">
        <option value="free">Standard (Gratuit)</option>
        <option value="premium">Premium (Réservé aux membres Premium)</option>
      </select>
    </div>
    <div class="field">
      <label>Statut de publication</label>
      <select name="visibility">
        <option value="draft">Brouillon (invisible)</option>
        <option value="published">Publier immédiatement</option>
      </select>
    </div>
    <button class="btn" type="submit">Enregistrer et publier</button>
  </form>
</section>

<section class="post-list" style="margin-top:2rem;">
  <h2>Vos publications administratives récentes</h2>
  <?php if (!$myPosts): ?>
    <article class="card soft-card"><p class="muted">Aucune publication pour le moment.</p></article>
  <?php else: ?>
    <?php foreach ($myPosts as $p): ?>
      <article class="card soft-card" style="margin-bottom:1rem;">
        <h3>
          <?= e($p['title']) ?> 
          <span class="chip"><?= e($p['tier']) ?></span> 
          <span class="chip"><?= (int)$p['is_published'] === 1 ? 'publié' : 'brouillon' ?></span>
        </h3>
        <p><?= nl2br(e($p['content'])) ?></p>
        <p class="muted"><small>Créé le: <?= e($p['created_at']) ?></small></p>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/_bottom.php'; ?>
