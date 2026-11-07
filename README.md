# 🚀 Lockinrank Shop — Competitive Exam Prep & AP Physics Store

**Production Host:** `shop.lockinrank.com`  
**Core Domain:** Premier Academic Resources, JEE Main & Advanced Problem Sets, NEET Biology DPPs, and International AP Physics (College Board) Workbooks.

An ultra-lightweight, zero-build, blazingly fast digital storefront built with **Flight PHP**, **Pico CSS v2**, and **HTMX**. Integrated with **Razorpay Standard Checkout** (UPI, RuPay, Netbanking, Cards) and a secure **password-protected admin portal** at `/admin/`.

---

## ⚡ Tech Stack & Footprint

| Layer | Technology | Footprint | Key Role |
| :--- | :--- | :--- | :--- |
| **Backend** | **Flight PHP (v3.19)** | ~200 KB | Micro-framework with sub-2ms bootstrap time and zero node build steps. |
| **Styling** | **Pico CSS (v2) + `app.css`** | ~20 KB | Semantic, accessible HTML styling with Lockinrank's custom electric indigo (`#4f46e5`) theme. |
| **Reactivity** | **HTMX (v1.9)** | ~14 KB | Live search with 250ms debounce, multi-filter faceted search, dynamic cart updates, and watermarked modals. |
| **Payments** | **Razorpay Standard SDK** | Live Gateway | Server-to-server order creation in paise + client-side checkout modal + HMAC-SHA256 verification. |
| **Database** | **SQLite (PDO)** | Portable | Zero-daemon database (`data/tpt.db`), ready to connect to Hostinger MySQL via PDO. |

---

## 🔐 Password-Protected Admin Portal

All administrative operations have been removed from the public storefront and secured under the `/admin/` route.

* **Admin URL:** [`http://localhost:8088/admin/login`](http://localhost:8088/admin/login) (in production: `https://shop.lockinrank.com/admin/login`)
* **Default Username:** `admin`
* **Default Password:** `admin123`
* **Configuration & Password Hash:** Configured in [`config/admin.php`](config/admin.php) with Bcrypt hashing.

### Admin Features:
1. **Control Center Dashboard:** Displays live direct revenue (INR ₹), total paid sales, **free sample downloads**, active materials count, and student rating average.
2. **Order Receipts Log:** Displays recent customer orders, contact numbers, order amounts, and Razorpay Payment IDs.
3. **Resource Publisher:** Form to publish new DPPs, question sets, formula cards, and test series for JEE, NEET, and AP Physics.
4. **Product Management:** Direct delete and view actions for each material.
5. **Secure Sign Out:** Destroys admin session with timeout protection.

---

## 📥 1-Click Sample Download & Tracking

* **Student Experience:** On every paid product page, students have a 1-click **"Download Free Sample Pages (PDF)"** option that requires no login or email forms.
* **Backend Tracking:** Each download triggers `GET /product/sample/{id}`, which atomically increments `products.sample_downloads` in SQLite.
* **Conversion Analytics:** The Admin Dashboard displays sample downloads vs paid purchases per study package to analyze interest and conversion.

---

## 💳 Razorpay Configuration

Your Razorpay credentials and store branding are configured in [`config/razorpay.php`](config/razorpay.php):

```php
return [
    'key_id' => 'rzp_test_Tp3PMKVjGXswG8',
    'key_secret' => 'm2Q4SNsHHGxafFRFFZ32MhA7',
    'company_name' => 'Lockinrank Shop',
    'domain' => 'shop.lockinrank.com',
    'theme_color' => '#4f46e5',
    'currency' => 'INR'
];
```

---

## 🚀 Running Locally

Start the local server with PHP 8.2+:
```powershell
$env:PATH = "C:\xampp1\php;$env:PATH"
cd c:\maz\smaphysics\Projects\shop
php -S localhost:8088 -t public
```

### Key Endpoints:
* **Public Storefront:** [http://localhost:8088/](http://localhost:8088/)
* **Cart & Razorpay Checkout:** [http://localhost:8088/cart](http://localhost:8088/cart)
* **100% Free Resources:** [http://localhost:8088/?price=free](http://localhost:8088/?price=free)
* **Protected Admin Portal:** [http://localhost:8088/admin](http://localhost:8088/admin) (redirects to `/admin/login`)
* **Admin Login:** [http://localhost:8088/admin/login](http://localhost:8088/admin/login)
* **Terms & Conditions:** [http://localhost:8088/terms](http://localhost:8088/terms)
* **Privacy Policy:** [http://localhost:8088/privacy](http://localhost:8088/privacy)
* **Shipping & Delivery:** [http://localhost:8088/shipping](http://localhost:8088/shipping)
* **Cancellation & Refunds:** [http://localhost:8088/cancellation-refunds](http://localhost:8088/cancellation-refunds)
* **Contact Support:** [http://localhost:8088/contact](http://localhost:8088/contact)
