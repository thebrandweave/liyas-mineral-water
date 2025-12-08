<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Products | LIYAS Mineral Water</title>
    <meta name="description" content="Browse our premium collection of LIYAS Mineral Water products. Pure, refreshing, and naturally sourced mineral water for a healthy lifestyle.">
    <meta name="keywords" content="mineral water, premium water, healthy water, LIYAS, pure water, products">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="../assets/images/logo/logo-bg.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="../assets/images/logo/logo-bg.jpg">
    <link rel="apple-touch-icon" href="../assets/images/logo/logo-bg.jpg">
    <link rel="icon" type="image/jpeg" sizes="32x32" href="../assets/images/logo/logo-bg.jpg">
    <link rel="icon" type="image/jpeg" sizes="16x16" href="../assets/images/logo/logo-bg.jpg">
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/product.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    
    <meta name="theme-color" content="#4ad2e2">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="LIYAS Water">
    
    <meta property="og:title" content="Products | LIYAS Mineral Water">
    <meta property="og:description" content="Browse our premium collection of LIYAS Mineral Water products.">
    <meta property="og:image" content="../assets/images/logo/logo.png">
    <meta property="og:url" content="https://liyas-water.com/products">
    <meta property="og:type" content="website">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Products | LIYAS Mineral Water">
    <meta name="twitter:description" content="Browse our premium collection of LIYAS Mineral Water products.">
    <meta name="twitter:image" content="../assets/images/logo/logo.png">
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "LIYAS Mineral Water Products",
        "description": "Premium quality mineral water products",
        "url": "https://liyas-water.com/products"
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
            background:rgba(74, 210, 226, 0.22);
            will-change: width, height, transform;
            animation: circleDropExpand 2.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        /* Logo container in the center */
        .logo-text {
            position: fixed;
            top: 50%;
            left: 50%;
            font-size: clamp(2rem, 8vw, 4rem);
            font-weight: 700;
            color: rgba(74, 210, 226, 0.71);
            z-index: 10000;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            pointer-events: none;
            font-family: 'Poppins', sans-serif;
        }

        .logo-text.move-top-left {
            top: 20px !important;
            left: 20px !important;
            right: auto !important;
            transform: none !important;
            font-size: clamp(1rem, 4vw, 1.5rem);
        }

        .logo-text img {
            width: clamp(60px, 15vw, 100px);
            height: clamp(60px, 15vw, 100px);
        }

        @media (max-width: 767px) {
            .logo-text.move-top-left {
                top: 14px !important;
                left: 14px !important;
            }
            
            .logo-text img {
                width: clamp(50px, 12vw, 80px);
                height: clamp(50px, 12vw, 80px);
            }
        }

        @media (max-width: 375px) {
            .logo-text.move-top-left {
                top: 12px !important;
                left: 12px !important;
            }
        }

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

        /* Page Header Design */
        .page-header {
            background: linear-gradient(135deg, rgba(74, 210, 226, 0.05) 0%, rgba(240, 249, 255, 0.3) 50%, rgba(255, 255, 255, 0.9) 100%);
            padding: 140px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(240, 249, 255, 0.3) 50%, rgba(255, 255, 255, 0.95) 100%);
            z-index: 1;
        }

        .page-header .container {
            position: relative;
            z-index: 2;
        }

        .page-header-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        .page-breadcrumb {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .page-breadcrumb a {
            color: #4ad2e2;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .page-breadcrumb a:hover {
            color: #2cbac9;
        }

        .page-breadcrumb span {
            color: #94a3b8;
        }

        .page-title {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            color: #0f172a;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            font-weight: 800;
        }

        .page-title .text-primary {
            color: #4ad2e2 !important;
        }



        @media (max-width: 767px) {
            .page-header {
                padding: 120px 0 60px;
            }

            .page-breadcrumb {
                font-size: 0.8rem;
            }
        }

        /* Social Sidebar */
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
            color: #0f172a;
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
            box-shadow: 0 12px 28px rgba(74, 210, 226, 0.25);
            border-color: rgba(74, 210, 226, 0.35);
        }

        @media (max-width: 767px) {
            .social-sidebar { display: none; }
        }

        /* Top-right Login Button */
        .top-login-btn {
            position: fixed;
            top: 42px;
            right: 20px;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            color: #000000;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(0, 0, 0, 0.08);
            text-decoration: none;
            z-index: 1100;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            cursor: pointer;
            min-width: 52px;
            min-height: 52px;
        }

        .top-login-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 20px rgba(74, 210, 226, 0.25);
            background: rgba(255, 255, 255, 1);
            border-color: rgba(74, 210, 226, 0.2);
        }

        .top-login-btn .login-svg {
            width: 24px;
            height: 24px;
            stroke-width: 2;
            transition: stroke 0.3s ease;
        }

        .top-login-btn:hover .login-svg {
            stroke: rgba(74, 210, 226, 1);
        }

        @media (max-width: 767px) {
            .top-login-btn {
                width: 48px !important;
                height: 48px !important;
                top: 16px !important;
                right: 16px !important;
            }
            
            .top-login-btn .login-svg {
                width: 22px !important;
                height: 22px !important;
            }
        }
    </style>

    <?php include '../components/navbar.php' ?>

    <div id="splash-screen" aria-hidden="true">
        <div class="splash-layer" aria-hidden="true"></div>
        <div class="logo-text"> 
            <div class="row">
                <img style="width: 100px; height: 100px;" src="../assets/images/logo/logo.png" alt="Liyas">
            </div>
        </div>
        <div class="math-particles" aria-hidden="true" id="mathParticles"></div>
    </div>

    <a href="../login.php" class="top-login-btn" title="Login">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.5" stroke="currentColor" class="login-svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 9V5.25A2.25 2.25 0 0013.5 3H6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 006 21h7.5a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
        </svg>
    </a>

    <div class="social-sidebar">
        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-x-twitter"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
    </div>
    
    <button id="backToTop" title="Go to top"><i class="fas fa-arrow-up"></i></button>

    <section class="page-header" id="home">
        <div class="container">
            <div class="page-header-content">
                <div class="page-breadcrumb">
                    <a href="../">Home</a> <span>/</span> <span>Products</span>
                </div>
                <h1 class="page-title">Our <span class="text-primary">Products</span></h1>

            </div>
        </div>
    </section>

    <?php include '../components/products.php'; ?>

    <?php include '../components/whychooseus.php'; ?>

    <?php include '../components/cta.php'; ?>

    <?php include '../components/footer.php'; ?>

    <script src="../assets/js/script.js"></script>
    
    <script>
        // Splash screen functionality
        document.addEventListener('DOMContentLoaded', function() {
            const splash = document.getElementById('splash-screen');
            const mathParticles = document.getElementById('mathParticles');
            const logo = document.querySelector('.logo-text'); 

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
                    particle.style.left = x + '%';
                    particle.style.top = y + '%';
                    particle.style.animationDelay = delay + 's';
                    mathParticles.appendChild(particle);
                }
            }

            setTimeout(() => {
                splash.classList.add('splash-fadeout');
                gsap.to(logo, {
                    x: '-50%',
                    y: '-50%',
                    top: '20px',
                    left: '20px',
                    duration: 1.2,
                    ease: 'power2.out',
                    onComplete: () => {
                        logo.classList.add('move-top-left');
                        setTimeout(() => {
                            splash.style.display = 'none';
                        }, 300);
                    }
                });
            }, 2800);
        });

        // Back to top button
        const backToTop = document.getElementById("backToTop");
        window.addEventListener("scroll", () => {
            if (window.scrollY > 300) {
                backToTop.style.display = "flex";
            } else {
                backToTop.style.display = "none";
            }
        });
        backToTop.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
    </script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // AOS init
        document.addEventListener("DOMContentLoaded", function() {
            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                AOS.init({ 
                    duration: 800, 
                    easing: 'ease-out', 
                    once: true, 
                    offset: 50 
                });
            } else {
                AOS.init({ 
                    duration: 1200, 
                    easing: 'ease-out-cubic', 
                    once: true, 
                    offset: 100 
                });
            }
        });
    </script>
</body>
</html>

