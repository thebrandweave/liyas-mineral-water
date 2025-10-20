<?php
/**
 * Contact Us component
 */
?>
<section class="contact-section" id="contact" data-aos="fade-up">
    <div class="contact-bg-fixed"></div>
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <div class="contact-eyebrow">Get In Touch</div>
            <h2 class="contact-title">Contact Us</h2>
            <p class="contact-subtext">We would like to hear from you.</p>
        </div>

        <form action="#" method="post" class="contact-form mt-4" data-aos="fade-up" data-aos-delay="50">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-12">
                            <input type="text" class="form-control contact-input" name="name" placeholder="Name" required>
                        </div>
                        <div class="col-12">
                            <input type="email" class="form-control contact-input" name="email" placeholder="Email" required>
                        </div>
                        <div class="col-12">
                            <input type="tel" class="form-control contact-input" name="phone" placeholder="Phone" required>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <textarea class="form-control contact-textarea h-100" name="message" placeholder="Message" rows="9" required></textarea>
                </div>
            </div>
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary px-5 contact-submit">Submit</button>
            </div>
        </form>
    </div>

    <style>
        .contact-section { 
            padding: 96px 0 0 0; 
            position: relative; 
            overflow: hidden;
            max-height: 100vh;
        }
        
        .contact-bg-fixed {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 120%;
            background: url('https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1600&q=80') center/cover no-repeat;
            transform: translateY(var(--scroll, 0px));
            transition: transform 0.1s ease-out;
            z-index: -1;
        }
        
        .contact-bg-fixed::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(240, 249, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
            z-index: 1;
        }
        
        .contact-eyebrow { color: #8aa0b2; font-weight: 500; letter-spacing: .4px; text-transform: none; font-size: 22px; }
        .contact-title { font-weight: 800; color: #0a2440; font-size: 44px; margin-top: 4px; }
        .contact-subtext { color: #7a8a99; font-size: 18px; }

        .contact-form { position: relative; z-index: 2; }
        .contact-form .form-control { border-radius: 8px; padding: 12px 14px; border: 1px solid #e9eef3; background: #fff; box-shadow: none; }
        .contact-input::placeholder, .contact-textarea::placeholder { color: #9fb0bf; }
        .contact-textarea { min-height: 100%; resize: vertical; }
        .contact-submit { background: #20b3f3; border-color: #20b3f3; }
        .contact-submit:hover { background: #19a6e3; border-color: #19a6e3; }
        .contact-wave { position: relative; z-index: 1; display: block; width: 100%;transform: scaleX(-1)}

        @media (max-width: 991.98px) {
            .contact-title { font-size: 36px; }
            .contact-section { padding: 72px 0 0 0; }
            .contact-wave { margin-top: -100px; }
        }
        @media (max-width: 575.98px) {
            .contact-title { font-size: 30px; }
        }
    </style>

    <svg class="contact-wave" viewBox="0 0 1440 490"><path d="M0,400 L0,225 C130.27,247.4 260.53,269.8 419,238 C577.47,206.2 764.13,120.2 939,84 C1113.87,47.8 1276.93,61.4 1440,75 L1440,400 L0,400 Z" fill="#fff"></path></svg>

    <!-- ✅ Added tiny script for scroll animation -->
    <script>
    document.addEventListener("scroll", function() {
        const section = document.querySelector(".contact-section");
        const bg = document.querySelector(".contact-bg-fixed");
        const rect = section.getBoundingClientRect();

        if (rect.top < window.innerHeight && rect.bottom > 0) {
            const scrollY = rect.top * -0.3; // Adjust speed
            bg.style.setProperty("--scroll", `${scrollY}px`);
        }
    });
    </script>
</section>
