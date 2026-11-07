<?php 
$pageTitle = "Order Confirmed — Lockinrank Resources Ready!";
include __DIR__ . '/layout-header.php'; 
?>

<div style="max-width: 750px; margin: 0 auto; text-align: center; padding: 2rem 0;">

    <div style="width: 70px; height: 70px; background-color: rgba(16, 185, 129, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
        <i class="fa-solid fa-circle-check" style="font-size: 2.5rem; color: #10b981;"></i>
    </div>

    <h2 style="font-weight: 800; margin-bottom: 0.5rem; color: #0f172a;">Payment Successful, <?= htmlspecialchars($buyerName) ?>!</h2>
    <p style="color: #64748b; font-size: 1.05rem; margin-bottom: 1.5rem;">
        Your order <strong>#<?= htmlspecialchars($orderNumber) ?></strong> (₹<?= number_format($total, 0) ?> via <?= htmlspecialchars($paymentMethod) ?>) is confirmed.
    </p>

    <!-- Razorpay Payment Reference Badge (if paid) -->
    <?php if (!empty($razorpayPaymentId)): ?>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; background-color: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.35rem 1rem; border-radius: 6px; font-size: 0.85rem; color: #334155; margin-bottom: 1rem;">
            <i class="fa-solid fa-receipt" style="color: #4f46e5;"></i>
            <span>Razorpay Payment ID: <strong><?= htmlspecialchars($razorpayPaymentId) ?></strong></span>
        </div>
        <br>
    <?php endif; ?>

    <!-- WhatsApp & Email Delivery Notifications -->
    <div style="display: flex; flex-direction: column; align-items: center; gap: 0.85rem; margin-bottom: 2rem;">
        
        <!-- 1-Click WhatsApp Instant Delivery Button -->
        <?php if (!empty($whatsappUrl)): ?>
            <a href="<?= htmlspecialchars($whatsappUrl) ?>" 
               target="_blank" 
               rel="noopener noreferrer" 
               role="button" 
               style="background-color: #25d366; border-color: #25d366; color: #ffffff !important; font-weight: 800; font-size: 1rem; padding: 0.85rem 1.6rem; border-radius: 50px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.65rem; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.35); transition: transform 0.15s ease;">
                <i class="fa-brands fa-whatsapp" style="font-size: 1.4rem;"></i>
                <span>Send Links to My WhatsApp (<?= htmlspecialchars($buyerPhone) ?>)</span>
            </a>
            <div style="font-size: 0.8rem; color: #64748b;">
                <i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> 1-Tap instant access &bull; Keeps your download links saved directly in WhatsApp
            </div>
        <?php endif; ?>

        <!-- Email Receipt Confirmed Badge -->
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; background-color: #f8fafc; border: 1px solid #e2e8f0; color: #475569; padding: 0.45rem 1rem; border-radius: 50px; font-size: 0.82rem;">
            <i class="fa-regular fa-envelope" style="color: #4f46e5;"></i>
            <span>Receipt &amp; secure download links sent to <strong><?= htmlspecialchars($buyerEmail) ?></strong></span>
        </div>

    </div>

    <!-- Downloadable Items Box -->
    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; text-align: left; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <h4 style="margin-bottom: 1rem; font-size: 1.15rem; display: flex; align-items: center; gap: 0.5rem; color: #0f172a;">
            <i class="fa-solid fa-cloud-arrow-down" style="color: #4f46e5;"></i> Your Downloadable Study Materials
        </h4>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($downloads as $d): ?>
                <?php 
                    $format = $d['file_format'] ?? $d['format'] ?? 'PDF';
                    $token = $d['download_token'] ?? $d['token'] ?? '';
                ?>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                    <div>
                        <strong style="font-size: 1rem; color: #0f172a;"><?= htmlspecialchars($d['title']) ?></strong>
                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.2rem;">
                            Format: <?= htmlspecialchars($format) ?> &bull; Single-student license
                            <?php if (!empty($d['expires_at'])): ?>
                                &bull; <span style="color: #b45309; font-weight: 700;"><i class="fa-regular fa-clock"></i> Link expires: <?= date('d M Y, h:i A', strtotime($d['expires_at'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <a href="/download/<?= htmlspecialchars($token) ?>" role="button" style="font-size: 0.85rem; padding: 0.45rem 1rem; margin-bottom: 0; background-color: #10b981; border-color: #10b981; color: #ffffff !important; font-weight: 700; text-decoration: none;">
                            <i class="fa-solid fa-download"></i> Download PDF
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display: flex; justify-content: center; gap: 1rem;">
        <a href="/" role="button" style="background-color: #ffffff; color: #0f172a !important; border: 1px solid #cbd5e1; font-weight: 600; text-decoration: none;">
            Explore More Resources
        </a>
        <a href="/admin/dashboard" role="button" style="background-color: #0284c7; color: #ffffff !important; border: 1px solid #0284c7; font-weight: 600; text-decoration: none;">
            Store Admin Dashboard
        </a>
    </div>

</div>

<?php include __DIR__ . '/layout-footer.php'; ?>
