# 🚀 Hostinger Automatic GitHub Deployment Guide

This guide details how to publish and auto-deploy **Lockinrank Shop** (`shop.lockinrank.com`) to Hostinger directly from this GitHub repository.

---

## ⚡ Architecture for Hostinger

* **Root Document Bridge**: The repository includes a root `.htaccess` and `index.php` that automatically routes all incoming web traffic into `public/` while securing sensitive backend directories (`config/`, `data/`, `src/`, `storage/`, `vendor/`, `views/`).
* **Zero SSH / Zero Composer Requirement**: The lightweight `vendor/` framework bundle is committed to Git, enabling Hostinger's Git tool to pull and run the application instantly without needing SSH terminal access or composer install steps.
* **Dual Database Support**:
  * **Default (Turnkey)**: SQLite via PDO (`data/tpt.db`). Requires **zero database creation or credentials**.
  * **Optional MySQL**: Configured in [`config/database.php`](config/database.php) or via environment variables.

---

## 🛠️ Step 1: Connect Git in Hostinger hPanel

1. Log in to your **Hostinger Control Panel (hPanel)**.
2. Select your domain or subdomain (e.g., `shop.lockinrank.com`).
3. In the left sidebar, navigate to **Advanced** &rarr; **Git**.
4. Fill in the deployment form:
   * **Repository:** `https://github.com/mazhar-in/shop-lockinrank.git`
   * **Branch:** `main`
   * **Install Directory:** Leave blank for root `public_html`, or enter `public_html` (or your subdomain folder, e.g., `domains/shop.lockinrank.com/public_html`).
5. Click **Create**. Hostinger will clone the repository.

---

## 🔄 Step 2: Set Up Automatic Deployments (GitHub Webhook)

1. After Hostinger creates the Git repository, it displays a **Webhook URL** (e.g. `https://hpanel.hostinger.com/api/git/deploy/...`).
2. Copy this **Webhook URL**.
3. Open your GitHub repository: [https://github.com/mazhar-in/shop-lockinrank](https://github.com/mazhar-in/shop-lockinrank)
4. Go to **Settings** &rarr; **Webhooks** &rarr; **Add webhook**.
5. Paste the Hostinger URL into **Payload URL**.
6. Set Content type to `application/json`.
7. Choose **Just the push event**.
8. Click **Add webhook**.

> **Result**: Whenever you push code to `main`, GitHub pings Hostinger and the live store updates automatically within seconds!

---

## ⚙️ Step 3: PHP Version Configuration

1. In hPanel, go to **Advanced** &rarr; **PHP Configuration**.
2. Ensure the PHP version is set to **PHP 8.2** or **PHP 8.3**.
3. Under the **PHP Extensions** tab, confirm that `pdo_sqlite` and `curl` are enabled (enabled by default on Hostinger).

---

## 💳 Step 4: Razorpay Production Keys (When Going Live)

When ready to switch from Razorpay Test mode to Live payments:
Edit [`config/razorpay.php`](config/razorpay.php) in your repository or set environment variables:
```php
'key_id' => 'rzp_live_YourKeyIdHere',
'key_secret' => 'YourLiveSecretHere',
```
Commit and push to `main` — Hostinger will deploy the live payment configuration immediately.

---

## 🔐 Admin Portal Access on Hostinger

* **URL:** `https://shop.lockinrank.com/admin/login`
* **Default Username:** `admin`
* **Default Password:** `admin123`
* **Change Credentials:** Edit [`config/admin.php`](config/admin.php) to update username or generate a new Bcrypt hash with `password_hash('your_new_password', PASSWORD_BCRYPT)`.
