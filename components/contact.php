<?php
/**
 * Contact Us component - Fully Responsive
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
            padding: var(--space-xxxl) 0 0 0; 
            position: relative; 
            overflow: hidden;
            min-height: 100vh;
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
        
        .contact-eyebrow { 
            color: var(--secondary); 
            font-weight: 500; 
            letter-spacing: .4px; 
            text-transform: none; 
            font-size: var(--text-lg);
            margin-bottom: var(--space-xs);
        }
        
        .contact-title { 
            font-weight: 800; 
            color: var(--dark); 
            font-size: clamp(var(--text-3xl), 5vw, var(--text-5xl));
            margin-top: var(--space-xs);
            margin-bottom: var(--space-sm);
        }
        
        .contact-subtext { 
            color: var(--secondary); 
            font-size: var(--text-lg);
            margin-bottom: var(--space-xl);
        }

        .contact-form { 
            position: relative; 
            z-index: 2; 
            max-width: 800px;
            margin: 0 auto;
        }
        
        .contact-form .form-control { 
            border-radius: 8px; 
            padding: var(--space-sm) var(--space-md); 
            border: 1px solid #e9eef3; 
            background: var(--white); 
            box-shadow: none; 
            font-size: var(--text-base);
            transition: all 0.3s ease;
        }
        
        .contact-form .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
            outline: none;
        }
        
        .contact-input::placeholder, .contact-textarea::placeholder { 
            color: var(--secondary); 
        }
        
        .contact-textarea { 
            min-height: 100%; 
            resize: vertical; 
        }
        
        .contact-submit { 
            background: var(--primary); 
            border-color: var(--primary); 
            padding: var(--space-sm) var(--space-xl);
            font-size: var(--text-base);
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .contact-submit:hover { 
            background: var(--primary-dark); 
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(14, 165, 233, 0.3);
        }
        
        .contact-wave { 
            position: relative; 
            z-index: 1; 
            display: block; 
            width: 100%;
            transform: scaleX(-1);
        }

        /* Responsive Styles */
        @media (max-width: 767px) {
            .contact-section {
                padding: var(--space-xxl) 0 0 0;
                min-height: auto;
            }
            
            .contact-title { 
                font-size: clamp(var(--text-2xl), 6vw, var(--text-3xl));
            }
            
            .contact-subtext {
                font-size: var(--text-base);
            }
            
            .contact-eyebrow {
                font-size: var(--text-base);
            }
            
            .contact-form .form-control {
                padding: var(--space-sm);
                font-size: var(--text-sm);
            }
            
            .contact-submit {
                width: 100%;
                max-width: 300px;
                padding: var(--space-sm) var(--space-lg);
            }
            
            .contact-wave { 
                margin-top: -50px; 
            }
        }
        
        @media (min-width: 768px) and (max-width: 991px) {
            .contact-section {
                padding: var(--space-xxxl) 0 0 0;
            }
            
            .contact-title { 
                font-size: clamp(var(--text-4xl), 4vw, var(--text-5xl));
            }
            
            .contact-wave { 
                margin-top: -80px; 
            }
        }

        @media (min-width: 992px) {
            .contact-section {
                padding: var(--space-xxxl) 0 0 0;
            }
            
            .contact-title { 
                font-size: clamp(var(--text-4xl), 3vw, var(--text-5xl));
            }
            
            .contact-wave { 
                margin-top: -100px; 
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            .contact-bg-fixed {
                transition: none;
            }
            
            .contact-form .form-control,
            .contact-submit {
                transition: none;
            }
        }

        /* Focus styles */
        .contact-form .form-control:focus,
        .contact-submit:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        /* High contrast mode */
        @media (prefers-contrast: high) {
            .contact-form .form-control {
                border-color: #000000;
            }
            
            .contact-form .form-control:focus {
                border-color: #0066cc;
            }
        }
    </style>

    <svg class="contact-wave" viewBox="0 0 1440 490">
        <path d="M0,400 L0,225 C130.27,247.4 260.53,269.8 419,238 C577.47,206.2 764.13,120.2 939,84 C1113.87,47.8 1276.93,61.4 1440,75 L1440,400 L0,400 Z" fill="#fff"></path>
    </svg>

    <!-- Responsive scroll animation script -->
    <script>
    document.addEventListener("scroll", function() {
        const section = document.querySelector(".contact-section");
        const bg = document.querySelector(".contact-bg-fixed");
        
        if (!section || !bg) return;
        
        const rect = section.getBoundingClientRect();

        if (rect.top < window.innerHeight && rect.bottom > 0) {
            const scrollY = rect.top * -0.3; // Adjust speed
            bg.style.setProperty("--scroll", `${scrollY}px`);
        }
    });
    </script>
</section>
