<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Dashboard — Lockinrank Shop') ?></title>
    <!-- Pico CSS v2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="/css/app.min.css?v=2.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">

    <?php 
    $activeTab = 'dashboard';
    include __DIR__ . '/layout-nav.php'; 
    ?>

    <main class="container" style="max-width: 1300px; padding: 2rem 1.5rem;">
        
        <!-- Header Actions -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 1.8rem; font-weight: 900; margin: 0 0 0.25rem 0; color: #0f172a;">
                    Control Center Overview
                </h1>
                <p style="color: #64748b; margin: 0; font-size: 0.92rem;">
                    Store health, sales volume, and quick navigation for <strong>shop.lockinrank.com</strong>
                </p>
            </div>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="/admin/products" role="button" style="background-color: #ffffff; border: 1px solid #cbd5e1; color: #0f172a !important; font-weight: 700; font-size: 0.88rem; padding: 0.55rem 1rem; border-radius: 8px; text-decoration: none;">
                    <i class="fa-solid fa-cubes"></i> Manage Products
                </a>
                <a href="/admin/orders" role="button" style="background-color: #ffffff; border: 1px solid #cbd5e1; color: #0f172a !important; font-weight: 700; font-size: 0.88rem; padding: 0.55rem 1rem; border-radius: 8px; text-decoration: none;">
                    <i class="fa-solid fa-receipt"></i> View Orders
                </a>
                <a href="/admin/upload" role="button" style="background-color: #4f46e5; border-color: #4f46e5; color: #ffffff !important; font-weight: 700; font-size: 0.88rem; padding: 0.55rem 1.25rem; display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 8px; text-decoration: none;">
                    <i class="fa-solid fa-plus"></i> Publish Material
                </a>
            </div>
        </div>

        <!-- Default Password Security Warning Banner -->
        <?php if (!empty($isDefaultPassword)): ?>
            <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1.5px solid #f59e0b; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.08);">
                <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: #f59e0b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 0.95rem; color: #92400e;">
                            Action Recommended: Default Password in Use
                        </div>
                        <div style="font-size: 0.82rem; color: #b45309;">
                            Your admin portal is currently using the factory default password (<code>admin123</code>). Change it now to secure your store.
                        </div>
                    </div>
                </div>
                <div>
                    <a href="/admin/settings" role="button" style="background-color: #d97706; border-color: #d97706; color: #ffffff !important; font-weight: 700; font-size: 0.85rem; padding: 0.5rem 1.15rem; display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 6px; text-decoration: none; margin-bottom: 0;">
                        <i class="fa-solid fa-key"></i> Change Password Now
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Metric Stat Cards in INR -->
        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Net Revenue</span>
                    <i class="fa-solid fa-indian-rupee-sign" style="color: #10b981; font-size: 1rem;"></i>
                </div>
                <div style="font-size: 1.6rem; font-weight: 900; color: #10b981; margin: 0.35rem 0 0.15rem 0;">
                    ₹<?= number_format($totalEarnings, 0) ?>
                </div>
                <small style="color: #64748b; font-size: 0.72rem;">100% Direct Store Revenue</small>
            </div>

            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Paid Orders</span>
                    <i class="fa-solid fa-cart-shopping" style="color: #4f46e5; font-size: 1rem;"></i>
                </div>
                <div style="font-size: 1.6rem; font-weight: 900; color: #0f172a; margin: 0.35rem 0 0.15rem 0;">
                    <?= number_format($totalSales) ?>
                </div>
                <small style="color: #64748b; font-size: 0.72rem;">Delivered Study Packs</small>
            </div>

            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Freebie Downloads</span>
                    <i class="fa-solid fa-file-arrow-down" style="color: #6366f1; font-size: 1rem;"></i>
                </div>
                <div style="font-size: 1.6rem; font-weight: 900; color: #4338ca; margin: 0.35rem 0 0.15rem 0;">
                    <?= number_format($totalSampleDownloads ?? 0) ?>
                </div>
                <small style="color: #64748b; font-size: 0.72rem;">Preview & Free Downloads</small>
            </div>

            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Published Catalog</span>
                    <i class="fa-solid fa-book-open" style="color: #0284c7; font-size: 1rem;"></i>
                </div>
                <div style="font-size: 1.6rem; font-weight: 900; color: #0f172a; margin: 0.35rem 0 0.15rem 0;">
                    <?= count($products) ?>
                </div>
                <small style="color: #64748b; font-size: 0.72rem;">Active Materials</small>
            </div>

            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em;">Unique Customers</span>
                    <i class="fa-solid fa-users" style="color: #f59e0b; font-size: 1rem;"></i>
                </div>
                <div style="font-size: 1.6rem; font-weight: 900; color: #d97706; margin: 0.35rem 0 0.15rem 0;">
                    <?= number_format($totalCustomers ?? 0) ?>
                </div>
                <small style="color: #64748b; font-size: 0.72rem;">Enrolled Students/Parents</small>
            </div>
        </div>

        <!-- Google Merchant Center Integration Card -->
        <?php
        $adminSeo = file_exists(__DIR__ . '/../../config/seo.php') ? require __DIR__ . '/../../config/seo.php' : [];
        $feedDomain = rtrim($adminSeo['base_url'] ?? 'https://shop.lockinrank.com', '/');
        $feedLiveUrl = $feedDomain . '/feed/google-merchant.xml';
        ?>
        <div style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 1px solid #bfdbfe; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 6px rgba(59, 130, 246, 0.06);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="background-color: #3b82f6; color: #ffffff; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                        <i class="fa-brands fa-google"></i>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <h3 style="font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0;">Google Merchant Center &amp; Shopping Feed</h3>
                            <span style="background-color: #dcfce7; color: #15803d; font-size: 0.72rem; font-weight: 700; padding: 0.15rem 0.55rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <span style="width: 7px; height: 7px; background-color: #22c55e; border-radius: 50%; display: inline-block;"></span> Live RSS 2.0
                            </span>
                        </div>
                        <p style="font-size: 0.82rem; color: #64748b; margin: 0.2rem 0 0 0;">
                            Sync your digital study catalog with Google Shopping, Free Product Listings, and Search automatically.
                        </p>
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="/feed/google-merchant.xml" target="_blank" role="button" style="background-color: #ffffff; border: 1px solid #cbd5e1; color: #1e293b !important; font-size: 0.82rem; font-weight: 700; padding: 0.45rem 0.85rem; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Preview XML
                    </a>
                </div>
            </div>

            <!-- Feed URL Bar with Copy Button -->
            <div style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.5rem 0.75rem; display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <span style="font-size: 0.78rem; font-weight: 800; color: #475569; text-transform: uppercase; white-space: nowrap;">Feed URL:</span>
                <input type="text" id="gmcFeedUrlInput" value="<?= htmlspecialchars($feedLiveUrl) ?>" readonly style="margin: 0; padding: 0.25rem 0.5rem; font-family: monospace; font-size: 0.85rem; border: none; background: transparent; width: 100%; color: #0f172a;" onclick="this.select();">
                <button type="button" id="copyFeedUrlBtn" onclick="copyFeedUrl()" style="margin: 0; padding: 0.4rem 0.85rem; font-size: 0.8rem; font-weight: 700; white-space: nowrap; border-radius: 6px; background-color: #3b82f6; border-color: #3b82f6; color: #ffffff;">
                    <i class="fa-regular fa-copy"></i> Copy Feed URL
                </button>
            </div>

            <!-- 3-Step Setup Guide Accordion/Notes -->
            <div style="background-color: #f8fafc; border-left: 3px solid #3b82f6; padding: 0.75rem 1rem; border-radius: 0 6px 6px 0; font-size: 0.8rem; color: #475569;">
                <div style="font-weight: 800; color: #1e293b; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-circle-info" style="color: #3b82f6;"></i> How to connect with Google Merchant Center:
                </div>
                <ol style="margin: 0; padding-left: 1.25rem; line-height: 1.6;">
                    <li>Log into <a href="https://merchants.google.com" target="_blank" style="font-weight: 700; color: #2563eb;">Google Merchant Center</a> and claim your store domain (<code>shop.lockinrank.com</code>).</li>
                    <li>Go to <strong>Products &rarr; Feeds &rarr; Add Product Feed</strong> &rarr; Select <strong>Scheduled Fetch</strong>.</li>
                    <li>Paste the <strong>Feed URL</strong> above and set the fetch frequency to <strong>Daily</strong>. Google will automatically sync prices, stock, and new products!</li>
                </ol>
            </div>
        </div>

        <script>
        function copyFeedUrl() {
            var input = document.getElementById('gmcFeedUrlInput');
            var btn = document.getElementById('copyFeedUrlBtn');
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(function() {
                var originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
                btn.style.backgroundColor = '#16a34a';
                btn.style.borderColor = '#16a34a';
                setTimeout(function() {
                    btn.innerHTML = originalHtml;
                    btn.style.backgroundColor = '#3b82f6';
                    btn.style.borderColor = '#3b82f6';
                }, 2000);
            });
        }
        </script>

        <!-- Two Column Section: Recent Orders & Catalog Highlights -->
        <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">

            <!-- Recent Orders Preview -->
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">
                            <i class="fa-solid fa-receipt" style="color: #10b981;"></i> Latest Orders
                        </h3>
                        <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Recent purchases and free download claims</p>
                    </div>
                    <a href="/admin/orders" style="font-size: 0.85rem; font-weight: 700; color: #4f46e5; text-decoration: none;">
                        View All Orders (<?= number_format($orderCount ?? count($recentOrders)) ?>) &rarr;
                    </a>
                </div>

                <?php if (empty($recentOrders)): ?>
                    <div style="text-align: center; padding: 2rem 1rem; color: #64748b;">
                        <i class="fa-solid fa-box-open" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 0.5rem;"></i>
                        <p style="margin: 0;">No orders recorded yet.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="font-size: 0.88rem; width: 100%;">
                            <thead>
                                <tr style="border-bottom: 2px solid #e2e8f0;">
                                    <th style="padding: 0.65rem 0.5rem;">Order #</th>
                                    <th style="padding: 0.65rem 0.5rem;">Customer</th>
                                    <th style="padding: 0.65rem 0.5rem;">Payment Method</th>
                                    <th style="padding: 0.65rem 0.5rem;">Amount</th>
                                    <th style="padding: 0.65rem 0.5rem;">Status</th>
                                    <th style="padding: 0.65rem 0.5rem; text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentOrders, 0, 5) as $ord): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 0.65rem 0.5rem; font-weight: 800; color: #0f172a;">
                                            <?= htmlspecialchars($ord['order_number']) ?>
                                        </td>
                                        <td style="padding: 0.65rem 0.5rem;">
                                            <div style="font-weight: 700; color: #334155;"><?= htmlspecialchars($ord['buyer_name']) ?></div>
                                            <div style="font-size: 0.75rem; color: #64748b;"><?= htmlspecialchars($ord['buyer_email']) ?></div>
                                        </td>
                                        <td style="padding: 0.65rem 0.5rem; color: #334155; font-size: 0.82rem;">
                                            <?= htmlspecialchars($ord['payment_method']) ?>
                                        </td>
                                        <td style="padding: 0.65rem 0.5rem; font-weight: 800; color: #0f172a;">
                                            ₹<?= number_format($ord['total_amount'], 0) ?>
                                        </td>
                                        <td style="padding: 0.65rem 0.5rem;">
                                            <?php if ($ord['payment_status'] === 'paid'): ?>
                                                <span style="background: #dcfce7; color: #15803d; font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.45rem; border-radius: 4px;">PAID</span>
                                            <?php else: ?>
                                                <span style="background: #fef3c7; color: #b45309; font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.45rem; border-radius: 4px;"><?= strtoupper(htmlspecialchars($ord['payment_status'])) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 0.65rem 0.5rem; text-align: right;">
                                            <a href="/admin/customer?email=<?= urlencode($ord['buyer_email']) ?>" style="font-size: 0.78rem; padding: 0.25rem 0.55rem; background: #eff6ff; color: #2563eb; border-radius: 4px; font-weight: 700; text-decoration: none;">
                                                <i class="fa-solid fa-user"></i> Profile
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Products Quick View -->
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">
                            <i class="fa-solid fa-cubes" style="color: #4f46e5;"></i> Top Resources
                        </h3>
                        <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Most active course packs and study notes</p>
                    </div>
                    <a href="/admin/products" style="font-size: 0.85rem; font-weight: 700; color: #4f46e5; text-decoration: none;">
                        Manage All Products (<?= count($products) ?>) &rarr;
                    </a>
                </div>

                <div style="overflow-x: auto;">
                    <table style="font-size: 0.88rem; width: 100%;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 0.65rem 0.5rem;">Title</th>
                                <th style="padding: 0.65rem 0.5rem;">Grade / Board</th>
                                <th style="padding: 0.65rem 0.5rem;">Price</th>
                                <th style="padding: 0.65rem 0.5rem;">Sales</th>
                                <th style="padding: 0.65rem 0.5rem; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($products, 0, 5) as $p): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.65rem 0.5rem;">
                                        <div style="font-weight: 700; color: #0f172a;"><?= htmlspecialchars($p['title']) ?></div>
                                        <div style="font-size: 0.72rem; color: #64748b;"><?= htmlspecialchars($p['subject']) ?> &bull; <?= htmlspecialchars($p['file_format']) ?></div>
                                    </td>
                                    <td style="padding: 0.65rem 0.5rem;">
                                        <span class="chip" style="background: #4f46e5; color: #fff; font-size: 0.72rem;"><?= htmlspecialchars($p['grade']) ?></span>
                                    </td>
                                    <td style="padding: 0.65rem 0.5rem; font-weight: 800;">
                                        <?= $p['is_free'] ? '<span style="color:#16a34a;">FREE</span>' : '₹' . number_format($p['price'], 0) ?>
                                    </td>
                                    <td style="padding: 0.65rem 0.5rem; font-weight: 700; color: #0f172a;">
                                        <?= number_format($p['sales_count']) ?>
                                    </td>
                                    <td style="padding: 0.65rem 0.5rem; text-align: right; white-space: nowrap;">
                                        <a href="/admin/product/edit/<?= $p['id'] ?>" style="font-size: 0.78rem; padding: 0.25rem 0.55rem; background: #eef2ff; color: #4f46e5; border-radius: 4px; font-weight: 700; text-decoration: none;">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

</body>
</html>
