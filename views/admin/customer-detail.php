<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Customer Profile — Lockinrank Control Panel') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="/css/app.min.css?v=2.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">

    <?php 
    $activeTab = 'customers';
    include __DIR__ . '/layout-nav.php'; 
    ?>

    <main class="container" style="max-width: 1300px; padding: 2rem 1.5rem;">
        
        <!-- Back Link & Title -->
        <div style="margin-bottom: 1.5rem;">
            <a href="/admin/customers" style="color: #4f46e5; text-decoration: none; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.75rem;">
                <i class="fa-solid fa-arrow-left"></i> Back to All Customers
            </a>
            <h1 style="font-size: 1.8rem; font-weight: 900; margin: 0 0 0.25rem 0; color: #0f172a;">
                Student Profile &amp; Purchased Licenses
            </h1>
            <p style="color: #64748b; margin: 0; font-size: 0.92rem;">
                Review student enrollment details, past transaction receipts, and manage 7-day token expiration
            </p>
        </div>

        <!-- Customer Bio Card & KPI Highlights -->
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 1.25rem;">
                    <div style="width: 64px; height: 64px; background: #e0e7ff; color: #4338ca; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 900;">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1.4rem; font-weight: 800; margin: 0 0 0.25rem 0; color: #0f172a;">
                            <?= htmlspecialchars($customer['buyer_name'] ?: 'Student') ?>
                        </h2>
                        <div style="display: flex; gap: 1.25rem; flex-wrap: wrap; font-size: 0.88rem; color: #475569; margin-top: 0.25rem;">
                            <span><i class="fa-regular fa-envelope" style="color: #4f46e5;"></i> <?= htmlspecialchars($customer['buyer_email']) ?></span>
                            <span><i class="fa-brands fa-whatsapp" style="color: #059669;"></i> <?= htmlspecialchars($customer['buyer_phone'] ?: 'N/A') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Stats summary badges -->
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1.25rem; text-align: center;">
                        <div style="font-size: 0.72rem; color: #64748b; font-weight: 800; text-transform: uppercase;">Lifetime Spend</div>
                        <div style="font-size: 1.35rem; font-weight: 900; color: #10b981;">₹<?= number_format($customer['total_spent'], 0) ?></div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1.25rem; text-align: center;">
                        <div style="font-size: 0.72rem; color: #64748b; font-weight: 800; text-transform: uppercase;">Orders Placed</div>
                        <div style="font-size: 1.35rem; font-weight: 900; color: #0f172a;"><?= number_format($customer['total_orders']) ?></div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1.25rem; text-align: center;">
                        <div style="font-size: 0.72rem; color: #64748b; font-weight: 800; text-transform: uppercase;">First Active</div>
                        <div style="font-size: 0.95rem; font-weight: 700; color: #475569; margin-top: 0.25rem;"><?= date('d M Y', strtotime($customer['first_order_date'])) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 1: Purchased Materials & 7-Day Download Tokens -->
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0;">
                        <i class="fa-solid fa-cloud-arrow-down" style="color: #4f46e5;"></i> Owned Study Packs &amp; Download Tokens
                    </h3>
                    <p style="font-size: 0.82rem; color: #64748b; margin: 0.15rem 0 0 0;">
                        Links expire after 7 days for security. Extend expiry anytime with 1-click.
                    </p>
                </div>
                <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">
                    <?= count($materials) ?> resource<?= count($materials) !== 1 ? 's' : '' ?> unlocked
                </span>
            </div>

            <?php if (empty($materials)): ?>
                <div style="text-align: center; padding: 2rem; color: #64748b;">
                    No materials purchased or downloaded yet.
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="font-size: 0.88rem; width: 100%;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 0.75rem 0.5rem;">Study Resource</th>
                                <th style="padding: 0.75rem 0.5rem;">Order #</th>
                                <th style="padding: 0.75rem 0.5rem;">Download Stats</th>
                                <th style="padding: 0.75rem 0.5rem;">Expiry Date</th>
                                <th style="padding: 0.75rem 0.5rem;">Access Status</th>
                                <th style="padding: 0.75rem 0.5rem; text-align: right;">Token Management</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materials as $m): ?>
                                <?php 
                                    $isExpired = !empty($m['expires_at']) && (strtotime($m['expires_at']) < time());
                                ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.85rem 0.5rem;">
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.92rem;"><?= htmlspecialchars($m['title']) ?></div>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                            Format: <?= htmlspecialchars($m['file_format'] ?? 'PDF') ?> &bull; ₹<?= number_format($m['price'], 0) ?>
                                        </div>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; font-family: monospace; font-size: 0.82rem; color: #475569;">
                                        <?= htmlspecialchars($m['order_number']) ?>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; font-size: 0.85rem; color: #334155;">
                                        <i class="fa-solid fa-download" style="color: #6366f1;"></i> <?= (int)$m['download_count'] ?> time<?= (int)$m['download_count'] !== 1 ? 's' : '' ?>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; font-size: 0.82rem;">
                                        <?php if (!empty($m['expires_at'])): ?>
                                            <div style="font-weight: 600; color: <?= $isExpired ? '#dc2626' : '#15803d' ?>;">
                                                <?= date('d M Y, h:i A', strtotime($m['expires_at'])) ?>
                                            </div>
                                            <div style="font-size: 0.72rem; color: #64748b;">
                                                <?= $isExpired ? 'Expired' : 'Active link' ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #64748b;">No expiry set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem;">
                                        <?php if ($isExpired): ?>
                                            <span style="background: #fee2e2; color: #b91c1c; font-size: 0.72rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                                <i class="fa-solid fa-clock-rotate-left"></i> EXPIRED
                                            </span>
                                        <?php else: ?>
                                            <span style="background: #dcfce7; color: #15803d; font-size: 0.72rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                                <i class="fa-solid fa-circle-check"></i> ACTIVE
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; text-align: right; white-space: nowrap;">
                                        <!-- Test Download Link -->
                                        <a href="/download/<?= htmlspecialchars($m['download_token']) ?>" target="_blank" style="font-size: 0.78rem; padding: 0.3rem 0.6rem; background: #f1f5f9; color: #334155; border-radius: 4px; text-decoration: none; margin-right: 0.35rem; font-weight: 600;">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Test Token
                                        </a>

                                        <!-- 1-Click Renew / Extend Button -->
                                        <form method="POST" action="/admin/token/refresh" style="display: inline; margin: 0;">
                                            <input type="hidden" name="item_id" value="<?= $m['id'] ?>">
                                            <input type="hidden" name="return_url" value="/admin/customer?email=<?= urlencode($customer['buyer_email']) ?>">
                                            <button type="submit" style="background-color: #4f46e5; border-color: #4f46e5; color: #ffffff !important; font-size: 0.78rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 4px; margin-bottom: 0; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem;">
                                                <i class="fa-solid fa-arrows-rotate"></i> Extend +7 Days
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section 2: Chronological Past Orders -->
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0;">
                    <i class="fa-solid fa-receipt" style="color: #10b981;"></i> Chronological Order History
                </h3>
                <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">
                    <?= count($orders) ?> order records
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table style="font-size: 0.88rem; width: 100%;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 0.75rem 0.5rem;">Order Reference</th>
                            <th style="padding: 0.75rem 0.5rem;">Date &amp; Time</th>
                            <th style="padding: 0.75rem 0.5rem;">Payment Method</th>
                            <th style="padding: 0.75rem 0.5rem;">Gateway Reference</th>
                            <th style="padding: 0.75rem 0.5rem;">Amount</th>
                            <th style="padding: 0.75rem 0.5rem;">Status</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $ord): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.75rem 0.5rem; font-weight: 800; color: #0f172a;">
                                    <?= htmlspecialchars($ord['order_number']) ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; font-size: 0.85rem; color: #475569;">
                                    <?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; color: #334155; font-size: 0.85rem;">
                                    <?= htmlspecialchars($ord['payment_method']) ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; font-family: monospace; font-size: 0.78rem; color: #475569;">
                                    <?= htmlspecialchars($ord['razorpay_payment_id'] ?? $ord['razorpay_order_id'] ?? 'N/A') ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; font-weight: 800; color: #0f172a;">
                                    ₹<?= number_format($ord['total_amount'], 0) ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem;">
                                    <?php if ($ord['payment_status'] === 'paid'): ?>
                                        <span style="background: #dcfce7; color: #15803d; font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.45rem; border-radius: 4px;">PAID</span>
                                    <?php else: ?>
                                        <span style="background: #fef3c7; color: #b45309; font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.45rem; border-radius: 4px;"><?= strtoupper(htmlspecialchars($ord['payment_status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; text-align: right;">
                                    <a href="/order/success/<?= htmlspecialchars($ord['order_number']) ?>" target="_blank" style="font-size: 0.78rem; padding: 0.25rem 0.55rem; background: #eff6ff; color: #2563eb; border-radius: 4px; font-weight: 700; text-decoration: none;">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Order Receipt
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>

