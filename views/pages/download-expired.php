<?php 
$pageTitle = "Download Link Expired — Lockinrank Shop";
include __DIR__ . '/../layout-header.php'; 

$supportPhone = "919990253219"; // Official Lockinrank WhatsApp Support Desk
$msg = "Hello Lockinrank Support! My download link for Order #{$orderNumber} ({$title}) has expired. Could you please extend my download link? Customer: {$buyerName} ({$buyerEmail})";
$waUrl = "https://wa.me/" . $supportPhone . "?text=" . rawurlencode($msg);
?>

<div style="max-width: 680px; margin: 3rem auto; padding: 2rem; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center;">

    <!-- Expired Warning Icon -->
    <div style="width: 72px; height: 72px; background-color: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
        <i class="fa-solid fa-clock-rotate-left" style="font-size: 2.2rem; color: #d97706;"></i>
    </div>

    <h2 style="font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">
        This Download Link Has Expired
    </h2>
    <p style="color: #64748b; font-size: 1rem; margin-bottom: 1.75rem; line-height: 1.5;">
        For digital asset copyright protection and server security, direct download links remain active for <strong>7 days</strong> after purchase.
    </p>

    <!-- Details Box -->
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem 1.5rem; text-align: left; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem;">
            <span style="color: #64748b;">Study Resource:</span>
            <strong style="color: #0f172a; text-align: right; max-width: 65%;"><?= htmlspecialchars($title) ?></strong>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem;">
            <span style="color: #64748b;">Order Reference:</span>
            <strong style="color: #4f46e5; font-family: monospace;">#<?= htmlspecialchars($orderNumber) ?></strong>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem;">
            <span style="color: #64748b;">Link Expired On:</span>
            <span style="color: #dc2626; font-weight: 700;"><?= date('d M Y, h:i A', strtotime($expiredAt)) ?></span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.9rem;">
            <span style="color: #64748b;">Registered Email:</span>
            <span style="color: #334155; font-weight: 600;"><?= htmlspecialchars($buyerEmail) ?></span>
        </div>
    </div>

    <!-- Helpful Tips & Renewal Options -->
    <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 1.25rem; text-align: left; margin-bottom: 2rem;">
        <h4 style="color: #1e40af; font-size: 0.98rem; font-weight: 800; margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-circle-info"></i> How to re-access your study material
        </h4>
        <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.88rem; color: #1e3a8a; line-height: 1.6;">
            <li><strong>Already downloaded?</strong> Check your browser's download folder or device storage for the PDF file.</li>
            <li><strong>Need a fresh link?</strong> Tap the button below to message our academic support desk on WhatsApp. We will instantly extend your access for another 7 days at no charge.</li>
        </ul>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; flex-direction: column; gap: 0.75rem; align-items: center;">
        <a href="<?= htmlspecialchars($waUrl) ?>" 
           target="_blank" 
           rel="noopener noreferrer" 
           role="button" 
           style="background-color: #25d366; border-color: #25d366; color: #ffffff !important; font-weight: 800; font-size: 1rem; padding: 0.85rem 1.75rem; border-radius: 50px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.65rem; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.35);">
            <i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i>
            <span>Request 7-Day Link Extension on WhatsApp</span>
        </a>

        <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
            <a href="/contact" role="button" style="background-color: #f1f5f9; color: #334155 !important; border: 1px solid #cbd5e1; font-size: 0.88rem; font-weight: 600; text-decoration: none;">
                <i class="fa-regular fa-envelope"></i> Email Support
            </a>
            <a href="/" role="button" style="background-color: #ffffff; color: #4f46e5 !important; border: 1px solid #c7d2fe; font-size: 0.88rem; font-weight: 600; text-decoration: none;">
                <i class="fa-solid fa-store"></i> Return to Store
            </a>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../layout-footer.php'; ?>

