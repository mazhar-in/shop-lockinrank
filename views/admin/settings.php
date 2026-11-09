<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Security & Password Settings — Lockinrank Control Panel') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">

    <?php 
    $activeTab = 'settings';
    include __DIR__ . '/layout-nav.php'; 
    ?>

    <main class="container" style="max-width: 900px; padding: 2.5rem 1.5rem;">

        <!-- Header -->
        <div style="margin-bottom: 2rem;">
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: #e0e7ff; color: #4338ca; padding: 0.3rem 0.85rem; border-radius: 20px; font-size: 0.78rem; font-weight: 800; margin-bottom: 0.75rem;">
                <i class="fa-solid fa-shield-halved"></i> SECURITY &amp; CREDENTIALS
            </div>
            <h1 style="font-size: 1.8rem; font-weight: 900; margin: 0 0 0.35rem 0; color: #0f172a;">
                Admin Account Settings
            </h1>
            <p style="color: #64748b; margin: 0; font-size: 0.95rem;">
                Manage administrator credentials and secure access to the <strong>shop.lockinrank.com</strong> control panel.
            </p>
        </div>

        <?php if (!empty($isDefaultPassword)): ?>
            <!-- Default Password Warning -->
            <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1.5px solid #f59e0b; border-radius: 10px; padding: 1.1rem 1.25rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.08);">
                <div style="width: 42px; height: 42px; border-radius: 8px; background: #f59e0b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 0.95rem; color: #92400e;">
                        Default Password Currently Active
                    </div>
                    <div style="font-size: 0.82rem; color: #b45309;">
                        Your store is currently using the initial default password (<code>admin123</code>). Please choose a strong new password below to secure your administrative portal.
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid" style="grid-template-columns: 1.6fr 1fr; gap: 2rem; align-items: flex-start;">

            <!-- Password Change Form -->
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                
                <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background: #4f46e5; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #0f172a;">Change Admin Password</h3>
                        <div style="font-size: 0.78rem; color: #64748b;">Update your portal login key securely</div>
                    </div>
                </div>

                <form method="POST" action="/admin/settings/password" style="margin-bottom: 0;">
                    
                    <!-- Username Display -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-weight: 800; font-size: 0.85rem; color: #1e293b; margin-bottom: 0.35rem; display: block;">
                            Username
                        </label>
                        <input type="text" value="<?= htmlspecialchars($username) ?>" disabled style="background-color: #f8fafc; color: #64748b; font-size: 0.9rem; cursor: not-allowed; margin-bottom: 0;">
                        <small style="color: #94a3b8; font-size: 0.75rem;">Administrator username cannot be changed.</small>
                    </div>

                    <!-- Admin Display Name -->
                    <div style="margin-bottom: 1.25rem;">
                        <label for="admin_name" style="font-weight: 800; font-size: 0.85rem; color: #1e293b; margin-bottom: 0.35rem; display: block;">
                            Admin Display Name
                        </label>
                        <input type="text" id="admin_name" name="admin_name" value="<?= htmlspecialchars($adminName) ?>" placeholder="Lockinrank Admin" style="font-size: 0.9rem; margin-bottom: 0;">
                    </div>

                    <!-- Current Password -->
                    <div style="margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                            <label for="current_password" style="font-weight: 800; font-size: 0.85rem; color: #1e293b; margin-bottom: 0;">
                                Current Password <span style="color: #ef4444;">*</span>
                            </label>
                            <?php if (!empty($isDefaultPassword)): ?>
                                <span style="font-size: 0.72rem; color: #d97706; font-weight: 700;">Default: admin123</span>
                            <?php endif; ?>
                        </div>
                        <div style="position: relative;">
                            <input type="password" id="current_password" name="current_password" required placeholder="Enter current password" style="font-size: 0.9rem; padding-right: 2.5rem; margin-bottom: 0;">
                            <button type="button" onclick="togglePasswordVisibility('current_password', this)" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px; font-size: 0.9rem;">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #f1f5f9;">

                    <!-- New Password -->
                    <div style="margin-bottom: 1.25rem;">
                        <label for="new_password" style="font-weight: 800; font-size: 0.85rem; color: #1e293b; margin-bottom: 0.35rem; display: block;">
                            New Password <span style="color: #ef4444;">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="new_password" name="new_password" required minlength="6" placeholder="At least 6 characters" style="font-size: 0.9rem; padding-right: 2.5rem; margin-bottom: 0.25rem;" oninput="checkPasswordStrength(this.value)">
                            <button type="button" onclick="togglePasswordVisibility('new_password', this)" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px; font-size: 0.9rem;">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div id="password-strength" style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">
                            Minimum 6 characters. Use letters, numbers &amp; symbols.
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div style="margin-bottom: 1.75rem;">
                        <label for="confirm_password" style="font-weight: 800; font-size: 0.85rem; color: #1e293b; margin-bottom: 0.35rem; display: block;">
                            Confirm New Password <span style="color: #ef4444;">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Re-enter new password" style="font-size: 0.9rem; padding-right: 2.5rem; margin-bottom: 0;">
                            <button type="button" onclick="togglePasswordVisibility('confirm_password', this)" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px; font-size: 0.9rem;">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <button type="submit" style="background-color: #4f46e5; border-color: #4f46e5; color: #ffffff; font-weight: 700; font-size: 0.92rem; padding: 0.65rem 1.5rem; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0;">
                            <i class="fa-solid fa-lock"></i> Save New Password
                        </button>
                        <a href="/admin/dashboard" role="button" class="secondary" style="margin-bottom: 0; font-size: 0.9rem; padding: 0.65rem 1.25rem; text-decoration: none; border-radius: 8px;">
                            Cancel
                        </a>
                    </div>

                </form>

            </div>

            <!-- Security Sidebar & Best Practices -->
            <div>
                
                <!-- Security Checklist -->
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-shield-check" style="color: #10b981;"></i> Password Best Practices
                    </h4>
                    <ul style="font-size: 0.82rem; color: #475569; line-height: 1.6; padding-left: 1.2rem; margin-bottom: 0;">
                        <li style="margin-bottom: 0.4rem;">Use at least 8 characters for robust protection.</li>
                        <li style="margin-bottom: 0.4rem;">Include uppercase and lowercase letters.</li>
                        <li style="margin-bottom: 0.4rem;">Include at least one number and special symbol (e.g., <code>!@#$%^&amp;*</code>).</li>
                        <li style="margin-bottom: 0.4rem;">Do not reuse passwords across multiple websites.</li>
                        <li>All passwords are encrypted with one-way <strong>Bcrypt</strong> hashing.</li>
                    </ul>
                </div>

                <!-- Session Security Info -->
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-clock-rotate-left" style="color: #6366f1;"></i> Active Session
                    </h4>
                    <div style="font-size: 0.82rem; color: #64748b; line-height: 1.6;">
                        <div>Logged in as: <strong style="color: #0f172a;"><?= htmlspecialchars($username) ?></strong></div>
                        <div>Display Name: <strong style="color: #0f172a;"><?= htmlspecialchars($adminName) ?></strong></div>
                        <div>Session duration: <strong>24 Hours</strong> (auto-expires on inactivity)</div>
                        <div style="margin-top: 0.75rem;">
                            <a href="/admin/logout" style="color: #ef4444; font-weight: 700; text-decoration: none; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign out from this device
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <script>
    function togglePasswordVisibility(fieldId, btn) {
        const input = document.getElementById(fieldId);
        if (!input) return;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }

    function checkPasswordStrength(val) {
        const el = document.getElementById('password-strength');
        if (!el) return;
        if (!val || val.length === 0) {
            el.innerHTML = 'Minimum 6 characters. Use letters, numbers &amp; symbols.';
            el.style.color = '#94a3b8';
            return;
        }
        if (val.length < 6) {
            el.innerHTML = '<span style="color: #dc2626;"><i class="fa-solid fa-xmark"></i> Too short (minimum 6 characters required)</span>';
            return;
        }
        if (val === 'admin123') {
            el.innerHTML = '<span style="color: #dc2626;"><i class="fa-solid fa-triangle-exclamation"></i> Cannot use the default password "admin123"</span>';
            return;
        }

        let strength = 0;
        if (val.length >= 8) strength++;
        if (/[A-Z]/.test(val)) strength++;
        if (/[0-9]/.test(val)) strength++;
        if (/[^A-Za-z0-9]/.test(val)) strength++;

        if (strength <= 1) {
            el.innerHTML = '<span style="color: #ea580c;"><i class="fa-solid fa-shield"></i> Weak password — add numbers or uppercase letters</span>';
        } else if (strength <= 3) {
            el.innerHTML = '<span style="color: #ca8a04;"><i class="fa-solid fa-shield-halved"></i> Moderate password — good, add a symbol to make it stronger</span>';
        } else {
            el.innerHTML = '<span style="color: #16a34a;"><i class="fa-solid fa-shield-check"></i> Strong password! Excellent protection.</span>';
        }
    }
    </script>
</body>
</html>
