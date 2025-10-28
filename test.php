<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
<title>LIYAS Mineral Water - Responsive Sticky Scroll Test</title>
<meta name="description" content="Test page for responsive sticky scroll transitions">
<meta name="keywords" content="responsive, sticky scroll, test, LIYAS">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
  /* --- Reset & Fonts --- */
  * { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box; 
  }
  
  body { 
    font-family: 'Poppins', sans-serif; 
    overflow-x: hidden; 
    line-height: 1.6;
    color: #333;
  }

  /* --- CSS Variables for Responsive Design --- */
  :root {
    --space-xs: 0.5rem;
    --space-sm: 1rem;
    --space-md: 1.5rem;
    --space-lg: 2rem;
    --space-xl: 3rem;
    --space-xxl: 4rem;
    --space-xxxl: 6rem;
    
    --text-xs: 0.75rem;
    --text-sm: 0.875rem;
    --text-base: 1rem;
    --text-lg: 1.125rem;
    --text-xl: 1.25rem;
    --text-2xl: 1.5rem;
    --text-3xl: 1.875rem;
    --text-4xl: 2.25rem;
    --text-5xl: 3rem;
    --text-6xl: 3.75rem;
    
    --primary: #0ea5e9;
    --primary-dark: #0284c7;
    --secondary: #64748b;
    --dark: #0f172a;
    --light: #f8fafc;
    --white: #ffffff;
  }

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
    padding: var(--space-xl) var(--space-sm);
  }

  /* Responsive Sticky Sections */
  @media (max-width: 767px) {
    .sticky-section {
      height: auto;
      min-height: 100vh;
      padding: var(--space-lg) var(--space-sm);
    }
  }

  @media (min-width: 768px) and (max-width: 991px) {
    .sticky-section {
      height: auto;
      min-height: 100vh;
      padding: var(--space-xl) var(--space-md);
    }
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
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: var(--white);
    text-align: center;
  }
  
  .hero-section h1 {
    font-size: clamp(2rem, 8vw, 5vw);
    font-weight: 800;
    margin-bottom: var(--space-md);
    line-height: 1.1;
  }
  
  .hero-section p {
    font-size: clamp(1rem, 3vw, 1.5rem);
    margin-top: var(--space-md);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    opacity: 0.9;
  }

  /* Responsive Hero Styles */
  @media (max-width: 767px) {
    .hero-section h1 {
      font-size: clamp(1.5rem, 6vw, 2.5rem);
    }
    
    .hero-section p {
      font-size: clamp(0.9rem, 2.5vw, 1.1rem);
    }
  }

  /* About styling */
  .about-section {
    background: var(--white);
    color: var(--dark);
    opacity: 0; /* start hidden */
    transform: translateY(50px);
    transition: transform 0.5s ease-out, opacity 0.5s ease-out;
    text-align: center;
  }
  
  .about-section h1 {
    font-size: clamp(1.5rem, 6vw, 4vw);
    font-weight: 700;
    margin-bottom: var(--space-md);
    line-height: 1.2;
  }
  
  .about-section p {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    margin-top: var(--space-md);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    color: var(--secondary);
  }

  /* Responsive About Styles */
  @media (max-width: 767px) {
    .about-section h1 {
      font-size: clamp(1.2rem, 5vw, 2rem);
    }
    
    .about-section p {
      font-size: clamp(0.9rem, 2vw, 1rem);
    }
  }

  /* Additional Test Sections */
  .test-section {
    background: var(--light);
    color: var(--dark);
    text-align: center;
  }

  .test-section h1 {
    font-size: clamp(1.5rem, 6vw, 4vw);
    font-weight: 700;
    margin-bottom: var(--space-md);
    line-height: 1.2;
  }

  .test-section p {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    margin-top: var(--space-md);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    color: var(--secondary);
  }

  /* Responsive Test Section Styles */
  @media (max-width: 767px) {
    .test-section h1 {
      font-size: clamp(1.2rem, 5vw, 2rem);
    }
    
    .test-section p {
      font-size: clamp(0.9rem, 2vw, 1rem);
    }
  }

  /* Responsive Container Padding */
  @media (max-width: 767px) {
    .sticky-section {
      padding: var(--space-lg) var(--space-sm);
    }
  }

  @media (min-width: 768px) and (max-width: 991px) {
    .sticky-section {
      padding: var(--space-xl) var(--space-md);
    }
  }

  /* Responsive Typography */
  @media (max-width: 767px) {
    body {
      font-size: var(--text-sm);
    }
  }

  /* Responsive Spacing */
  @media (max-width: 767px) {
    .sticky-section {
      gap: var(--space-md);
    }
  }

  /* Performance Optimizations */
  @media (prefers-reduced-motion: reduce) {
    .sticky-section {
      transition: none;
    }
    
    .hero-section, .about-section, .test-section {
      transition: none;
    }
  }

  /* High Contrast Mode */
  @media (prefers-contrast: high) {
    :root {
      --primary: #0066cc;
      --primary-dark: #004499;
      --dark: #000000;
      --secondary: #333333;
    }
  }

  /* Print Styles */
  @media print {
    .sticky-section {
      height: auto;
      page-break-inside: avoid;
    }
    
    .hero-section {
      background: white;
      color: black;
    }
  }
