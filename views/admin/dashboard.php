<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Dashboard — Lockinrank Shop') ?></title>
    <!-- Pico CSS v2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">

    <!-- Top Admin Navigation Bar -->
    <header style="background: #0f172a; border-bottom: 1px solid #1e293b; padding: 0.75rem 2rem; position: sticky; top: 0; z-index: 50;">
        <div style="max-width: 1300px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <img src="/images/lockinrank-icon.svg" alt="Lockinrank" style="width: 34px; height: 34px; border-radius: 8px; display: block;">
                <div>
                    <span style="font-size: 1.15rem; font-weight: 900; color: #ffffff; letter-spacing: -0.5px;">LOCKINRANK</span>
                    <span style="background: #f59e0b; color: #000000; font-size: 0.65rem; font-weight: 800; padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 0.35rem; text-transform: uppercase;">ADMIN</span>
                    <span style="color: #64748b; font-size: 0.75rem; margin-left: 0.5rem;">shop.lockinrank.com</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="/" target="_blank" style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View Live Store
                </a>
                <span style="color: #334155;">|</span>
                <span style="color: #cbd5e1; font-size: 0.85rem; font-weight: 600;">
                    <i class="fa-solid fa-user-circle" style="color: #818cf8;"></i> <?= htmlspecialchars($_SESSION['admin_user'] ?? 'admin') ?>
                </span>
                <a href="/admin/logout" style="background-color: #ef4444; color: #ffffff !important; font-size: 0.8rem; font-weight: 700; padding: 0.35rem 0.85rem; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                </a>
            </div>
        </div>
    </header>

    <main class="container" style="max-width: 1300px; padding: 2rem 1.5rem;">
        
        <!-- Header Actions -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 1.8rem; font-weight: 900; margin: 0 0 0.25rem 0; color: #0f172a;">
                    Control Center & Resource Management
                </h1>
                <p style="color: #64748b; margin: 0; font-size: 0.92rem;">
                    Direct single-merchant catalog & revenue operations for <strong>shop.lockinrank.com</strong>
                </p>
            </div>
            <div>
                <a href="/admin/upload" role="button" style="background-color: #4f46e5; border-color: #4f46e5; color: #ffffff !important; font-weight: 700; font-size: 0.95rem; padding: 0.65rem 1.25rem; display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 8px; text-decoration: none;">
                    <i class="fa-solid fa-plus"></i> Publish New Material
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards in INR -->
        <div class="grid" style="grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 2.5rem;">
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Net Revenue</span>
                    <i class="fa-solid fa-indian-rupee-sign" style="color: #10b981; font-size: 1rem;"></i>
                </div>
                <div style="font-size: 1.55rem; font-weight: 900; color: #10b981; margin: 0.35rem 0 0.15rem 0;">
                    ₹<?= number_format($totalEarnings, 0) ?>
                </div>
                <small style="color: #64748b; font-size: 0.72rem;">100% Direct Store Revenue</small>
            </div>

            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Paid Sales</span>
                    <i class="fa-solid fa-cart-shopping" style="color: #4f46e5; font-size: 1rem;"></i>
                </div>
                <div style="font-size: 1.55rem; font-weight: 900; color: #0f172a; margin: 0.35rem 0 0.15rem 0;">
                    <?= number_format($totalSales) ?>
                </div>
                <small style="color: #64748b; font-size: 0.72rem;">Paid Resource Orders</small>
            </div>

            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Sample Downloads</span>
                    <i class="fa-solid fa-file-arrow-down" style="color: #6366f1; font-size: 1rem;"></i>
                </div>
                <div style="font-size: 1.55rem; font-weight: 900; color: #4338ca; margin: 0.35rem 0 0.15rem 0;">
                    <?= number_format($totalSampleDownloads ?? 0) ?>
                </div>
                <small style="color: #64748b; font-size: 0.72rem;">Free Preview Downloads</small>
            </div>

            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Published Packs</span>
                    <i class="fa-solid fa-book-open" style="color: #0284c7; font-size: 1rem;"></i>
                </div>
                <div style="font-size: 1.55rem; font-weight: 900; color: #0f172a; margin: 0.35rem 0 0.15rem 0;">
                    <?= count($products) ?>
                </div>
                <small style="color: #64748b; font-size: 0.72rem;">Active Materials</small>
            </div>

            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Rating</span>
                    <i class="fa-solid fa-star" style="color: #f59e0b; font-size: 1rem;"></i>
                </div>
                <div style="font-size: 1.55rem; font-weight: 900; color: #d97706; margin: 0.35rem 0 0.15rem 0;">
                    <?= number_format($store['rating'] ?? 5.0, 1) ?>
                </div>
                <small style="color: #64748b; font-size: 0.72rem;"><?= $store['total_reviews'] ?? 0 ?> student reviews</small>
            </div>
        </div>

        <!-- Products Management Table -->
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 2.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">
                    <i class="fa-solid fa-cubes" style="color: #4f46e5;"></i> Active Course Materials & Resource Catalog
                </h3>
                <span style="font-size: 0.85rem; color: #64748b;"><?= count($products) ?> items listed</span>
            </div>

            <div style="overflow-x: auto;">
                <table style="font-size: 0.88rem; width: 100%;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 0.75rem 0.5rem;">Resource Title</th>
                            <th style="padding: 0.75rem 0.5rem;">Exam & Board</th>
                            <th style="padding: 0.75rem 0.5rem;">Price</th>
                            <th style="padding: 0.75rem 0.5rem;">Sample Downloads</th>
                            <th style="padding: 0.75rem 0.5rem;">Paid Sales</th>
                            <th style="padding: 0.75rem 0.5rem;">Rating</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.85rem 0.5rem;">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.92rem;"><?= htmlspecialchars($p['title']) ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem; display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                        <span>Subject: <strong><?= htmlspecialchars($p['subject']) ?></strong></span>
                                        <span>&bull;</span>
                                        <span><?= $p['page_count'] ?> Pages</span>
                                        <span>&bull;</span>
                                        <span><?= htmlspecialchars($p['file_format']) ?></span>
                                        <span>&bull;</span>
                                        <?php $imgCnt = count(json_decode($p['preview_images'] ?? '[]', true) ?: (!empty($p['thumbnail_url']) ? [1] : [])); ?>
                                        <span style="background: #e0e7ff; color: #3730a3; padding: 0.05rem 0.4rem; border-radius: 4px; font-weight: 700; font-size: 0.7rem;">
                                            <i class="fa-solid fa-images" style="color: #4f46e5;"></i> <?= $imgCnt ?> image<?= $imgCnt !== 1 ? 's' : '' ?>
                                        </span>
                                        <?php if (!empty($p['material_file_path'])): ?>
                                            <span style="background: #ecfdf5; color: #047857; padding: 0.05rem 0.4rem; border-radius: 4px; font-weight: 700; font-size: 0.7rem;">
                                                <i class="fa-solid fa-file-circle-check"></i> Material Ready
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($p['sample_file_path'])): ?>
                                            <span style="background: #eff6ff; color: #1d4ed8; padding: 0.05rem 0.4rem; border-radius: 4px; font-weight: 700; font-size: 0.7rem;">
                                                <i class="fa-solid fa-file-lines"></i> Sample Ready
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="padding: 0.85rem 0.5rem;">
                                    <span class="chip" style="background: #4f46e5; color: #ffffff;"><?= htmlspecialchars($p['grade']) ?></span>
                                    <span class="chip" style="background: #e0e7ff; color: #3730a3;"><?= htmlspecialchars($p['board']) ?></span>
                                </td>
                                <td style="padding: 0.85rem 0.5rem; font-weight: 800;">
                                    <?php if ($p['is_free']): ?>
                                        <span style="color: #16a34a;">FREE (₹0)</span>
                                    <?php else: ?>
                                        <span style="color: #0f172a;">₹<?= number_format($p['price'], 0) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.85rem 0.5rem; font-weight: 700; color: #4338ca;">
                                    <?php if ($p['is_free']): ?>
                                        <span style="color: #94a3b8; font-size: 0.78rem;">100% Freebie</span>
                                    <?php else: ?>
                                        <i class="fa-solid fa-file-arrow-down" style="color: #818cf8;"></i> <?= number_format($p['sample_downloads'] ?? 0) ?>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.85rem 0.5rem; font-weight: 700; color: #0f172a;">
                                    <?= number_format($p['sales_count']) ?>
                                </td>
                                <td style="padding: 0.85rem 0.5rem; color: #d97706; font-weight: 700;">
                                    <i class="fa-solid fa-star"></i> <?= number_format($p['rating'], 1) ?> (<?= $p['review_count'] ?>)
                                </td>
                                <td style="padding: 0.85rem 0.5rem; text-align: right; white-space: nowrap;">
                                    <a href="/admin/product/edit/<?= $p['id'] ?>" style="font-size: 0.8rem; padding: 0.35rem 0.65rem; background: #eef2ff; border-radius: 6px; color: #4f46e5; text-decoration: none; margin-right: 0.35rem; font-weight: 700;">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    <a href="/product/<?= htmlspecialchars($p['slug']) ?>" target="_blank" style="font-size: 0.8rem; padding: 0.35rem 0.65rem; background: #f1f5f9; border-radius: 6px; color: #334155; text-decoration: none; margin-right: 0.35rem;">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                    <a href="/admin/product/delete/<?= $p['id'] ?>" onclick="return confirm('Are you sure you want to delete this resource?')" style="font-size: 0.8rem; padding: 0.35rem 0.65rem; background: #fee2e2; border-radius: 6px; color: #dc2626; text-decoration: none;">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Customer Orders Table -->
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">
                    <i class="fa-solid fa-receipt" style="color: #10b981;"></i> Recent Orders & Payment Settlements
                </h3>
                <span style="font-size: 0.85rem; color: #64748b;">Instant Fulfillment Logs</span>
            </div>

            <?php if (empty($recentOrders)): ?>
                <div style="text-align: center; padding: 2.5rem 1rem; color: #64748b;">
                    <i class="fa-solid fa-box-open" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 0.75rem;"></i>
                    <p style="margin: 0;">No orders recorded yet. As students purchase or download freebies, receipts will display here.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="font-size: 0.88rem; width: 100%;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 0.75rem 0.5rem;">Order #</th>
                                <th style="padding: 0.75rem 0.5rem;">Customer Name</th>
                                <th style="padding: 0.75rem 0.5rem;">Contact & WhatsApp</th>
                                <th style="padding: 0.75rem 0.5rem;">Payment Method</th>
                                <th style="padding: 0.75rem 0.5rem;">Gateway Ref</th>
                                <th style="padding: 0.75rem 0.5rem;">Amount</th>
                                <th style="padding: 0.75rem 0.5rem;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $ord): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.75rem 0.5rem; font-weight: 800; color: #0f172a;">
                                        <?= htmlspecialchars($ord['order_number']) ?>
                                    </td>
                                    <td style="padding: 0.75rem 0.5rem; font-weight: 600; color: #334155;">
                                        <?= htmlspecialchars($ord['buyer_name']) ?>
                                    </td>
                                    <td style="padding: 0.75rem 0.5rem; font-size: 0.82rem; color: #64748b;">
                                        <div><?= htmlspecialchars($ord['buyer_email']) ?></div>
                                        <div><?= htmlspecialchars($ord['buyer_phone'] ?? '-') ?></div>
                                    </td>
                                    <td style="padding: 0.75rem 0.5rem; color: #334155;">
                                        <?= htmlspecialchars($ord['payment_method']) ?>
                                    </td>
                                    <td style="padding: 0.75rem 0.5rem; font-size: 0.8rem; font-family: monospace; color: #475569;">
                                        <?= htmlspecialchars($ord['razorpay_payment_id'] ?? 'N/A') ?>
                                    </td>
                                    <td style="padding: 0.75rem 0.5rem; font-weight: 800; color: #0f172a;">
                                        ₹<?= number_format($ord['total_amount'], 0) ?>
                                    </td>
                                    <td style="padding: 0.75rem 0.5rem;">
                                        <?php if ($ord['payment_status'] === 'paid'): ?>
                                            <span style="background: #dcfce7; color: #15803d; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 4px;">
                                                <i class="fa-solid fa-circle-check"></i> PAID
                                            </span>
                                        <?php else: ?>
                                            <span style="background: #fef3c7; color: #b45309; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 4px;">
                                                <?= htmlspecialchars(strtoupper($ord['payment_status'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>

</body>
</html>
