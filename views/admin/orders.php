<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Manage Orders — Lockinrank Control Panel') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">

    <?php 
    $activeTab = 'orders';
    include __DIR__ . '/layout-nav.php'; 
    ?>

    <main class="container" style="max-width: 1300px; padding: 2rem 1.5rem;">
        
        <!-- Header & Action -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 1.7rem; font-weight: 900; margin: 0 0 0.25rem 0; color: #0f172a;">
                    Orders & Transaction Records
                </h1>
                <p style="color: #64748b; margin: 0; font-size: 0.92rem;">
                    Full transaction log, payment settlement status, and automated token delivery audit (<?= number_format($totalOrders) ?> total orders)
                </p>
            </div>
            <div>
                <a href="/admin/customers" role="button" style="background-color: #ffffff; border: 1px solid #cbd5e1; color: #0f172a !important; font-weight: 700; font-size: 0.9rem; padding: 0.55rem 1.25rem; display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 8px; text-decoration: none;">
                    <i class="fa-solid fa-users"></i> View All Customers
                </a>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 1.5rem;">
            <form method="GET" action="/admin/orders" style="margin: 0; display: grid; grid-template-columns: 3fr 1.5fr auto auto; gap: 0.75rem; align-items: center;">
                <div>
                    <input type="search" name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search by Order #, Customer Name, Email, or WhatsApp..." style="margin-bottom: 0; font-size: 0.88rem;">
                </div>
                <div>
                    <select name="status" style="margin-bottom: 0; font-size: 0.88rem;">
                        <option value="">All Payment Statuses</option>
                        <option value="paid" <?= ($statusFilter === 'paid') ? 'selected' : '' ?>>Paid (Completed)</option>
                        <option value="pending" <?= ($statusFilter === 'pending') ? 'selected' : '' ?>>Pending Payment</option>
                        <option value="failed" <?= ($statusFilter === 'failed') ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
                <div>
                    <button type="submit" style="background-color: #0f172a; border-color: #0f172a; color: #fff; margin-bottom: 0; font-size: 0.88rem; padding: 0.55rem 1.2rem; font-weight: 700;">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
                <?php if (!empty($search) || !empty($statusFilter)): ?>
                    <div>
                        <a href="/admin/orders" role="button" style="background-color: #f1f5f9; color: #475569 !important; border-color: #cbd5e1; margin-bottom: 0; font-size: 0.88rem; padding: 0.55rem 0.9rem;">
                            Reset
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Orders Table -->
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 1.5rem;">
            <?php if (empty($orders)): ?>
                <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                    <i class="fa-solid fa-receipt" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 0.75rem;"></i>
                    <p style="margin: 0; font-size: 1rem;">No orders found matching the specified search or filter.</p>
                    <a href="/admin/orders" style="margin-top: 0.75rem; display: inline-block; font-size: 0.88rem; color: #4f46e5; font-weight: 700;">Clear Filters</a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="font-size: 0.88rem; width: 100%;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 0.75rem 0.5rem;">Order # &amp; Date</th>
                                <th style="padding: 0.75rem 0.5rem;">Customer Details</th>
                                <th style="padding: 0.75rem 0.5rem;">Payment Gateway &amp; Ref</th>
                                <th style="padding: 0.75rem 0.5rem;">Amount</th>
                                <th style="padding: 0.75rem 0.5rem;">Status</th>
                                <th style="padding: 0.75rem 0.5rem; text-align: right;">Customer Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $ord): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.85rem 0.5rem;">
                                        <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">
                                            <?= htmlspecialchars($ord['order_number']) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">
                                            <i class="fa-regular fa-clock"></i> <?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem;">
                                        <div style="font-weight: 700; color: #334155; font-size: 0.92rem;">
                                            <?= htmlspecialchars($ord['buyer_name']) ?>
                                        </div>
                                        <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.15rem;">
                                            <i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($ord['buyer_email']) ?>
                                        </div>
                                        <div style="font-size: 0.78rem; color: #047857; margin-top: 0.1rem;">
                                            <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($ord['buyer_phone'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; font-size: 0.85rem; color: #334155;">
                                        <div><strong><?= htmlspecialchars($ord['payment_method']) ?></strong></div>
                                        <?php if (!empty($ord['razorpay_payment_id'])): ?>
                                            <div style="font-family: monospace; font-size: 0.75rem; color: #475569; margin-top: 0.15rem;">
                                                <?= htmlspecialchars($ord['razorpay_payment_id']) ?>
                                            </div>
                                        <?php elseif (!empty($ord['razorpay_order_id'])): ?>
                                            <div style="font-family: monospace; font-size: 0.75rem; color: #94a3b8; margin-top: 0.15rem;">
                                                <?= htmlspecialchars($ord['razorpay_order_id']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; font-weight: 800; color: #0f172a; font-size: 0.95rem;">
                                        ₹<?= number_format($ord['total_amount'], 0) ?>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem;">
                                        <?php if ($ord['payment_status'] === 'paid'): ?>
                                            <span style="background: #dcfce7; color: #15803d; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                                <i class="fa-solid fa-circle-check"></i> PAID
                                            </span>
                                        <?php elseif ($ord['payment_status'] === 'pending'): ?>
                                            <span style="background: #fef3c7; color: #b45309; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 4px;">
                                                PENDING
                                            </span>
                                        <?php else: ?>
                                            <span style="background: #fee2e2; color: #b91c1c; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 4px;">
                                                <?= strtoupper(htmlspecialchars($ord['payment_status'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; text-align: right; white-space: nowrap;">
                                        <a href="/admin/customer?email=<?= urlencode($ord['buyer_email']) ?>" style="font-size: 0.8rem; padding: 0.35rem 0.65rem; background: #eff6ff; border-radius: 6px; color: #2563eb; text-decoration: none; margin-right: 0.35rem; font-weight: 700;">
                                            <i class="fa-solid fa-user"></i> View Profile
                                        </a>
                                        <a href="/order/success/<?= htmlspecialchars($ord['order_number']) ?>" target="_blank" style="font-size: 0.8rem; padding: 0.35rem 0.65rem; background: #f1f5f9; border-radius: 6px; color: #475569; text-decoration: none;">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Order Page
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination (10 per page) -->
        <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <span style="font-size: 0.88rem; color: #64748b;">
                    Showing Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (<?= number_format($totalOrders) ?> total orders)
                </span>
                <div style="display: flex; gap: 0.5rem;">
                    <?php 
                        $queryParams = $_GET;
                        $prevPageUrl = '?' . http_build_query(array_merge($queryParams, ['page' => max(1, $page - 1)]));
                        $nextPageUrl = '?' . http_build_query(array_merge($queryParams, ['page' => min($totalPages, $page + 1)]));
                    ?>
                    <?php if ($page > 1): ?>
                        <a href="<?= $prevPageUrl ?>" role="button" style="padding: 0.4rem 0.85rem; font-size: 0.85rem; background: #ffffff; color: #0f172a !important; border: 1px solid #cbd5e1; text-decoration: none;">
                            &laquo; Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span style="padding: 0.4rem 0.85rem; font-size: 0.85rem; background: #4f46e5; color: #ffffff; border-radius: 6px; font-weight: 700;">
                                <?= $i ?>
                            </span>
                        <?php elseif ($i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                            <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>" role="button" style="padding: 0.4rem 0.85rem; font-size: 0.85rem; background: #ffffff; color: #0f172a !important; border: 1px solid #cbd5e1; text-decoration: none;">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= $nextPageUrl ?>" role="button" style="padding: 0.4rem 0.85rem; font-size: 0.85rem; background: #ffffff; color: #0f172a !important; border: 1px solid #cbd5e1; text-decoration: none;">
                            Next &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>

</body>
</html>

