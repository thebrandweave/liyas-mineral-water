<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>LIYAS Mineral Water - Premium Quality Water</title>
    <meta name="description" content="LIYAS Mineral Water - Premium quality mineral water for a healthy lifestyle. Pure, refreshing, and naturally sourced.">
    <meta name="keywords" content="mineral water, premium water, healthy water, LIYAS, pure water">
    
    <link rel="icon" type="image/x-icon" href="assets/images/logo/logo.png">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/about.css">
    <link rel="stylesheet" href="assets/css/product.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    
    <meta name="theme-color" content="#0ea5e9">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="LIYAS Water">
    
    <meta property="og:title" content="LIYAS Mineral Water - Premium Quality Water">
    <meta property="og:description" content="Premium quality mineral water for a healthy lifestyle. Pure, refreshing, and naturally sourced.">
    <meta property="og:image" content="assets/images/logo/logo.png">
    <meta property="og:url" content="https://liyas-water.com">
    <meta property="og:type" content="website">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="LIYAS Mineral Water - Premium Quality Water">
    <meta name="twitter:description" content="Premium quality mineral water for a healthy lifestyle. Pure, refreshing, and naturally sourced.">
    <meta name="twitter:image" content="assets/images/logo/logo.png">
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "LIYAS Mineral Water",
        "description": "Premium quality mineral water for a healthy lifestyle",
        "url": "https://liyas-water.com",
        "logo": "assets/images/logo/logo.png",
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+1-234-567-8900",
            "contactType": "customer service"
        }
    }
    </script>
