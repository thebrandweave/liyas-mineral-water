<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Liyas-mineral Water</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/about.css">
    <link rel="stylesheet" href="assets/css/product.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
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
            /* box-shadow: 0 12px 15px rgba(0,0,0,0.2); */
            /* background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%); */
            background:rgba(14, 164, 233, 0.22);
            will-change: width, height, transform;
            animation: circleDropExpand 2.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }


/* Logo text during splash */
.logo-text {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 60px;
    font-weight: 700;
    color: rgba(14, 164, 233, 0.71);
    z-index: 10;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    pointer-events: none;
    transition: all 1s ease; /* smooth transition */
}

/* After splash, move to top-right */
.logo-text.move-top-right {
    top: 20px;        /* distance from top */
    right: 20px;      /* distance from right */
    left: auto;       /* remove previous left */
    transform: none;  /* remove center transform */
    font-size: 36px;  /* optional smaller size */
}



.logo-text span {
  display: inline-block;
  position: relative;
  top: 0;
  transition: top 0.2s ease;
}


.logo-text.move-top {
  top: 2.5em;
  transform: translateX(-50%);
  font-size: 48px;
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

        .math-particle {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #ffffff;
            border-radius: 50%;
            animation: particleFloat 4s infinite;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.8);
        }

        .math-particle:nth-child(3n) {
            background: linear-gradient(135deg, #00a2ed, #0ea5e9);
            width: 12px;
            height: 12px;
        }

        .math-particle:nth-child(3n+1) {
            background: #ffffff;
            width: 8px;
            height: 8px;
        }

        .math-particle:nth-child(3n+2) {
            background:rgba(14, 164, 233, 0.8);
            width: 6px;
            height: 6px;
        }

        @keyframes particleFloat {
            0% { transform: translateY(0) translateX(0) translateZ(0) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            50% { transform: translateY(-50px) translateX(var(--x-offset)) translateZ(var(--z-offset)) rotate(180deg); opacity: 0.9; }
            100% { transform: translateY(-150px) translateX(calc(var(--x-offset) * 2)) translateZ(calc(var(--z-offset) * 0.5)) rotate(360deg); opacity: 0; }
        }

        @keyframes black {
            0%   { border-radius: 100%; width: 0; height: 0; top: 50%; transform: translateY(-50%) scale(0); }
            10%  { width: 300px; height: 300px; border-radius: 50%; transform: translateY(-50%) scale(1); }
            25%,100% { width: 100%; height: 100%; border-radius: 0; top: 0; transform: translateY(0) scale(1); }
        }

        @keyframes white { /* legacy - unused now */ }

        @keyframes circleDropExpand {
            0%   { width: 0; height: 0; border-radius: 50%; transform: translate(-50%, -80%); opacity: .98; }
            60%  { width: 60vmax; height: 60vmax; border-radius: 50%; transform: translate(-50%, -60%); }
            100% { width: 160vmax; height: 160vmax; border-radius: 50%; transform: translate(-50%, -50%); }
        }

        @keyframes logoFade {
            from { opacity: 0; transform: translateY(10px) scale(0.9); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .splash-fadeout {
            animation: splashFadeOut 0.8s cubic-bezier(0.215, 0.610, 0.355, 1.000) forwards;
        }
        
        @keyframes splashFadeOut {
            from { opacity: 1; transform: scale(1); }
            to   { opacity: 0; visibility: hidden; transform: scale(1.05); }
        }

        /* AOS handles reveal animations globally */
    </style>

    <!-- Splash Screen -->
    <div id="splash-screen" aria-hidden="true">
        <div class="splash-layer" aria-hidden="true"></div>
        <div class="logo-text">
            <div class="row">
            <img style="width: 100px; height: 100px;" src="assets/images/logo/logo.png" alt="Liyas">
            <!-- <span style="font-size: 24px; font-weight: 700; color: #0ea5e9;">Liyas</span> -->
            </div>
        </div>
        <div class="math-particles" aria-hidden="true" id="mathParticles"></div>
    </div>

    <!-- Social Icons Sidebar -->
    <div class="social-sidebar">
        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-x-twitter"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
    </div>
    
    <!-- Back to Top Button -->
    <button id="backToTop" title="Go to top"><i class="fas fa-arrow-up"></i></button>

    <!-- Hero Section -->
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

    <!-- About Section -->
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

    <!-- Product Section -->
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

// ====== Logo Text Scroll Float Effect (No Fade) ======
// window.addEventListener("load", function () {
//   const logo = document.querySelector(".logo-text");
//   if (!logo) return;

//   const txt = logo.innerText.trim();
//   logo.innerText = "";

//   // Create each span for the letters
//   for (let i = 0; i < txt.length; i++) {
//     const span = document.createElement("span");
//     span.textContent = txt[i];
//     span.style.display = "inline-block";
//     span.style.position = "relative";
//     span.style.transition = "top 0.2s ease";
//     span.y = 0;
//     logo.appendChild(span);
//   }

//   const letters = document.querySelectorAll(".logo-text span");
//   let lastScrollY = 0;

//   window.addEventListener("scroll", function () {
//     const scrollY = window.scrollY;
//     const scrollDirectionUp = scrollY > lastScrollY;

//     letters.forEach((span, i) => {
//       // Create a wave pattern using sine
//       const wave = Math.sin((scrollY / 30) + i) * 5; // amplitude = 5px
//       span.style.top = wave + "px";
//     });

//     lastScrollY = scrollY;
//   });
// });



    // Splash screen functionality
    document.addEventListener('DOMContentLoaded', function() {
        const splash = document.getElementById('splash-screen');
        const mathParticles = document.getElementById('mathParticles');
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
        const fallbackTimeout = setTimeout(finishSplash, 4000);
        layer.addEventListener('animationend', finishSplash);
        function finishSplash() {
    clearTimeout(fallbackTimeout);
    layer.removeEventListener('animationend', finishSplash);
    splash.classList.add('splash-fadeout');

    const logo = document.querySelector('.logo-text');

    // Wait until fade starts, then move logo to top and detach it
    setTimeout(() => {
        if (logo && splash.contains(logo)) {
            document.body.appendChild(logo);
            logo.classList.add('move-top');
        }
    }, 300);

    // Finally remove splash layer
    setTimeout(() => {
        splash.style.display = 'none';
        document.body.style.overflow = '';
    }, 1200);
}

    });

/* Logo text during splash */










    

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
