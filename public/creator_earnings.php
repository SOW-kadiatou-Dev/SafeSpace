<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
requireAdmin();
require_once __DIR__ . '/_top.php';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM motivation_posts WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalPosts = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM motivation_posts WHERE user_id = ? AND tier='premium' AND is_published=1");
$stmt->execute([$user['id']]);
$premiumPosts = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM motivation_posts WHERE user_id = ? AND tier='free' AND is_published=1");
$stmt->execute([$user['id']]);
$freePosts = (int)$stmt->fetchColumn();

$estimatedSubscribers = max(0, $premiumPosts * 8 + (int)floor($freePosts * 1.4));
$price = PREMIUM_MONTHLY_FEE_FCFA;
$grossRevenue = round($estimatedSubscribers * $price, 2);
$platformFee = round($grossRevenue * 0.12, 2);
$netRevenue = round($grossRevenue - $platformFee, 2);
$conversion = $totalPosts > 0 ? round(($premiumPosts / $totalPosts) * 100, 1) : 0;
?>
<section class="hero page-hero">
  <h1>Creator Earnings</h1>
  <p class="muted">Simulation de revenus basee sur tes contenus publies.</p>
</section>
<section class="metrics-row" style="margin-top:1rem;">
  <article class="metric-card"><span class="metric-number"><?= $totalPosts ?></span><span>Posts total</span></article>
  <article class="metric-card"><span class="metric-number"><?= $premiumPosts ?></span><span>Premium publies</span></article>
  <article class="metric-card"><span class="metric-number"><?= $estimatedSubscribers ?></span><span>Subs estimes</span></article>
  <article class="metric-card"><span class="metric-number"><?= $conversion ?>%</span><span>Taux premium</span></article>
</section>
<section class="grid grid-3" style="margin-top:1rem;">
  <article class="card soft-card"><h3>Revenu brut</h3><p class="money"><?= formatCFA($grossRevenue) ?></p></article>
  <article class="card soft-card"><h3>Frais plateforme</h3><p class="money">-<?= formatCFA($platformFee) ?></p></article>
  <article class="card soft-card"><h3>Revenu net estime</h3><p class="money"><?= formatCFA($netRevenue) ?></p></article>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
