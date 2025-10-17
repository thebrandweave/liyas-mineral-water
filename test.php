<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sticky Scroll Transition</title>
<style>
  /* --- Reset & Fonts --- */
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Poppins', sans-serif; overflow-x: hidden; }

  /* --- Container --- */
  .sticky-container {
    position: relative;
    width: 100%;
  }

  /* --- Sections --- */
  .sticky-section {
    position: relative;
    width: 100%;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
  }

  /* Sticky content for hero */
  .sticky-section.sticky-content {
    position: sticky;
    top: 0;
    z-index: 2;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    transition: transform 0.5s ease-out, opacity 0.5s ease-out;
  }

  /* Hero styling */
  .hero-section {
    background: #0ea4e9;
    color: white;
  }
  .hero-section h1 {
    font-size: 5vw;
    font-weight: 800;
  }
  .hero-section p {
    font-size: 1.5rem;
    margin-top: 1rem;
  }

  /* About styling */
  .about-section {
    background: #ffffff;
    color: #333;
    opacity: 0; /* start hidden */
    transform: translateY(50px);
    transition: transform 0.5s ease-out, opacity 0.5s ease-out;
  }
  .about-section h1 {
    font-size: 4vw;
    font-weight: 700;
  }
  .about-section p {
    font-size: 1.2rem;
    margin-top: 1rem;
  }
</style>
</head>
<body>

<div class="sticky-container">

  <!-- Hero Section (sticky) -->
  <section class="sticky-section sticky-content hero-section">
      <h1>Hero Section</h1>
      <p>Welcome to our website</p>
  </section>

  <!-- About Section slides in -->
  <section class="sticky-section about-section">
      <h1>About Section</h1>
      <p>Here is some content about us. This section appears as you scroll down.</p>
  </section>

</div>

<script>
  // Scroll-triggered fade and slide transition
  const hero = document.querySelector('.hero-section');
  const about = document.querySelector('.about-section');

  window.addEventListener('scroll', () => {
      const scrollY = window.scrollY;
      const windowHeight = window.innerHeight;

      // Hero fade out
      const heroProgress = Math.min(scrollY / windowHeight, 1);
      hero.style.opacity = `${1 - heroProgress}`;
      hero.style.transform = `translateY(${-50 * heroProgress}px)`;

      // About fade in
      const aboutProgress = Math.min((scrollY - windowHeight/2) / (windowHeight/2), 1);
      if (aboutProgress > 0) {
          about.style.opacity = `${aboutProgress}`;
          about.style.transform = `translateY(${50 - 50 * aboutProgress}px)`;
      }
  });
</script>

</body>
</html>
