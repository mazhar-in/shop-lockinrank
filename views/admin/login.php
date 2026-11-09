<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Portal — Lockinrank Shop') ?></title>
    <!-- Pico CSS v2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="/css/app.min.css?v=2.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); padding: 1.5rem;">

    <div style="width: 100%; max-width: 440px; background: #ffffff; border-radius: 12px; padding: 2.5rem 2rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2);">
        
        <!-- Brand Header -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <img src="/images/lockinrank-icon.svg" alt="Lockinrank" style="width: 72px; height: 72px; border-radius: 16px; margin-bottom: 1rem; box-shadow: 0 10px 20px -5px rgba(53, 74, 79, 0.35); display: inline-block;">
            <h2 style="margin: 0; font-size: 1.6rem; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">
                LOCKINRANK <span style="color: #059669;">SHOP</span>
            </h2>
            <p style="margin: 0.35rem 0 0 0; color: #64748b; font-size: 0.88rem; font-weight: 500;">
                Authorized Administrator Portal
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.88rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="/admin/login" method="POST" style="margin-bottom: 0;">
            
            <div style="margin-bottom: 1.25rem;">
                <label for="username" style="font-size: 0.84rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    Admin Username
                </label>
                <div style="position: relative;">
                    <input type="text" id="username" name="username" placeholder="e.g. admin" required autofocus
                           style="padding-left: 2.5rem; font-size: 0.95rem; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <i class="fa-solid fa-user" style="position: absolute; left: 1rem; top: 1.15rem; color: #94a3b8; font-size: 0.95rem;"></i>
                </div>
            </div>

            <div style="margin-bottom: 1.75rem;">
                <label for="password" style="font-size: 0.84rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    Password
                </label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" placeholder="••••••••" required
                           style="padding-left: 2.5rem; font-size: 0.95rem; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <i class="fa-solid fa-key" style="position: absolute; left: 1rem; top: 1.15rem; color: #94a3b8; font-size: 0.95rem;"></i>
                </div>
            </div>

            <button type="submit" style="width: 100%; padding: 0.85rem; font-size: 1rem; font-weight: 800; background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); border: none; border-radius: 8px; color: #ffffff; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.25);">
                <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right: 0.4rem;"></i> Secure Sign In
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.75rem; border-top: 1px solid #f1f5f9; padding-top: 1.25rem;">
            <a href="/" style="font-size: 0.84rem; color: #64748b; text-decoration: none; font-weight: 600;">
                <i class="fa-solid fa-arrow-left"></i> Return to Public Storefront
            </a>
        </div>

    </div>

</body>
</html>
