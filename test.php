<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Liyas Mineral Water — Demo</title>
  <style>
    /* ====== Scroll Reveal Animation (Water Theme) ====== */
section {
  opacity: 0;
  transform: translateY(80px);
  filter: blur(10px);
  transition: 
    opacity 1.5s ease-out,
    transform 1.5s ease-out,
    filter 1.5s ease-out;
}

section.section-visible {
  opacity: 1;
  transform: translateY(0);
  filter: blur(0);
}

/* Add subtle floating for elements inside each section */
.fade-up {
  opacity: 0;
  transform: translateY(40px);
  transition: all 1.2s ease-out;
}

.section-visible .fade-up {
  opacity: 1;
  transform: translateY(0);
}

/* Water glow effect on titles */
.section-visible h1, 
.section-visible h2 {
  text-shadow: 0 0 15px rgba(0, 153, 255, 0.25);
}

/* Hero and About sections with wave animation overlay */
.wave-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  background: radial-gradient(circle at 50% 70%, rgba(0, 174, 255, 0.1), transparent 70%);
  animation: waveMove 8s ease-in-out infinite alternate;
  z-index: 0;
}

@keyframes waveMove {
  0% { transform: scale(1) translateY(0); }
  100% { transform: scale(1.05) translateY(-20px); }
}

  </style>
</head>
<body>

  <!-- SPLASH -->
  <div id="splash-screen" aria-hidden="true">
    <div class="animate" aria-hidden="true"></div>
    <div class="animate2" aria-hidden="true"></div>
    <div class="logo-text">Liyas</div>
  </div>

  <!-- MAIN SITE (hidden until splash removed) -->
  <div id="main-content" role="main" aria-live="polite">
    <main>
      <section class="home">
        <h1>Welcome to Liyas Mineral Water</h1>
        <p>Pure, refreshing, and delivered to your doorstep.</p>
      </section>

      <section class="about">
        <h1>About Liyas</h1>
        <p>Our mission is to provide crystal-clear, healthy mineral water for every home and workplace. We maintain strict purification standards and hygienic packaging.</p>
      </section>

      <section class="products">
        <h1>Our Products</h1>
        <p>Choose from 500ml, 1L, 5L, and 20L options. Subscription and bulk orders are available with fast local delivery.</p>
      </section>

      <section class="contact">
        <h1>Contact Us</h1>
        <p>Reach us at info@liyaswater.com or call +91 98765 43210. We’d love to serve you!</p>
      </section>
    </main>
  </div>

  <script>
    /* ==============
       1) Wait for the page to be ready.
       2) Hide splash when the animations finish (animationend event).
       3) Use IntersectionObserver to reveal sections when they enter viewport.
       ============== */

    (function () {
      const splash = document.getElementById('splash-screen');
      const mainContent = document.getElementById('main-content');

      // Ensure body can't scroll while splash is visible
      document.body.style.overflow = 'hidden';

      // Listen for animationend on the second animated layer (white)
      // This is more reliable than a fixed timeout.
      const layer = document.querySelector('.animate2');

      // Fallback timeout in case animationend doesn't fire (e.g., dev tools paused):
      const fallbackTimeout = setTimeout(finishSplash, 7000); // 7s fallback

      function finishSplash() {
        clearTimeout(fallbackTimeout);

        // Fade out the splash then remove it
        splash.classList.add('splash-fadeout');

        // Wait for fadeout to finish then remove splash and show main content
        splash.addEventListener('animationend', onSplashFadeComplete);

        // If animationend on fade doesn't occur (edge case), set a hard timeout
        setTimeout(() => {
          if (splash && splash.parentNode) {
            removeSplash();
          }
        }, 1200);
      }

      function onSplashFadeComplete(e) {
        if (e.animationName === 'splashFadeOut') {
          removeSplash();
        }
      }

      function removeSplash() {
        // show main content
        splash.style.display = 'none';
        mainContent.style.display = 'block';

        // restore scrolling
        document.body.style.overflowY = 'auto';

        // initialize scroll reveal
        initScrollReveal();

        // cleanup listeners
        if (layer) layer.removeEventListener('animationend', finishSplash);
        splash.removeEventListener('animationend', onSplashFadeComplete);
      }

      // if the animated layer finishes, finish splash
      if (layer) {
        layer.addEventListener('animationend', finishSplash, { once: true });
      } else {
        // if the layer isn't found, fallback to timeout
        setTimeout(finishSplash, 6000);
      }

      /* ----------------------
         IntersectionObserver
         Reveals sections when they enter viewport
         ---------------------- */
      function initScrollReveal() {
        const options = {
          root: null,
          rootMargin: '0px',
          threshold: 0.15 // 15% visible triggers reveal
        };
        const observer = new IntersectionObserver((entries, obs) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('visible');
              obs.unobserve(entry.target); // reveal once
            }
          });
        }, options);

        document.querySelectorAll('section').forEach(sec => observer.observe(sec));
      }
    })();
  </script>
</body>
</html>
