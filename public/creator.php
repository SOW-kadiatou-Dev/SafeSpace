<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
requireAdmin();
require_once __DIR__ . '/_top.php';

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
            $message = $isPublished ? 'Publication en ligne.' : 'Brouillon sauvegarde.';
        }
    }
}

$stmt = $pdo->prepare('SELECT id, title, content, category, tier, is_published, created_at FROM motivation_posts WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$myPosts = $stmt->fetchAll();
?>
<section class="hero page-hero"><h1>Creator Studio</h1><p class="muted">Espace prive admin pour gerer tes contenus motivation.</p></section>
<?php if ($message !== ''): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>
<section class="card soft-card" style="margin-top:1rem;">
  <h2>Nouvelle publication motivation</h2>
  <form method="post">
    <input type="hidden" name="action" value="publish" />
    <div class="field"><label>Titre</label><input name="title" required /></div>
    <div class="field"><label>Categorie</label><input name="category" placeholder="motivation, stress, etudes..." /></div>
    <div class="field"><label>Contenu</label><textarea name="content" required></textarea></div>
    <div class="field"><label>Niveau</label>
      <select name="tier"><option value="free">Free</option><option value="premium">Premium</option></select>
    </div>
    <div class="field"><label>Statut</label>
      <select name="visibility"><option value="draft">Brouillon (non visible)</option><option value="published">Publier maintenant</option></select>
    </div>
    <button class="btn" type="submit">Enregistrer</button>
  </form>
</section>
<section class="post-list">
  <?php foreach ($myPosts as $p): ?>
    <article class="card">
      <h3><?= e($p['title']) ?> <span class="chip"><?= e($p['tier']) ?></span> <span class="chip"><?= (int)$p['is_published'] === 1 ? 'publie' : 'brouillon' ?></span></h3>
      <p><?= nl2br(e($p['content'])) ?></p>
    </article>
  <?php endforeach; ?>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
