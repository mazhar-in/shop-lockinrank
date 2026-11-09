    </main>
    <!-- Main Container End -->

    <!-- Quick Preview Modal Target -->
    <dialog id="preview-modal">
        <article class="modal-preview-box" id="preview-modal-content">
            <!-- Content loaded dynamically via HTMX -->
        </article>
    </dialog>

    <!-- Mobile App Bottom Navigation Bar (Visible on mobile browsing pages) -->
    <?php if (empty($hideMobileBottomNav)): ?>
    <nav class="mobile-bottom-nav" id="mobileBottomNav" aria-label="Mobile Navigation">
        <a href="/" class="mobile-nav-item <?= (!isset($_GET['price']) && ($_SERVER['REQUEST_URI'] === '/' || str_starts_with($_SERVER['REQUEST_URI'], '/?'))) ? 'active' : '' ?>">
            <i class="fa-solid fa-compass"></i>
            <span>Catalog</span>
        </a>
        <a href="/?price=free" class="mobile-nav-item <?= (isset($_GET['price']) && $_GET['price'] === 'free') ? 'active' : '' ?>" style="color: #10b981;">
            <i class="fa-solid fa-gift"></i>
            <span>Freebies</span>
        </a>
        <a href="/cart" class="mobile-nav-item <?= (str_starts_with($_SERVER['REQUEST_URI'], '/cart')) ? 'active' : '' ?>">
            <div style="position: relative; display: inline-block;">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="mobile-cart-badge" id="mobileBottomCartBadge"><?= $cartCount ?? 0 ?></span>
            </div>
            <span>Cart</span>
        </a>
        <a href="/contact" class="mobile-nav-item <?= (str_starts_with($_SERVER['REQUEST_URI'], '/contact')) ? 'active' : '' ?>">
            <i class="fa-regular fa-envelope"></i>
            <span>Support</span>
        </a>
    </nav>
    <?php endif; ?>

    <!-- Site Footer with Legal Links & Compliance -->
    <footer class="site-footer" style="background-color: #ffffff; border-top: 1px solid #e2e8f0; padding: 2.5rem 0 2rem 0; margin-top: auto; position: relative; z-index: 10;">
        <div class="container">
            <div class="grid" style="grid-template-columns: 2fr 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                
                <!-- Col 1: Store Bio -->
                <div>
                    <a href="/" class="brand-logo" style="margin-bottom: 0.75rem; display: inline-flex;">
                        <img src="/images/lockinrank-logo.svg" alt="Lockinrank Shop" style="height: 42px; width: auto; display: block;">
                    </a>
                    <p style="font-size: 0.88rem; color: #64748b; line-height: 1.6; max-width: 420px; margin-bottom: 0.75rem;">
                        Premier digital store for <strong>Lockinrank</strong> (<strong>shop.lockinrank.com</strong>). Rigorous, high-yield DPPs, question sets, formula cards, and concept boosters for JEE Main, JEE Advanced, NEET, CBSE Boards, and College Board AP Physics.
                    </p>
                    <div style="display: flex; gap: 0.75rem; font-size: 0.85rem; color: #16a34a; font-weight: 600; flex-wrap: wrap;">
                        <span><i class="fa-solid fa-shield-halved"></i> 100% Verified Academic Solutions</span>
                        <span>&bull;</span>
                        <span><i class="fa-brands fa-whatsapp"></i> Instant WhatsApp & Email Delivery</span>
                    </div>
                </div>

                <!-- Col 2: Customer Care & Policies -->
                <div>
                    <h5 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        Policy & Support
                    </h5>
                    <ul style="list-style: none; padding-left: 0; font-size: 0.88rem; line-height: 2;">
                        <li><a href="https://wa.me/919990253219" target="_blank" style="color: #16a34a; font-weight: 700; text-decoration: none;"><i class="fa-brands fa-whatsapp"></i> WhatsApp Support</a></li>
                        <li><a href="/contact" style="color: #475569; text-decoration: none;">Help &amp; Support Desk</a></li>
                        <li><a href="/shipping" style="color: #475569; text-decoration: none;">Instant Digital Delivery</a></li>
                        <li><a href="/cancellation-refunds" style="color: #475569; text-decoration: none;">Cancellation &amp; Refunds</a></li>
                        <li><a href="/privacy" style="color: #475569; text-decoration: none;">Privacy Policy</a></li>
                        <li><a href="/terms" style="color: #475569; text-decoration: none;">Terms &amp; Conditions</a></li>
                    </ul>
                </div>

                <!-- Col 3: Quick Links (No admin links) -->
                <div>
                    <h5 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        Curriculum
                    </h5>
                    <ul style="list-style: none; padding-left: 0; font-size: 0.88rem; line-height: 2;">
                        <li><a href="/" style="color: #475569; text-decoration: none;">All Study Materials</a></li>
                        <li><a href="/?class=JEE+Main+%26+Advanced" style="color: #475569; text-decoration: none;">JEE Main & Advanced</a></li>
                        <li><a href="/?class=NEET+(Medical)" style="color: #475569; text-decoration: none;">NEET Medical Prep</a></li>
                        <li><a href="/?class=AP+Physics+(College+Board)" style="color: #475569; text-decoration: none;">AP Physics 1 & C</a></li>
                        <li><a href="/?price=free" style="color: #10b981; font-weight: 700; text-decoration: none;"><i class="fa-solid fa-gift"></i> 100% Free Resources</a></li>
                        <li><a href="/cart" style="color: #475569; text-decoration: none;">Shopping Cart</a></li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Legal Bar -->
            <div style="border-top: 1px solid #f1f5f9; padding-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; font-size: 0.82rem; color: #94a3b8;">
                <div>
                    &copy; <?= date('Y') ?> <strong>Lockinrank</strong> (shop.lockinrank.com) &bull; 641004, Coimbatore, Tamil Nadu, India.
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <span style="color: #475569;"><i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Instant UPI & Download</span>
                    <span>&bull;</span>
                    <span style="color: #475569;"><i class="fa-solid fa-shield-halved" style="color: #4f46e5;"></i> Razorpay Verified Merchant</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function openPreviewModal() {
            document.getElementById('preview-modal').showModal();
        }
        function closePreviewModal() {
            document.getElementById('preview-modal').close();
        }
    </script>

    <!-- KaTeX Math Engine & Auto-Renderer -->
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js" crossorigin="anonymous" onload="renderLockinrankMath()"></script>
    <script>
        function renderLockinrankMath(rootEl) {
            const target = rootEl || document.body;
            if (typeof renderMathInElement === 'function') {
                renderMathInElement(target, {
                    delimiters: [
                        { left: '$$', right: '$$', display: true },
                        { left: '\\[', right: '\\]', display: true },
                        { left: '\\(', right: '\\)', display: false },
                        { left: '$', right: '$', display: false }
                    ],
                    ignoredTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code', 'option'],
                    throwOnError: false
                });
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            renderLockinrankMath();
        });
        document.addEventListener('htmx:afterSwap', function(evt) {
            renderLockinrankMath(evt.detail.target);
        });
    </script>
</body>
</html>
