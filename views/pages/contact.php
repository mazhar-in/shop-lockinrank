<?php 
$pageTitle = "Contact Us — Lockinrank Shop Support";
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
                    <input type="tel" id="contact_phone" name="phone" placeholder="+91 98765 43210" style="font-size: 0.9rem;">
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
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 2rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; color: #0f172a;">
                    <i class="fa-solid fa-headset" style="color: #0284c7;"></i> Direct Contact Channels
                </h3>

                <div style="display: flex; gap: 1rem; margin-bottom: 1.25rem; align-items: flex-start;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #eff6ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa-solid fa-envelope" style="color: #4f46e5;"></i>
                    </div>
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">Customer Support Email</strong>
                        <div style="color: #64748b; font-size: 0.88rem;">For order verification, link re-issue & inquiries:</div>
                        <a href="mailto:support@lockinrank.com" style="font-weight: 600; color: #4f46e5;">support@lockinrank.com</a>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-bottom: 1.25rem; align-items: flex-start;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #f0fdf4; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa-brands fa-whatsapp" style="color: #25d366; font-size: 1.2rem;"></i>
                    </div>
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">WhatsApp Support Helpline</strong>
                        <div style="color: #64748b; font-size: 0.88rem;">Instant chat support for download links:</div>
                        <a href="https://wa.me/919876543210" target="_blank" style="font-weight: 600; color: #16a34a;">+91 98765 43210</a>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; align-items: flex-start;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #f8fafc; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa-regular fa-clock" style="color: #64748b;"></i>
                    </div>
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">Working Hours</strong>
                        <div style="color: #64748b; font-size: 0.88rem;">Monday to Saturday: 9:00 AM – 6:30 PM IST</div>
                        <small style="color: #059669; font-weight: 600;">Response guaranteed within 24 hours</small>
                    </div>
                </div>
            </div>

            <!-- Registered Business Address Box (Mandatory for Razorpay & E-Commerce Compliance) -->
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; color: #0f172a;">
                    <i class="fa-solid fa-location-dot" style="color: #ef4444;"></i> Registered Business Address
                </h4>
                <p style="color: #64748b; font-size: 0.85rem; line-height: 1.6; margin-bottom: 0;">
                    <strong>Lockinrank</strong> (shop.lockinrank.com)<br>
                    Official Academic Resource Store<br>
                    India & International Courses<br>
                    Support: support@lockinrank.com
                </p>
            </div>
        </div>

    </div>

</div>

<?php include __DIR__ . '/../layout-footer.php'; ?>
