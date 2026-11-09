<?php 
$pageTitle = "Your Cart — Lockinrank Shop";
$noIndex = true;
include __DIR__ . '/layout-header.php'; 
?>

<!-- Razorpay Standard Checkout SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<h2 style="margin-bottom: 1.5rem; font-weight: 800; color: #0f172a;">
    <i class="fa-solid fa-cart-shopping" style="color: #4f46e5;"></i> Your Study Resource Cart
</h2>

<?php if (empty($items)): ?>
    <div style="text-align: center; padding: 4rem 1rem; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;">
        <i class="fa-solid fa-cart-arrow-down" style="font-size: 3.5rem; color: #94a3b8; margin-bottom: 1rem;"></i>
        <h4 style="color: #0f172a;">Your cart is currently empty</h4>
        <p style="color: #64748b; margin-bottom: 1.5rem;">Explore high-yield problem packs for JEE Main, JEE Advanced, NEET, and AP Physics.</p>
        <a href="/" role="button" style="background-color: #4f46e5; color: #ffffff !important; border: 1px solid #4f46e5; font-weight: 700; text-decoration: none;">
            Browse Resource Catalog
        </a>
    </div>
<?php else: ?>
    <div class="cart-layout">

        <!-- Items List -->
        <div>
            <?php foreach ($items as $item): ?>
                <div style="display: flex; gap: 1rem; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                    <a href="/product/<?= htmlspecialchars($item['slug'] ?? '') ?>" style="display: block; flex-shrink: 0;" title="View <?= htmlspecialchars($item['title']) ?>">
                        <img src="<?= htmlspecialchars(!empty($item['thumbnail_url']) ? $item['thumbnail_url'] : '/images/default-study-material.png') ?>" 
                             alt="<?= htmlspecialchars($item['title']) ?>" 
                             onerror="this.onerror=null; this.src='/images/default-study-material.png';"
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; display: block;">
                    </a>
                    <div style="flex: 1;">
                        <h4 style="font-size: 1rem; margin-bottom: 0.25rem; font-weight: 700; color: #0f172a;">
                            <?= htmlspecialchars($item['title']) ?>
                        </h4>
                        <div style="font-size: 0.8rem; color: #64748b;">
                            <span class="chip" style="font-size: 0.7rem; padding: 0.1rem 0.4rem; background: #0284c7;"><?= htmlspecialchars($item['board'] ?? 'CBSE') ?></span>
                            <?= htmlspecialchars($item['grade'] ?? '') ?> &bull; Single-classroom / home license
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 1.25rem; font-weight: 800; margin-bottom: 0.5rem; color: #0f172a;">
                            <?= $item['is_free'] ? '<span class="price-free">FREE</span>' : '₹' . number_format($item['price'], 0) ?>
                        </div>
                        <a href="/cart/remove/<?= $item['id'] ?>" style="font-size: 0.8rem; color: #ef4444; text-decoration: none; font-weight: 600;">
                            <i class="fa-solid fa-trash"></i> Remove
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Checkout Summary (Live Razorpay Standard Gateway) -->
        <div>
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <h4 style="margin-bottom: 1rem; font-size: 1.15rem; font-weight: 700; color: #0f172a;">Order Summary</h4>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem; color: #334155;">
                    <span>Resources (<?= count($items) ?>):</span>
                    <span>₹<?= number_format($subtotal, 0) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem; font-size: 0.9rem; color: #64748b;">
                    <span>Delivery (Digital Download):</span>
                    <span style="color: #16a34a; font-weight: 600;">FREE (₹0)</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; font-size: 1.35rem; font-weight: 800;">
                    <span style="color: #0f172a;">Total Amount:</span>
                    <span style="color: #0284c7;">₹<?= number_format($subtotal, 0) ?></span>
                </div>

                <!-- Alert Message Box -->
                <div id="checkout-alert" style="display: none; padding: 0.75rem; border-radius: 6px; font-size: 0.85rem; margin-bottom: 1rem;"></div>

                <!-- Razorpay Checkout Form -->
                <form id="razorpay-checkout-form" onsubmit="handleRazorpayCheckout(event)">
                    
                    <div style="margin-bottom: 0.75rem;">
                        <label for="buyer_name" style="font-size: 0.82rem; font-weight: 700; color: #1e293b;">
                            Aspirant / Educator Name <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" id="buyer_name" name="buyer_name" placeholder="Enter student or teacher full name" required minlength="2" style="font-size: 0.88rem; background-color: #ffffff; color: #0f172a;">
                    </div>
                    
                    <div style="margin-bottom: 0.75rem;">
                        <label for="buyer_email" style="font-size: 0.82rem; font-weight: 700; color: #1e293b;">
                            Email for Instant Download &amp; Invoice <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="email" id="buyer_email" name="buyer_email" placeholder="name@example.com (links sent here)" required style="font-size: 0.88rem; background-color: #ffffff; color: #0f172a;">
                    </div>

                    <div style="margin-bottom: 1.25rem;">
                        <label for="buyer_phone" style="font-size: 0.82rem; font-weight: 700; color: #1e293b;">
                            <i class="fa-brands fa-whatsapp" style="color: #25d366;"></i> WhatsApp Mobile Number (+91) <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="tel" id="buyer_phone" name="buyer_phone" placeholder="10-digit WhatsApp number (e.g. 9876543210)" required pattern="[0-9+ -]{10,15}" style="font-size: 0.88rem; background-color: #ffffff; color: #0f172a;">
                    </div>

                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem; margin-bottom: 1.25rem; font-size: 0.82rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                            <span style="font-weight: 700; color: #0f172a;">Payment Gateway:</span>
                            <?php $isLive = str_starts_with($razorpayKey ?? '', 'rzp_live'); ?>
                            <span class="chip" style="background-color: <?= $isLive ? '#10b981' : '#0284c7' ?>; font-size: 0.68rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px; color: #ffffff;">
                                <i class="fa-solid fa-shield-halved"></i> <?= $isLive ? '100% SECURE CHECKOUT' : 'RAZORPAY TEST MODE' ?>
                            </span>
                        </div>
                        <div style="color: #64748b; line-height: 1.4;">
                            Supports <strong>UPI</strong> (GPay, PhonePe, Paytm, QR), <strong>RuPay / Visa / Mastercard</strong>, and Netbanking.
                        </div>
                    </div>

                    <button type="submit" id="pay-button" style="width: 100%; font-size: 1rem; padding: 0.75rem; font-weight: 700; background-color: #10b981; border-color: #10b981; color: #ffffff !important; cursor: pointer;">
                        <?php if ($subtotal <= 0): ?>
                            <i class="fa-solid fa-cloud-arrow-down"></i> Download 100% Freebies
                        <?php else: ?>
                            <i class="fa-solid fa-lock"></i> Pay ₹<?= number_format($subtotal, 0) ?> via Razorpay
                        <?php endif; ?>
                    </button>
                </form>

                <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1rem; color: #64748b; font-size: 0.72rem;">
                    <i class="fa-solid fa-shield-halved" style="color: #0284c7;"></i> 256-bit SSL Encrypted &bull; Instant Signed Download
                </div>
            </div>
        </div>

    </div>

    <!-- Client-side Razorpay Checkout Handler Script -->
    <script>
    async function handleRazorpayCheckout(e) {
        e.preventDefault();
        const payBtn = document.getElementById('pay-button');
        const alertBox = document.getElementById('checkout-alert');
        
        // Disable button to prevent double-click
        payBtn.disabled = true;
        const originalHtml = payBtn.innerHTML;
        payBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Initializing Secure Gateway...';
        alertBox.style.display = 'none';

        const formData = new FormData(document.getElementById('razorpay-checkout-form'));

        try {
            const response = await fetch('/cart/initiate-payment', {
                method: 'POST',
                body: formData
            });

            const textResponse = await response.text();
            let data;
            try {
                data = JSON.parse(textResponse);
            } catch (err) {
                console.error("Non-JSON server response:", textResponse);
                throw new Error("Server error (" + response.status + "): Could not process order. Please try again.");
            }

            if (data.status === 'error') {
                throw new Error(data.message || 'Payment initiation failed.');
            }

            // Case A: Freebie (₹0) - Direct fulfillment
            if (data.status === 'free') {
                window.location.href = data.redirect_url;
                return;
            }

            // Case B: Paid order with Razorpay
            if (data.status === 'payment_required') {
                const options = {
                    key: data.key,
                    amount: data.amount,
                    currency: data.currency,
                    name: data.name,
                    description: data.description,
                    order_id: data.order_id,
                    prefill: data.prefill,
                    theme: data.theme,
                    modal: {
                        ondismiss: function () {
                            payBtn.disabled = false;
                            payBtn.innerHTML = originalHtml;
                            alertBox.style.display = 'block';
                            alertBox.style.backgroundColor = '#fef2f2';
                            alertBox.style.color = '#991b1b';
                            alertBox.style.border = '1px solid #fecaca';
                            alertBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Payment window was closed. You can retry anytime.';
                        }
                    },
                    handler: async function (paymentResponse) {
                        payBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying Payment with Razorpay...';
                        
                        try {
                            const verifyPayload = {
                                razorpay_order_id: paymentResponse.razorpay_order_id,
                                razorpay_payment_id: paymentResponse.razorpay_payment_id,
                                razorpay_signature: paymentResponse.razorpay_signature,
                                internal_order_number: data.internal_order_number
                            };

                            const verifyRes = await fetch('/cart/verify-payment', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: new URLSearchParams(verifyPayload)
                            });

                            const verifyText = await verifyRes.text();
                            let verifyData;
                            try {
                                verifyData = JSON.parse(verifyText);
                            } catch (e) {
                                throw new Error("Payment verification server error (" + verifyRes.status + ")");
                            }

                            if (verifyData.status === 'success') {
                                window.location.href = verifyData.redirect_url;
                            } else {
                                throw new Error(verifyData.message || 'Signature verification failed');
                            }
                        } catch (err) {
                            alertBox.style.display = 'block';
                            alertBox.style.backgroundColor = '#fef2f2';
                            alertBox.style.color = '#991b1b';
                            alertBox.style.border = '1px solid #fecaca';
                            alertBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Verification Error: ' + err.message;
                            payBtn.disabled = false;
                            payBtn.innerHTML = originalHtml;
                        }
                    }
                };

                const rzp = new Razorpay(options);
                rzp.open();
            }

        } catch (err) {
            alertBox.style.display = 'block';
            alertBox.style.backgroundColor = '#fef2f2';
            alertBox.style.color = '#991b1b';
            alertBox.style.border = '1px solid #fecaca';
            alertBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + err.message;
            payBtn.disabled = false;
            payBtn.innerHTML = originalHtml;
        }
    }
    </script>
<?php endif; ?>

<?php include __DIR__ . '/layout-footer.php'; ?>
