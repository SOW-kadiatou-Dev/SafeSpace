<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_top.php';

$postColumns = $pdo->query('PRAGMA table_info(posts)')->fetchAll();
$postColumnNames = [];
foreach ($postColumns as $column) {
    $name = (string)($column['name'] ?? '');
    if ($name !== '') {
        $postColumnNames[$name] = true;
    }
}

$hasCommentsTable = false;
$hasCommentStatus = false;
$commentsTableCheck = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='comments'")->fetchColumn();
if ($commentsTableCheck) {
    $hasCommentsTable = true;
    $commentColumns = $pdo->query('PRAGMA table_info(comments)')->fetchAll();
    foreach ($commentColumns as $column) {
        if ((string)($column['name'] ?? '') === 'status') {
            $hasCommentStatus = true;
            break;
        }
    }
}

$pseudoExpr = isset($postColumnNames['pseudo']) ? 'p.pseudo' : "'Anonyme'";
$contentExpr = isset($postColumnNames['content']) ? 'p.content' : "''";
$moodExpr = isset($postColumnNames['mood']) ? 'p.mood' : 'NULL';
$createdExpr = isset($postColumnNames['created_at']) ? 'p.created_at' : 'CURRENT_TIMESTAMP';
$statusFilter = isset($postColumnNames['status']) ? "WHERE p.status='published'" : '';
$orderBy = isset($postColumnNames['created_at']) ? 'ORDER BY p.created_at DESC' : 'ORDER BY p.id DESC';

$commentCountExpr = '0';
if ($hasCommentsTable) {
    $commentCountExpr = $hasCommentStatus
        ? "(SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id AND c.status='published')"
        : '(SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id)';
}

$sql = "SELECT p.id, {$pseudoExpr} AS pseudo, {$contentExpr} AS content, {$moodExpr} AS mood, {$createdExpr} AS created_at, {$commentCountExpr} AS comment_count
        FROM posts p
        {$statusFilter}
        {$orderBy}";

$stmt = $pdo->query($sql);
$posts = $stmt ? $stmt->fetchAll() : [];
?>
<section class="hero page-hero">
  <h1>Supportive Community</h1>
  <p class="muted">Publications anonymes de la communauté.</p>
</section>
<section class="post-list">
  <?php if (!$posts): ?>
    <article class="card soft-card"><p>Aucune publication pour le moment.</p></article>
  <?php endif; ?>

  <?php foreach ($posts as $post): ?>
    <article class="card soft-card">
      <div class="row">
        <strong><?= e($post['pseudo']) ?></strong>
        <?php if (!empty($post['mood'])): ?><span class="chip"><?= e($post['mood']) ?></span><?php endif; ?>
      </div>
      <p><?= nl2br(e($post['content'])) ?></p>
      <div class="row">
        <a href="post.php?id=<?= (int)$post['id'] ?>">Voir et commenter</a>
        <span class="muted"><?= (int)$post['comment_count'] ?> commentaire(s)</span>
      </div>
    </article>
  <?php endforeach; ?>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
