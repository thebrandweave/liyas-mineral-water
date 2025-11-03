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

    <svg class="wave-svg" width="100%" height="100%" id="svg" viewBox="0 0 1440 390" xmlns="http://www.w3.org/2000/svg" class="transition duration-300 ease-in-out delay-150">
            <style>
                .path-0{
                    animation:pathAnim-0 4s;
                    animation-timing-function: linear;
                    animation-iteration-count: infinite;
                }
                @keyframes pathAnim-0{
                    0%{
                        d: path("M 0,400 L 0,150 C 94.73076923076925,117.93589743589743 189.4615384615385,85.87179487179488 277,76 C 364.5384615384615,66.12820512820512 444.88461538461536,78.44871794871794 507,92 C 569.1153846153846,105.55128205128206 613.0000000000001,120.33333333333333 694,122 C 774.9999999999999,123.66666666666667 893.1153846153843,112.21794871794873 986,128 C 1078.8846153846157,143.78205128205127 1146.5384615384617,186.7948717948718 1218,195 C 1289.4615384615383,203.2051282051282 1364.730769230769,176.6025641025641 1440,150 L 1440,400 L 0,400 Z");
                    }
                    25%{
                        d: path("M 0,400 L 0,150 C 75.4897435897436,175.26666666666665 150.9794871794872,200.53333333333333 243,182 C 335.0205128205128,163.46666666666667 443.57179487179485,101.13333333333334 521,90 C 598.4282051282051,78.86666666666666 644.7333333333333,118.93333333333334 714,139 C 783.2666666666667,159.06666666666666 875.4948717948719,159.13333333333333 955,153 C 1034.5051282051281,146.86666666666667 1101.2871794871794,134.53333333333333 1180,133 C 1258.7128205128206,131.46666666666667 1349.3564102564103,140.73333333333335 1440,150 L 1440,400 L 0,400 Z");
                    }
                    50%{
                        d: path("M 0,400 L 0,150 C 62.51794871794871,169.57179487179485 125.03589743589743,189.14358974358973 204,184 C 282.9641025641026,178.85641025641027 378.374358974359,148.99743589743588 461,152 C 543.625641025641,155.00256410256412 613.4666666666667,190.8666666666667 695,172 C 776.5333333333333,153.1333333333333 869.7589743589742,79.53589743589743 947,78 C 1024.2410256410258,76.46410256410257 1085.497435897436,146.9897435897436 1165,171 C 1244.502564102564,195.0102564102564 1342.251282051282,172.5051282051282 1440,150 L 1440,400 L 0,400 Z");
                    }
                    75%{
                        d: path("M 0,400 L 0,150 C 65.39743589743588,127.30000000000001 130.79487179487177,104.60000000000001 219,110 C 307.20512820512823,115.39999999999999 418.2179487179486,148.9 502,166 C 585.7820512820514,183.1 642.3333333333335,183.79999999999998 725,186 C 807.6666666666665,188.20000000000002 916.448717948718,191.9 991,199 C 1065.551282051282,206.1 1105.871794871795,216.6 1175,209 C 1244.128205128205,201.4 1342.0641025641025,175.7 1440,150 L 1440,400 L 0,400 Z");
                    }
                    100%{
                        d: path("M 0,400 L 0,150 C 94.73076923076925,117.93589743589743 189.4615384615385,85.87179487179488 277,76 C 364.5384615384615,66.12820512820512 444.88461538461536,78.44871794871794 507,92 C 569.1153846153846,105.55128205128206 613.0000000000001,120.33333333333333 694,122 C 774.9999999999999,123.66666666666667 893.1153846153843,112.21794871794873 986,128 C 1078.8846153846157,143.78205128205127 1146.5384615384617,186.7948717948718 1218,195 C 1289.4615384615383,203.2051282051282 1364.730769230769,176.6025641025641 1440,150 L 1440,400 L 0,400 Z");
                    }
                }
            </style>
            <path d="M 0,400 L 0,150 C 94.73076923076925,117.93589743589743 189.4615384615385,85.87179487179488 277,76 C 364.5384615384615,66.12820512820512 444.88461538461536,78.44871794871794 507,92 C 569.1153846153846,105.55128205128206 613.0000000000001,120.33333333333333 694,122 C 774.9999999999999,123.66666666666667 893.1153846153843,112.21794871794873 986,128 C 1078.8846153846157,143.78205128205127 1146.5384615384617,186.7948717948718 1218,195 C 1289.4615384615383,203.2051282051282 1364.730769230769,176.6025641025641 1440,150 L 1440,400 L 0,400 Z" stroke="none" stroke-width="0" fill="#ffffff" fill-opacity="1" class="transition-all duration-300 ease-in-out delay-150 path-0"></path>
        </svg>

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
