<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Customer CRM & Profiles — Lockinrank Control Panel') ?></title>
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
        
        <!-- Header & Action -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 1.7rem; font-weight: 900; margin: 0 0 0.25rem 0; color: #0f172a;">
                    Enrolled Students &amp; Customer CRM
                </h1>
                <p style="color: #64748b; margin: 0; font-size: 0.92rem;">
                    Manage student contacts, order history, lifetime spend, and download access tokens (<?= number_format($totalCustomers) ?> distinct customers)
                </p>
            </div>
            <div>
                <a href="/admin/orders" role="button" style="background-color: #ffffff; border: 1px solid #cbd5e1; color: #0f172a !important; font-weight: 700; font-size: 0.9rem; padding: 0.55rem 1.25rem; display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 8px; text-decoration: none;">
                    <i class="fa-solid fa-receipt"></i> All Transaction Logs
                </a>
            </div>
        </div>

        <!-- Search Controls -->
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 1.5rem;">
            <form method="GET" action="/admin/customers" style="margin: 0; display: grid; grid-template-columns: 4fr auto auto; gap: 0.75rem; align-items: center;">
                <div>
                    <input type="search" name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search customer by name, email, or WhatsApp mobile..." style="margin-bottom: 0; font-size: 0.88rem;">
                </div>
                <div>
                    <button type="submit" style="background-color: #0f172a; border-color: #0f172a; color: #fff; margin-bottom: 0; font-size: 0.88rem; padding: 0.55rem 1.2rem; font-weight: 700;">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>
                </div>
                <?php if (!empty($search)): ?>
                    <div>
                        <a href="/admin/customers" role="button" style="background-color: #f1f5f9; color: #475569 !important; border-color: #cbd5e1; margin-bottom: 0; font-size: 0.88rem; padding: 0.55rem 0.9rem;">
                            Reset
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Customers Table -->
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 1.5rem;">
            <?php if (empty($customers)): ?>
                <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                    <i class="fa-solid fa-user-slash" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 0.75rem;"></i>
                    <p style="margin: 0; font-size: 1rem;">No customer records match your query.</p>
                    <a href="/admin/customers" style="margin-top: 0.75rem; display: inline-block; font-size: 0.88rem; color: #4f46e5; font-weight: 700;">Clear Search</a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="font-size: 0.88rem; width: 100%;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 0.75rem 0.5rem;">Customer Details</th>
                                <th style="padding: 0.75rem 0.5rem;">Contact & WhatsApp</th>
                                <th style="padding: 0.75rem 0.5rem;">Total Orders</th>
                                <th style="padding: 0.75rem 0.5rem;">Lifetime Spend</th>
                                <th style="padding: 0.75rem 0.5rem;">Last Order Placed</th>
                                <th style="padding: 0.75rem 0.5rem; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $c): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.85rem 0.5rem;">
                                        <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">
                                            <?= htmlspecialchars($c['buyer_name'] ?: 'Student') ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                            First ordered: <?= date('d M Y', strtotime($c['first_order_date'])) ?>
                                        </div>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem;">
                                        <div style="color: #334155; font-size: 0.88rem;">
                                            <i class="fa-regular fa-envelope" style="color: #6366f1;"></i> <?= htmlspecialchars($c['buyer_email']) ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: #059669; margin-top: 0.2rem; font-weight: 600;">
                                            <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($c['buyer_phone'] ?: '-') ?>
                                        </div>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; font-weight: 700; color: #4338ca;">
                                        <?= number_format($c['total_orders']) ?> order<?= $c['total_orders'] != 1 ? 's' : '' ?>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; font-weight: 800; color: #0f172a; font-size: 0.95rem;">
                                        ₹<?= number_format($c['total_spent'], 0) ?>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; font-size: 0.85rem; color: #475569;">
                                        <?= date('d M Y, h:i A', strtotime($c['last_order_date'])) ?>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; text-align: right; white-space: nowrap;">
                                        <a href="/admin/customer?email=<?= urlencode($c['buyer_email']) ?>" style="font-size: 0.82rem; padding: 0.4rem 0.75rem; background: #4f46e5; border-radius: 6px; color: #ffffff !important; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                                            <i class="fa-solid fa-id-card"></i> View Profile &amp; Tokens
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
                    Showing Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (<?= number_format($totalCustomers) ?> unique customers)
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

