<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrusel Infinito</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body, html {
            font-size: 62.5%;
            background-color: black;
        }
        section {
            position: relative;
            width: 100%;
            overflow: hidden;
        }
        /* Carousel Section */
        .loop-images {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 100vh;
        }
        .carousel-track {
            --left: -300rem;
            min-width: calc(10rem * var(--total));
            height: 30rem;
            position: relative;
        }
        .carousel-item {
            position: absolute;
            width: 30rem;
            height: 30rem;
            left: 100%;
            display: flex;
            justify-content: center;
            perspective: 1000px;
            transform-style: preserve-3d;
            animation: scroll-left var(--time) linear infinite;
            animation-delay: calc(var(--time) / var(--total) * (var(--i) - 1) - var(--time));
            cursor: pointer;
        }
        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background-color: white;
            transform: rotateY(-45deg);
            transition: 0.5s ease-in-out;
            mask: linear-gradient(black 70%, transparent 100%);
        }
        .carousel-item:hover img {
            transform: rotateY(0deg) translateY(-1rem);
        }
        @keyframes scroll-left {
            to { left: var(--left); }
        }
        .scroll-down {
            position: absolute;
            bottom: 5rem;
            left: 0;
            right: 0;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-size: 1.6rem;
            color: black;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Image Motion Section */
        .section2 {
            min-height: 60vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #000;
        }
        .image-motion {
            width: 400px;
            height: 400px;
            transform: rotateX(90deg); /* animated via GSAP */
        }
        .image-motion picture, .image-motion img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            border-radius: 10px;
        }

        /* Section3 - Text & Features */
        .section3 {
            --bg-color: #000000;
            --text-primary: #ccc;
            --text-secondary: #aaa;
            --text-white: #ffffff;
            --accent-primary: #ff6b6b;
            --accent-secondary: #ff8a80;
            --accent-tertiary: #ffab40;
            --accent-quaternary: #ff7043;
            --accent-quinary: #ff5722;
            --border-radius: 25px;
            --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);

            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-white);
            padding: 6rem 2rem;
            min-height: 100vh;
            background: var(--bg-color);
        }
        .section3 .container {
            width: 100%;
            max-width: 1200px;
            margin: auto;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        .section3 .title {
            font-size: 5rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            margin-bottom: 2.5rem;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary), var(--accent-tertiary), var(--accent-quaternary), var(--accent-quinary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
            letter-spacing: -2px;
            position: relative;
        }
        .section3 .title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 2px;
        }
        .section3 .subtitle {
            font-size: 1.6rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
            color: var(--text-primary);
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .section3 .text-content .text {
            font-size: 1.3rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            color: var(--text-primary);
            line-height: 1.9;
            margin-bottom: 2.5rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
            margin-top: 5rem;
        }
        .feature {
            background: linear-gradient(145deg, rgba(255,107,107,0.1), rgba(255,138,128,0.05));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,107,107,0.3);
            border-radius: var(--border-radius);
            padding: 3.5rem 2.5rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }
        .feature h3 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--text-white) 0%, var(--accent-primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .feature p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            line-height: 1.7;
        }
        @media (max-width: 768px) {
            .section3 .container .title { font-size: 3.5rem; }
            .section3 .subtitle { font-size: 1.2rem; }
        }
    </style>
</head>
<body>

<section class="loop-images" style="--bg: white;">
    <div class="carousel-track" style="--time: 60s; --total: 12;">
        <!-- Carousel items (same as original) -->
        <div class="carousel-item" style="--i: 1;"><img src="https://images.unsplash.com/photo-1758314896569-b3639ee707c4?q=80&w=715&auto=format&fit=crop" alt="image"></div>
        <div class="carousel-item" style="--i: 2;"><img src="https://plus.unsplash.com/premium_photo-1671649240322-2124cd07eaae?q=80&w=627&auto=format&fit=crop" alt="image"></div>
        <div class="carousel-item" style="--i: 3;"><img src="https://plus.unsplash.com/premium_photo-1673029925648-af80569efc46?q=80&w=687&auto=format&fit=crop" alt="image"></div>
        <!-- ... add rest 12 images similarly ... -->
    </div>
    <span class="scroll-down">Scroll down <span class="arrow">↓</span></span>
</section>

<section class="section2" style="--bg: black;">
    <div class="image-motion">
        <picture>
            <img src="https://i.postimg.cc/1ztkf4hX/moveimage.png" alt="image">
        </picture>
    </div>
</section>

<section style="--bg: black;" class="section3">
    <div class="container">
        <h1 class="title">Carrusel Infinito</h1>
        <p class="subtitle">Una experiencia visual única</p>
        <div class="text-content">
            <p class="text">Descubre la magia del movimiento continuo con nuestro carrusel de imágenes infinito...</p>
            <p class="text">La animación 3D y los efectos de perspectiva añaden profundidad y dinamismo...</p>
            <p class="text">Perfecto para portfolios, galerías de productos o cualquier proyecto...</p>
        </div>
        <div class="features">
            <div class="feature">
                <h3>Diseño Moderno</h3>
                <p>Efectos 3D y animaciones suaves</p>
            </div>
            <div class="feature">
                <h3>Rendimiento Óptimo</h3>
                <p>Animaciones CSS puras sin JavaScript</p>
            </div>
            <div class="feature">
                <h3>Totalmente Responsive</h3>
                <p>Se adapta a cualquier dispositivo</p>
            </div>
        </div>
    </div>
</section>

<!-- GSAP + Lenis + SplitText -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.14.0/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.14.0/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.14.0/SplitText.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.25/bundled/lenis.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    gsap.registerPlugin(ScrollTrigger, SplitText);

    const lenis = new Lenis();
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);

    // Image Motion Animation
    gsap.to('.image-motion', {
        transform: 'rotateX(0deg)',
        scrollTrigger: { trigger: '.section2', start: 'top bottom', end: 'bottom top', scrub: true }
    });

    // Section3 animations
    gsap.fromTo('.title', { opacity: 0, y: 50 }, { opacity: 1, y: 0, duration: 1, ease: 'power3.out', scrollTrigger: { trigger: '.section3', start: 'top 80%', end: 'bottom 20%', toggleActions: 'play none none reverse' } });
    gsap.fromTo('.subtitle', { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.8, delay: 0.3, ease: 'power3.out', scrollTrigger: { trigger: '.section3', start: 'top 80%', end: 'bottom 20%', toggleActions: 'play none none reverse' } });

    const text = new SplitText('.text', { types: 'lines', mask: 'lines' });
    gsap.fromTo(text.lines, { opacity: 0, y: 30 }, { opacity: 1, y: 0, stagger: 0.2, duration: 0.8, ease: 'power3.out', scrollTrigger: { trigger: '.text-content', start: 'top 80%', end: 'bottom 20%', toggleActions: 'play none none reverse' } });

    gsap.fromTo('.feature', { opacity: 0, y: 50, scale: 0.9 }, { opacity: 1, y: 0, scale: 1, stagger: 0.2, duration: 0.8, ease: 'power3.out', scrollTrigger: { trigger: '.features', start: 'top 80%', end: 'bottom 20%', toggleActions: 'play none none reverse' } });
});
</script>

</body>
</html>