</style>
</head>
<body>

<div class="sticky-container">

  <!-- Hero Section (sticky) -->
  <section class="sticky-section sticky-content hero-section">
      <h1>LIYAS Mineral Water</h1>
      <p>Premium quality mineral water for a healthy lifestyle. Pure, refreshing, and naturally sourced.</p>
  </section>

  <!-- About Section slides in -->
  <section class="sticky-section about-section">
      <h1>About Our Water</h1>
      <p>We source our water from pristine natural springs and carefully filter it through advanced purification systems to ensure the highest quality standards.</p>
  </section>

  <!-- Test Section -->
  <section class="sticky-section test-section">
      <h1>Quality Assurance</h1>
      <p>Every bottle undergoes rigorous testing to ensure it meets our strict quality standards and delivers the pure, refreshing taste you expect.</p>
  </section>

  <!-- Additional Test Section -->
  <section class="sticky-section about-section">
      <h1>Customer Satisfaction</h1>
      <p>We're committed to providing exceptional service and premium quality water that exceeds our customers' expectations.</p>
  </section>

</div>

<script>
  // Responsive scroll handling
  function handleResponsiveScroll() {
    const hero = document.querySelector('.hero-section');
    const about = document.querySelector('.about-section');
    const testSection = document.querySelector('.test-section');
    
    if (!hero || !about) return;
    
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
    
    // Test section fade in
    if (testSection) {
      const testProgress = Math.min((scrollY - windowHeight * 1.5) / (windowHeight/2), 1);
      if (testProgress > 0) {
        testSection.style.opacity = `${testProgress}`;
        testSection.style.transform = `translateY(${50 - 50 * testProgress}px)`;
      }
    }
  }

  // Throttle scroll events for better performance
  let scrollTimeout;
  window.addEventListener('scroll', () => {
    if (scrollTimeout) {
      clearTimeout(scrollTimeout);
    }
    scrollTimeout = setTimeout(handleResponsiveScroll, 10);
  });

  // Initial call
  handleResponsiveScroll();

  // Responsive handling
  function handleResize() {
    const isMobile = window.innerWidth <= 768;
    
    // Adjust scroll behavior for mobile
    if (isMobile) {
      // Reduce scroll sensitivity on mobile
      document.body.style.scrollBehavior = 'smooth';
    } else {
      document.body.style.scrollBehavior = 'auto';
    }
  }

  window.addEventListener('resize', handleResize);
  handleResize();

  // Touch-friendly interactions
  if ('ontouchstart' in window) {
    document.body.classList.add('touch-device');
    document.body.style.webkitOverflowScrolling = 'touch';
  }

  // Performance optimization for mobile
  if (window.innerWidth <= 768) {
    // Reduce animation complexity on mobile
    document.querySelectorAll('.sticky-section').forEach(section => {
      section.style.willChange = 'auto';
    });
  }

  // Accessibility improvements
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Tab') {
      document.body.classList.add('keyboard-navigation');
    }
  });

  document.addEventListener('mousedown', () => {
    document.body.classList.remove('keyboard-navigation');
  });

  // Reduced motion support
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('.sticky-section').forEach(section => {
      section.style.transition = 'none';
    });
  }
</script>

</body>
</html>