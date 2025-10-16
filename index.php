<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Liyas-mineral Water</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/about.css">
    <link rel="stylesheet" href="assets/css/product.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
</head>
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
            font-size: 60px;
            font-weight: 700;
            color: rgba(14, 164, 233, 0.71);
            z-index: 10000; /* Increased z-index to ensure visibility during splash fade */
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            pointer-events: none;
            /* REMOVED: transition: all 1s ease; */
        }

        /* Final state after GSAP animation completes. This ensures responsiveness. */
        .logo-text.move-top-right {
            /* The logo moves to 20px from top and 20px from right */
            top: 20px !important; 
            left: auto !important; /* Important to override GSAP's final 'left' property */
            right: 20px !important; 
           transform: none !important;  /* Ensure no residual transform affects positioning */
            /* Optional: Apply final size for responsiveness */
        }
        
        .logo-text img {
            width: 100px;
            height: 100px;
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
                        <h1>DELIVERY SERVICE</h1>
                        <p>We now deliver different types of bottled water. To drink the best water please come to us and give us an order and take safe and sound water for you.</p>
                        <div class="hero-buttons">
                            <a href="#" class="btn-primary">Order Now</a>
                            <a href="#about" class="btn-secondary">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image" data-aos="fade-left" data-aos-delay="100">
                        <img src="assets/images/water.png" alt="Pure Water" class="img-fluid animated-image">
                    </div>
                </div>
            </div>
        </div>
        <svg class="wave-svg" viewBox="0 0 1440 490"><path d="M0,400 L0,225 C130.27,247.4 260.53,269.8 419,238 C577.47,206.2 764.13,120.2 939,84 C1113.87,47.8 1276.93,61.4 1440,75 L1440,400 L0,400 Z" fill="#ffffff"></path></svg>
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
                    <span class="badge-sale">SALE</span>
                    <img src="assets/images/bottle-1.jpg" alt="3 Bottles">
                    <div class="card-body">
                        <h5 class="card-title mt-3">Three bottles of mineral water</h5>
                        <div class="rating">★★★★★</div>
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
                        <div class="rating">★★★★★</div>
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
                        <div class="rating">★★★★★</div>
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

    <?php include 'components/hollow-text.php'; ?>

    <?php include 'components/footer.php'; ?>

    <script src="assets/js/script.js"></script>
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
                top: 20,              // Final distance from top (pixels)
                left: 'calc(100% - 120px)', // Final distance from left (100% minus 100px logo width minus 20px right margin)
                x: 0,                 // Reset X translation (must be 0 when setting fixed left/top)
                y: 0,                 // Reset Y translation
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
    
    // AOS init
    document.addEventListener("DOMContentLoaded", function() {
        AOS.init({ duration: 1000, easing: 'ease-out', once: true, offset: 120 });
    });

    // Back to Top Button
    const backToTop = document.getElementById("backToTop");
    window.addEventListener("scroll", () => {
        backToTop.style.display = window.scrollY > 300 ? "flex" : "none";
    });
    backToTop.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
    </script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>