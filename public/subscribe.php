<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
requireAuth();
require_once __DIR__ . '/_top.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!cinetpayConfigured()) {
        $message = 'Configuration paiement manquante: renseigne CINETPAY_SITE_ID et CINETPAY_API_KEY.';
    } else {
        $transactionId = 'SS-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $notifyUrl = makeAbsoluteUrl('payment_notify.php');
        $returnUrl = makeAbsoluteUrl('payment_return.php');

        $paymentRow = [
            'user_id' => (int)$user['id'],
            'provider' => PAYMENT_PROVIDER,
            'transaction_id' => $transactionId,
            'amount' => PREMIUM_MONTHLY_FEE_FCFA,
            'currency' => CURRENCY_CODE,
            'status' => 'pending',
            'payment_url' => null,
            'payment_token' => null,
            'provider_response' => null,
            'expires_at' => null,
        ];

        $insert = $pdo->prepare('INSERT INTO payments (user_id, provider, transaction_id, amount, currency, status, payment_url, payment_token, provider_response, expires_at) VALUES (:user_id, :provider, :transaction_id, :amount, :currency, :status, :payment_url, :payment_token, :provider_response, :expires_at)');
        $insert->execute($paymentRow);

        try {
            $response = cinetpayRequest([
                'apikey' => CINETPAY_API_KEY,
                'site_id' => CINETPAY_SITE_ID,
                'transaction_id' => $transactionId,
                'amount' => PREMIUM_MONTHLY_FEE_FCFA,
                'currency' => CURRENCY_CODE,
                'description' => 'Abonnement Premium SafeSpace',
                'return_url' => $returnUrl,
                'notify_url' => $notifyUrl,
                'metadata' => json_encode([
                    'user_id' => (int)$user['id'],
                    'email' => (string)$user['email'],
                    'plan' => 'premium_monthly',
                ], JSON_UNESCAPED_UNICODE),
                'customer_id' => (string)$user['id'],
                'customer_name' => (string)$user['name'],
                'customer_surname' => '',
                'channels' => 'ALL',
                'lang' => 'FR',
            ]);

            $paymentUrl = $response['data']['payment_url'] ?? '';
            $paymentToken = $response['data']['payment_token'] ?? '';
            if ($paymentUrl === '') {
                throw new RuntimeException("CinetPay n'a pas renvoye de lien de paiement.");
            }

            $update = $pdo->prepare('UPDATE payments SET payment_url = ?, payment_token = ?, provider_response = ?, updated_at = CURRENT_TIMESTAMP WHERE transaction_id = ?');
            $update->execute([$paymentUrl, $paymentToken, json_encode($response, JSON_UNESCAPED_UNICODE), $transactionId]);

            header('Location: ' . $paymentUrl);
            exit;
        } catch (Throwable $e) {
            $pdo->prepare('UPDATE payments SET status = ?, provider_response = ?, updated_at = CURRENT_TIMESTAMP WHERE transaction_id = ?')
                ->execute(['failed', json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE), $transactionId]);
            $message = $e->getMessage();
        }
    }
}
?>

<section class="card auth-panel">
  <h1>Paiement Premium</h1>
  <p class="muted">Abonnement mensuel de <?= e(formatCFA(PREMIUM_MONTHLY_FEE_FCFA)) ?>.</p>
  <?php if ($message !== ''): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>
  <p><a class="btn" href="pricing.php">Retour aux offres</a></p>
</section>
<?php require_once __DIR__ . '/_bottom.php'; ?>
