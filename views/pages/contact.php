<?php 
$pageTitle = "Contact Us — Lockinrank Shop Support";
$pageDescription = "Contact Lockinrank academic support team for queries regarding JEE, NEET, or AP Physics study packages, order assistance, or downloads.";
$canonicalUrl = "https://shop.lockinrank.com/contact";
include __DIR__ . '/../layout-header.php'; 
?>

<div style="max-width: 900px; margin: 0 auto;">

    <div style="text-align: center; margin-bottom: 2.5rem;">
        <span class="chip" style="background-color: #4f46e5; margin-bottom: 0.5rem; display: inline-block;">Support & Inquiries</span>
        <h1 style="font-weight: 800; font-size: 2.2rem; margin-bottom: 0.5rem; color: #0f172a;">Contact Us</h1>
        <p style="color: #64748b; font-size: 1.05rem; max-width: 600px; margin: 0 auto;">
            Have questions about a problem pack, exam syllabus alignment, or need help retrieving your download links? We are here to help!
        </p>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fa-solid fa-circle-check" style="font-size: 1.3rem; color: #10b981;"></i>
            <div>
                <strong>Thank you! Your message has been received.</strong>
                <div style="font-size: 0.88rem;">Our educational support team will respond to your email/WhatsApp within 24 hours.</div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 2.5rem;">

        <!-- Contact Form -->
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; color: #0f172a;">
                <i class="fa-regular fa-paper-plane" style="color: #0284c7;"></i> Send Us a Message
            </h3>

            <form action="/contact" method="POST">
                <div style="margin-bottom: 1rem;">
                    <label for="contact_name" style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">Your Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="contact_name" name="name" placeholder="e.g. Mrs. Priya Sharma" required style="font-size: 0.9rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label for="contact_email" style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">Email Address <span style="color: #ef4444;">*</span></label>
                    <input type="email" id="contact_email" name="email" placeholder="priya@school.edu.in" required style="font-size: 0.9rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label for="contact_phone" style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">
                        <i class="fa-brands fa-whatsapp" style="color: #25d366;"></i> WhatsApp / Mobile Number (Optional)
                    </label>
                    <input type="tel" id="contact_phone" name="phone" placeholder="+91 99902 53219" style="font-size: 0.9rem;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label for="contact_subject" style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">Subject Area</label>
                    <select id="contact_subject" name="subject" style="font-size: 0.9rem;">
                        <option value="Download Issue">Help with Downloading Purchased Files</option>
                        <option value="Resource Query">Question about a Teaching Material / Curriculum</option>
                        <option value="Refund Request">Refund / Duplicate Transaction Query</option>
                        <option value="General Inquiry">General Feedback or Bulk School Licensing</option>
                    </select>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="contact_message" style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">Your Message <span style="color: #ef4444;">*</span></label>
                    <textarea id="contact_message" name="message" rows="4" placeholder="Please describe how we can assist you..." required style="font-size: 0.9rem;"></textarea>
                </div>

                <button type="submit" style="width: 100%; font-weight: 700; background-color: #0284c7; border-color: #0284c7;">
                    <i class="fa-solid fa-paper-plane"></i> Submit Inquiry
                </button>
            </form>
        </div>

        <!-- Direct Contact Details & Help Desk Info -->
        <div>
            <!-- WhatsApp Fast-Response Card -->
            <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #86efac; border-radius: 10px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.08);">
                <div style="display: flex; gap: 0.85rem; align-items: flex-start; margin-bottom: 0.85rem;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background-color: #25d366; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(37, 211, 102, 0.35);">
                        <i class="fa-brands fa-whatsapp" style="color: #ffffff; font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                            <strong style="color: #065f46; font-size: 1.05rem;">WhatsApp Support Desk</strong>
                            <span style="background: #059669; color: #ffffff; font-size: 0.68rem; font-weight: 800; padding: 0.1rem 0.45rem; border-radius: 12px; text-transform: uppercase;">Instant</span>
                        </div>
                        <div style="color: #047857; font-size: 0.88rem; margin-top: 0.15rem;">
                            Direct chat assistance for instant PDF links, UPI payment status &amp; academic queries.
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
                    <a href="https://wa.me/919990253219?text=Hello%20Lockinrank%20Support!%20I%20have%20an%20inquiry%20regarding%20study%20materials." target="_blank" rel="noopener noreferrer" style="background-color: #25d366; border-color: #25d366; color: #ffffff !important; font-weight: 800; font-size: 0.92rem; padding: 0.55rem 1.15rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; box-shadow: 0 2px 6px rgba(37, 211, 102, 0.3); margin-bottom: 0;">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.1rem;"></i> Chat on WhatsApp (+91 9990253219)
                    </a>
                </div>
            </div>

            <!-- Email & Operating Hours Card -->
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.15rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-headset" style="color: #4f46e5;"></i> Email &amp; Operating Hours
                </h3>

                <div style="display: flex; gap: 0.85rem; margin-bottom: 1.15rem; align-items: flex-start;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background-color: #eff6ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa-solid fa-envelope" style="color: #4f46e5;"></i>
                    </div>
                    <div>
                        <strong style="color: #0f172a; font-size: 0.92rem;">Customer Support Email</strong>
                        <div style="color: #64748b; font-size: 0.84rem;">For invoice verification, license re-issue &amp; institution bulk orders:</div>
                        <a href="mailto:support@lockinrank.com" style="font-weight: 700; color: #4f46e5; font-size: 0.92rem;">support@lockinrank.com</a>
                    </div>
                </div>

                <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background-color: #f8fafc; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa-regular fa-clock" style="color: #64748b;"></i>
                    </div>
                    <div>
                        <strong style="color: #0f172a; font-size: 0.92rem;">Academic Help Desk Hours</strong>
                        <div style="color: #64748b; font-size: 0.84rem;">Monday – Saturday: 9:00 AM – 6:30 PM IST</div>
                        <small style="color: #059669; font-weight: 700;">Guaranteed response within 24 hours</small>
                    </div>
                </div>
            </div>

            <!-- Registered Business Address Box (Mandatory for Razorpay & E-Commerce Compliance) -->
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background-color: #fee2e2; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa-solid fa-location-dot" style="color: #ef4444;"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 0.98rem; font-weight: 800; margin: 0 0 0.35rem 0; color: #0f172a;">
                            Registered Business Address
                        </h4>
                        <p style="color: #334155; font-size: 0.88rem; line-height: 1.6; margin-bottom: 0.5rem;">
                            <strong>Lockinrank</strong> (shop.lockinrank.com)<br>
                            641004, Coimbatore, Tamil Nadu, India.
                        </p>
                        <div style="display: flex; gap: 0.75rem; font-size: 0.8rem; color: #64748b; flex-wrap: wrap;">
                            <span><i class="fa-solid fa-shield-check" style="color: #10b981;"></i> Razorpay Verified Merchant</span>
                            <span>&bull;</span>
                            <span><i class="fa-solid fa-flag" style="color: #f59e0b;"></i> Tamil Nadu, India</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include __DIR__ . '/../layout-footer.php'; ?>