<body>
    <style>
        /* Splash Screen Styles */
        #splash-screen {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100vh;
            z-index: 9999;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background:transparent;
        }

        .splash-layer {
            position: absolute;
            top: 0%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0;
            height: 0;
            border-radius: 50%;
            background:rgba(14, 164, 233, 0.22);
            will-change: width, height, transform;
            animation: circleDropExpand 2.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }


        /* Logo container in the center (GSAP will manage the move, so no CSS transition) */
        .logo-text {
            position: fixed;
            top: 50%;
            left: 50%;
            /* The initial centering (translate) is managed by GSAP.set() in JS */
            font-size: clamp(2rem, 8vw, 4rem);
            font-weight: 700;
            color: rgba(14, 164, 233, 0.71);
            z-index: 10000; /* Increased z-index to ensure visibility during splash fade */
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            pointer-events: none;
            font-family: 'Poppins', sans-serif;
            /* REMOVED: transition: all 1s ease; */
        }

        /* Final state after GSAP animation completes. This ensures responsiveness. */
        .logo-text.move-top-right {
            /* The logo moves to 20px from top and 20px from right */
            top: 20px !important; 
            left: auto !important; /* Important to override GSAP's final 'left' property */
            right: 20px !important; 
            transform: none !important;  /* Ensure no residual transform affects positioning */
            /* Optional: Apply final size for responsiveness */
            font-size: clamp(1rem, 4vw, 1.5rem);
        }
        
        /* Responsive Logo Image */
        .logo-text img {
            width: clamp(60px, 15vw, 100px);
            height: clamp(60px, 15vw, 100px);
        }

        /* --- Existing Animations Below --- */
        .logo-text span {
            display: inline-block;
            position: relative;
            top: 0;
            transition: top 0.2s ease;
        }

        .math-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            pointer-events: none;
            perspective: 1000px;
        }
        
        /* ... (other math-particles and keyframes remain the same) ... */

        @keyframes circleDropExpand {
            0% { width: 0; height: 0; border-radius: 50%; transform: translate(-50%, -80%); opacity: .98; }
            60% { width: 60vmax; height: 60vmax; border-radius: 50%; transform: translate(-50%, -60%); }
            100% { width: 160vmax; height: 160vmax; border-radius: 50%; transform: translate(-50%, -50%); }
        }

        .splash-fadeout {
            animation: splashFadeOut 0.8s cubic-bezier(0.215, 0.610, 0.355, 1.000) forwards;
        }
        
        @keyframes splashFadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; visibility: hidden; transform: scale(1.05); }
        }

        /* Hero layout padding to avoid overlap with scroll control */
        .hero { 
            position: relative; 
            padding-bottom: 120px; /* space reserved for scroll control */
        }

        @media (min-width: 768px) {
            .hero { padding-bottom: 140px; }
        }

        /* Hero Scroll Down Button - Circular Text Design */
        .hero-scroll-down {
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
            cursor: pointer;
            width: 150px;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-scroll-down svg {
            width: 150px;
            height: 150px;
            transition: transform 1s cubic-bezier(0.65, 0, 0.35, 1);
        }

        .hero-scroll-down:hover svg .textcircle {
            transform: scale(1.2) rotate(90deg);
        }

        .hero-scroll-down .textcircle {
            transition: transform 1s cubic-bezier(0.65, 0, 0.35, 1);
            transform-origin: 250px 250px;
        }

        .hero-scroll-down text {
            font-size: 28px;
            font-family: "Poppins", sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            fill: rgba(14, 165, 233, 1);
            animation: rotate 25s linear infinite;
            transform-origin: 250px 250px;
        }

        .hero-scroll-down .center-arrow {
            transform-origin: 250px 250px;
            transition: transform 0.3s ease;
        }

        .hero-scroll-down:hover .center-arrow {
            transform: translateY(5px);
        }

        @keyframes rotate {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .hero-scroll-down .center-arrow {
            animation: bounce 2s infinite;
        }

        @media (max-width: 767px) {
            .hero-scroll-down {
                bottom: 12px; /* a bit lower on small screens */
                width: 120px;
                height: 120px;
            }
            
            .hero-scroll-down svg {
                width: 120px;
                height: 120px;
            }

            .hero-scroll-down text {
                font-size: 20px;
            }
        }

        /* Marquee Animation Styles - Overlay Version */
        .marquees-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 10;
            pointer-events: none;
        }

        .about-images {
            position: relative;
        }

        .marquee {
            --marquee--colour: rgba(14, 165, 233, 0.9);
            --marquee--repeat-count: 6;
            --marquee--base-duration: 1s;
            --marquee--repeat-size: calc(100% / var(--marquee--repeat-count));
            --marquee--double-size: calc(var(--marquee--repeat-size) * 2);
            --marquee--duration: calc(
                var(--marquee--base-duration) * var(--char-count, 20)
            );
            overflow: hidden;
            width: 110%;
            margin-left: -5%;
            mix-blend-mode: overlay;
            transform: rotate(-2deg);
            background: var(--marquee--colour);
            color: #fff;
            margin: 10px 0;
            backdrop-filter: blur(2px);
        }

        .marquee:nth-child(even) {
            --marquee--direction: -1;
            transform: rotate(2deg);
            background: rgba(255, 255, 255, 0.9);
            color: var(--marquee--colour);
        }

        .marquee p {
            transform: translateY(0.07em);
            font-weight: bold;
            margin: 0;
            display: flex;
            gap: 0.5em;
            line-height: 1.1;
            font-size: clamp(1.2rem, 4vw, 2.5rem);
            font-family: "Poppins", sans-serif;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .marquee p::after {
            content: "•";
            transform: translateY(0.175em);
            color: rgba(14, 165, 233, 0.6);
        }

        .marquee p::before {
            content: "";
        }

        .marquee--inner {
            width: max-content;
            display: flex;
            text-transform: uppercase;
        }

        @media (prefers-reduced-motion: no-preference) {
            .marquee--inner {
                animation: marquee var(--marquee--duration) infinite linear, reduce-marquee var(--marquee--duration) infinite linear paused;
                animation-composition: add;
            }
            .marquee--inner:hover {
                animation-play-state: running;
            }
        }

        @keyframes marquee {
            from {
                transform: translateX(calc(var(--marquee--double-size) * -1));
            }
            to {
                transform: translateX(calc(
                    (-1 * var(--marquee--double-size)) - 
                    (var(--marquee--double-size) * var(--marquee--direction, 1))
                ));
            }
        }

        @keyframes reduce-marquee {
            from {
                transform: translateX(calc(var(--marquee--repeat-size) * var(--marquee--direction, 1)));
            }
            to {
                transform: translateX(calc(
                    (-1 * var(--marquee--double-size)) - 
                    (var(--marquee--double-size) * var(--marquee--direction, 1))
                ));
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .marquees {
                padding: 40px 0;
                margin: 20px 0;
            }
            
            .marquee {
                transform: rotate(-1deg);
            }
            
            .marquee:nth-child(even) {
                transform: rotate(1deg);
            }
            
            .marquee p {
                font-size: clamp(1.5rem, 6vw, 2.5rem);
            }
        }

        /* Hero image placement rules */
        .hero-image-mobile {
            display: none;
            margin: var(--space-md, 1.5rem) 0;
            text-align: center;
        }

        .hero-image-desktop {
            display: block;
        }

        @media (max-width: 767px) {
            .hero-image-mobile { display: block; }
            .hero-image-desktop { display: none; }
        }

        @media (min-width: 768px) {
            .hero-image-mobile { display: none; }
            .hero-image-desktop { display: block; }
        }

        /* Social Sidebar - match circular white buttons look */
        .social-sidebar {
            position: fixed;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 18px;
            z-index: 1000;
        }

        .social-sidebar .social-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #ffffff;
            color: #0f172a; /* dark icon color */
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 10px 24px rgba(2, 8, 23, 0.12);
            border: 1px solid rgba(2, 8, 23, 0.06);
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
            backdrop-filter: blur(6px);
        }

        .social-sidebar .social-icon i { font-size: 20px; }

        .social-sidebar .social-icon:hover {
            background: var(--primary);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(14, 165, 233, 0.25);
            border-color: rgba(14, 165, 233, 0.35);
        }

        @media (max-width: 767px) {
            .social-sidebar { display: none; }
        }

    </style>

    <div id="splash-screen" aria-hidden="true">
        <div class="splash-layer" aria-hidden="true"></div>
        <div class="logo-text"> 
            <div class="row">
            <img style="width: 100px; height: 100px;" src="assets/images/logo/logo.png" alt="Liyas">
            </div>
        </div>
        <div class="math-particles" aria-hidden="true" id="mathParticles"></div>
    </div>

    <div class="social-sidebar">
        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-x-twitter"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
    </div>
    
    <button id="backToTop" title="Go to top"><i class="fas fa-arrow-up"></i></button>

    <section class="hero" id="home" data-aos="fade-up">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="hero-content" data-aos="fade-right" data-aos-delay="50">
                        <div class="hero-subtitle">PURE WATER</div>
                        <h1>DRINK BETTER</h1>
                        <div class="hero-image-mobile" aria-hidden="false">
                            <img src="assets/images/water.png" alt="Pure Water" class="img-fluid animated-image">
                        </div>
                        <p>
                        Meet Liyas — hydration redefined for today’s lifestyle.
Crafted for clarity, freshness, and a touch of fun.
Because great water isn’t just a choice — it’s a vibe.
                        </p>
                        <div class="hero-buttons">
                            <a href="#" class="btn-primary">Shop Now</a>
                            <a href="#about" class="btn-secondary">Explore</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image hero-image-desktop" data-aos="fade-left" data-aos-delay="100">
                        <img src="assets/images/water.png" alt="Pure Water" class="img-fluid animated-image">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="hero-scroll-down" id="heroScrollDown" title="Scroll to next section">
            <svg xmlns="http://www.w3.org/2000/svg" xml:lang="en" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 500 500">
                <defs>
                    <path id="textcirclenew" d="M250,400 a150,150 0 0,1 0,-300a150,150 0 0,1 0,300Z" 
                            transform="rotate(12,250,250)"/>
                </defs>
                <g class="textcircle">
                    <text style="font-size: 55px;">
                        <textPath 
                                     xlink:href="#textcirclenew" 
                                     aria-label="Scroll Down" 
                                     textLength="880">
                            Scroll Down
                        </textPath>
                    </text>
                </g>
                <g class="center-arrow">
                    <path d="M250,200 L250,280 M250,280 L230,260 M250,280 L270,260" 
                            fill="none" 
                            stroke="rgba(14, 165, 233, 1)" 
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                </g>
            </svg>
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
    </section>

    <section class="about-section" id="about" data-aos="fade-up">
        <div class="container">
            <div class="about-title" data-aos="fade-up">
                <div class="about-subtitle">PURE NATURAL WATER</div>
                <h2 class="about-heading">BEAUTIFUL WATER WITH BEAUTIFUL CARE & UNIQUE QUALITY</h2>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="about-images" data-aos="zoom-in" data-aos-delay="50">
                        <img src="assets/images/bottle-1.jpg" alt="Premium Water" class="about-image-main">
                        <img src="assets/images/bottle-1.jpg" alt="Natural Water" class="about-image-secondary">
                        
                        <div class="marquees-overlay">
                            <section class="marquee" style="--char-count: 25">
                                <div class="marquee--inner">
                                    <p>Pure Water Pure Life</p>
                                    <p aria-hidden="true">Pure Water Pure Life</p>
                                    <p aria-hidden="true">Pure Water Pure Life</p>
                                </div>
                            </section>
                            <section class="marquee" style="--char-count: 25">
                                <div class="marquee--inner">
                                    <p>Hydration Excellence</p>
                                    <p aria-hidden="true">Hydration Excellence</p>
                                    <p aria-hidden="true">Hydration Excellence</p>
                                </div>
                            </section>
                            <section class="marquee" style="--char-count: 25">
                                <div class="marquee--inner">
                                    <p>Quality You Can Trust</p>
                                    <p aria-hidden="true">Quality You Can Trust</p>
                                    <p aria-hidden="true">Quality You Can Trust</p>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-content" data-aos="fade-left" data-aos-delay="100">
                        <div class="about-text">
                            <p>We enjoy providing pure, natural water and helping clients stay healthy and hydrated. AquaPure has an extraordinary team of excellently trained and certified professionals who ensure the highest quality standards.</p>
                            <p>Our water is sourced from protected mountain springs, carefully filtered, and bottled in our state-of-the-art facility. We take pride in delivering not just water, but a premium hydration experience.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'components/whychooseus.php'; ?>

    <section class="product-section container" data-aos="fade-up">
        <h2>CHOOSE YOUR <span>WATER</span></h2>
        <p>BOTTLES WE DELIVER</p>
        <div class="row g-4 justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card product-card position-relative p-3" data-aos="fade-up" data-aos-delay="0">
                    <img src="assets/images/bottle-1.jpg" alt="3 Bottles">
                    <div class="card-body">
                        <h5 class="card-title mt-3">Three bottles of mineral water</h5>
                        <p class="price">₹13.25</p>
                        <button class="btn btn-outline-primary">Add to cart</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card product-card p-3" data-aos="fade-up" data-aos-delay="100">
                    <img src="assets/images/bottle-1.jpg" alt="1 Big Bottle">
                    <div class="card-body">
                        <h5 class="card-title mt-3">One big bottle of mineral water</h5>
                        <p class="price">₹10</p>
                        <button class="btn btn-outline-primary">Add to cart</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card product-card p-3" data-aos="fade-up" data-aos-delay="200">
                    <img src="assets/images/bottle-1.jpg" alt="Small Bottles">
                    <div class="card-body">
                        <h5 class="card-title mt-3">Small bottles of mineral water</h5>
                        <p class="price">₹10</p>
                        <button class="btn btn-outline-primary">Add to cart</button>
                    </div>
                </div>
            </div>
        </div>
        <button class="btn-view-more mt-5">View More</button>
    </section>

    <?php include 'components/order-delivery.php'; ?>

    <?php include 'components/contact.php'; ?>
    <?php include 'components/sustainability.php'; ?>
    <?php include 'components/cta.php'; ?>



    <?php include 'components/footer.php'; ?>

    <script src="assets/js/script.js"></script>
    
    <?php include 'components/hollow-text.php'; ?>

    <script>

    // Splash screen functionality
    document.addEventListener('DOMContentLoaded', function() {
        const splash = document.getElementById('splash-screen');
        const mathParticles = document.getElementById('mathParticles');
        const logo = document.querySelector('.logo-text'); 

        // 1. GSAP Initialization: Set the initial state for the logo (Centered)
        // This ensures GSAP knows the starting point, applying the CSS transform.
        gsap.set(logo, { x: '-50%', y: '-50%' });

        createMathParticles();

        function createMathParticles() {
            const particleCount = 24;
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'math-particle';
                const x = Math.random() * 100;
                const y = Math.random() * 100;
                const delay = Math.random() * 2;
                const xOffset = Math.random() * 200 - 100;
                const zOffset = Math.random() * 300;
                particle.style.left = `${x}%`;
                particle.style.top = `${y}%`;
                particle.style.animationDelay = `${delay}s`;
                particle.style.setProperty('--x-offset', `${xOffset}px`);
                particle.style.setProperty('--z-offset', `${zOffset}px`);
                mathParticles.appendChild(particle);
            }
        }

        document.body.style.overflow = 'hidden';
        const layer = document.querySelector('.splash-layer');
        const fallbackTimeout = setTimeout(finishSplash, 500); 
        layer.addEventListener('animationend', finishSplash);

        function finishSplash() {
            clearTimeout(fallbackTimeout);
            layer.removeEventListener('animationend', finishSplash);

            // Time after the circle animation ends (2.8s total duration for circleDropExpand).
            const delayBeforeLogoMove = 0.5; // Start moving 0.5s after the main splash circle effect

            // 1. GSAP Logo Animation (Center to Top Right)
            // Ensure the logo is outside the splash so it won't be hidden when splash disappears
            if (splash.contains(logo)) {
                document.body.appendChild(logo);
            }
            gsap.to(logo, {
                duration: 1.2,
                delay: delayBeforeLogoMove,
                top: 20,              // Final distance from top (pixels)
                left: 'calc(100% - 120px)', // Final distance from left (100% minus 100px logo width minus 20px right margin)
                x: 0,                 // Reset X translation (must be 0 when setting fixed left/top)
                y: 0,                 // Reset Y translation
                ease: "power2.inOut",
                onStart: () => {
                    // Start the splash screen fadeout concurrently
                    splash.classList.add('splash-fadeout');
                    // Hide splash after fade-out completes (0.8s)
                    setTimeout(() => {
                        splash.style.display = 'none';
                        document.body.style.overflow = '';
                    }, 850);
                },
                onComplete: () => {
                    // Apply final CSS class for responsive position locking (clean up GSAP's inline styles)
                    logo.classList.add('move-top-right');
                    gsap.set(logo, { clearProps: "left, top, x, y" }); 
                }
            });
        }

    });
    
    // AOS init with custom premium easing and responsive settings
    document.addEventListener("DOMContentLoaded", function() {
        const isMobile = window.innerWidth <= 768;

        // Define a custom premium easing curve for smooth deceleration
        const premiumEasing = 'cubic-bezier(0.23, 1, 0.320, 1)'; 

        if (isMobile) {
            // Mobile: faster, simpler animations for performance
            AOS.init({ 
                duration: 800, 
                easing: 'ease-out', 
                once: true, 
                offset: 50 
            });
        } else {
            // Tablet/Desktop: longer duration and custom easing for a premium feel
            AOS.init({ 
                duration: 1200, 
                easing: premiumEasing, 
                once: true, 
                offset: 120, // Wait slightly longer for elements to enter view
            });
        }
        
        // Performance optimization for mobile
        if (isMobile) {
            // Disable some animations on mobile for better performance
            document.querySelectorAll('.floating-element').forEach(el => {
                el.style.display = 'none';
            });
            
            // Reduce particle count on mobile
            document.querySelectorAll('.particle').forEach(el => {
                el.style.display = 'none';
            });
        }
        
        // Touch-friendly interactions
        if ('ontouchstart' in window) {
            // Add touch-friendly classes
            document.body.classList.add('touch-device');
            
            // Improve touch scrolling
            document.body.style.webkitOverflowScrolling = 'touch';
        }
        
        // Responsive form handling
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                // Ensure touch-friendly input sizes
                if (input.type === 'text' || input.type === 'email' || input.type === 'tel') {
                    input.style.minHeight = '44px';
                    input.style.fontSize = '16px'; // Prevents zoom on iOS
                }
            });
        });
    });

    // Back to Top Button
    const backToTop = document.getElementById("backToTop");
    window.addEventListener("scroll", () => {
        backToTop.style.display = window.scrollY > 300 ? "flex" : "none";
    });
    backToTop.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));

    // Hero Scroll Down Button
    const heroScrollDown = document.getElementById("heroScrollDown");
    heroScrollDown.addEventListener("click", () => {
        const aboutSection = document.getElementById("about");
        aboutSection.scrollIntoView({ behavior: "smooth" });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>