<?php
/**
 * Contact Us component
 */
?>
<section class="contact-section" id="contact" data-aos="fade-up">
    <div class="container">
        <div class="contact-title fade-in" data-aos="fade-up">
            <div class="contact-subtitle">CONTACT US</div>
            <h2 class="contact-heading">WE'D LOVE TO HEAR FROM YOU</h2>
            <p class="contact-intro">Reach out for orders, queries, or feedback. We typically respond within a few hours.</p>
        </div>

        <div class="row g-4 mt-2">
            <div class="col-lg-6" data-aos="fade-right" data-aos-delay="50">
                <div class="contact-card product-card p-4 h-100">
                    <form action="#" method="post" class="contact-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" placeholder="Your Phone" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" name="message" rows="4" placeholder="How can we help?" required></textarea>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Send Message</button>
                                <a href="https://wa.me/1234567890" target="_blank" class="btn btn-success d-inline-flex align-items-center">
                                    <i class="fab fa-whatsapp me-2"></i> WhatsApp Order
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="100">
                <div class="contact-card product-card p-4 h-100">
                    <div class="d-flex align-items-start gap-3 flex-wrap">
                        <div>
                            <div class="fw-semibold text-uppercase small text-primary">Address</div>
                            <div>123, Water Street, City, 560001</div>
                        </div>
                        <div>
                            <div class="fw-semibold text-uppercase small text-primary">Phone</div>
                            <div><a href="tel:+911234567890" class="link-dark text-decoration-none">+91 12345 67890</a></div>
                        </div>
                        <div>
                            <div class="fw-semibold text-uppercase small text-primary">Email</div>
                            <div><a href="mailto:hello@liyaswater.com" class="link-dark text-decoration-none">hello@liyaswater.com</a></div>
                        </div>
                    </div>
                    <div class="ratio ratio-16x9 mt-3 rounded overflow-hidden">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3153.019324!2d-122.4194155!3d37.7749295!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzfCsDQ2JzMwLjciTiAxMjLCsDI1JzA5LjkiVw!5e0!3m2!1sen!2sus!4v1710000000000"
                            style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .contact-section { padding: 80px 0; background: #ffffff; }
        .contact-subtitle { color: #00a2ed; font-weight: 600; letter-spacing: 1px; margin-bottom: 8px; }
        .contact-heading { font-weight: 700; color: #0b2e4e; }
        .contact-intro { color: #5a6b7b; max-width: 720px; }
        .contact-card { border: 1px solid rgba(0,0,0,0.05); border-radius: 16px; background: #fff; transition: transform .3s ease, box-shadow .3s ease; }
        .contact-card:hover { transform: translateY(-6px); box-shadow: 0 16px 30px rgba(0, 122, 212, 0.12); }
        .contact-form .form-control { padding: 10px 12px; border-radius: 10px; }
        @media (max-width: 767.98px) { .contact-section { padding: 56px 0; } }
    </style>
</section>


