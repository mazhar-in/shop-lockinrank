<?php
// Shared Admin Navigation & Top Bar
// Expected parameters: $activeTab (dashboard | products | orders | customers | upload)
$activeTab = $activeTab ?? 'dashboard';
?>
<!-- Top Admin Navigation Bar -->
<header style="background: #0f172a; border-bottom: 1px solid #1e293b; padding: 0.75rem 2rem; position: sticky; top: 0; z-index: 50;">
    <div style="max-width: 1300px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <img src="/images/lockinrank-icon.svg" alt="Lockinrank" style="width: 34px; height: 34px; border-radius: 8px; display: block;">
            <div>
                <a href="/admin/dashboard" style="text-decoration: none; display: flex; align-items: center; gap: 0.35rem;">
                    <span style="font-size: 1.15rem; font-weight: 900; color: #ffffff; letter-spacing: -0.5px;">LOCKINRANK</span>
                    <span style="background: #f59e0b; color: #000000; font-size: 0.65rem; font-weight: 800; padding: 0.15rem 0.4rem; border-radius: 4px; text-transform: uppercase;">ADMIN</span>
                </a>
                <div style="color: #64748b; font-size: 0.72rem;">shop.lockinrank.com &bull; Control Panel</div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="/" target="_blank" style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Live Store
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

<!-- Secondary Modular Control Panel Tabs -->
<div style="background: #1e293b; border-bottom: 1px solid #334155; padding: 0 2rem;">
    <div style="max-width: 1300px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <nav style="display: flex; align-items: center; gap: 0.5rem; overflow-x: auto; padding: 0.5rem 0;">
            <a href="/admin/dashboard" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.55rem 1rem; border-radius: 6px; font-size: 0.9rem; font-weight: 700; text-decoration: none; transition: all 0.15s ease; <?= $activeTab === 'dashboard' ? 'background: #4f46e5; color: #ffffff !important;' : 'color: #94a3b8; hover:color: #ffffff;' ?>">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
            <a href="/admin/products" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.55rem 1rem; border-radius: 6px; font-size: 0.9rem; font-weight: 700; text-decoration: none; transition: all 0.15s ease; <?= $activeTab === 'products' ? 'background: #4f46e5; color: #ffffff !important;' : 'color: #94a3b8;' ?>">
                <i class="fa-solid fa-cubes"></i> Products
            </a>
            <a href="/admin/orders" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.55rem 1rem; border-radius: 6px; font-size: 0.9rem; font-weight: 700; text-decoration: none; transition: all 0.15s ease; <?= $activeTab === 'orders' ? 'background: #4f46e5; color: #ffffff !important;' : 'color: #94a3b8;' ?>">
                <i class="fa-solid fa-receipt"></i> Orders
            </a>
            <a href="/admin/customers" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.55rem 1rem; border-radius: 6px; font-size: 0.9rem; font-weight: 700; text-decoration: none; transition: all 0.15s ease; <?= $activeTab === 'customers' ? 'background: #4f46e5; color: #ffffff !important;' : 'color: #94a3b8;' ?>">
                <i class="fa-solid fa-users"></i> Customers
            </a>
        </nav>

        <div style="padding: 0.5rem 0;">
            <a href="/admin/upload" role="button" style="background-color: #10b981; border-color: #10b981; color: #ffffff !important; font-weight: 700; font-size: 0.85rem; padding: 0.5rem 1rem; display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 6px; text-decoration: none; margin-bottom: 0;">
                <i class="fa-solid fa-plus"></i> Publish Material
            </a>
        </div>
    </div>
</div>

<?php 
// Render session flash alerts if available
if (!empty($_SESSION['admin_flash_success'])): 
?>
    <div style="max-width: 1300px; margin: 1rem auto 0 auto; padding: 0 1.5rem;">
        <div style="background-color: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.9rem; display: flex; align-items: center; justify-content: space-between;">
            <div><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_SESSION['admin_flash_success']) ?></div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #166534; cursor: pointer; padding: 0; font-size: 1rem;">&times;</button>
        </div>
    </div>
<?php 
    unset($_SESSION['admin_flash_success']); 
endif; 
?>

<?php 
if (!empty($_SESSION['admin_flash_error'])): 
?>
    <div style="max-width: 1300px; margin: 1rem auto 0 auto; padding: 0 1.5rem;">
        <div style="background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.9rem; display: flex; align-items: center; justify-content: space-between;">
            <div><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($_SESSION['admin_flash_error']) ?></div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #991b1b; cursor: pointer; padding: 0; font-size: 1rem;">&times;</button>
        </div>
    </div>
<?php 
    unset($_SESSION['admin_flash_error']); 
endif; 
?>

