<section class="hollow-word-section d-flex justify-content-center align-items-center">
  <div class="hollow-bg-fixed"></div>
  <div class="hollow-word" id="hollow">
    <h1 class="hollow-text-content">
      <span class="outline">L</span>
      <span class="outline">I</span>
      <span class="outline">Y</span>
      <span class="outline">A</span>
      <span class="outline">S</span>
    </h1>
  </div>
</section>

<style>
/* Full-width and partial height section */
.hollow-word-section {
  width: 100vw;
  height: 40vh;
  height: 100%;
  min-height: 40vh; /* Use min-height for flexibility */
  background: #ffffff; /* background for contrast */
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  margin-top: -5em;
  /* margin-top: -5em; */ /* Removed to prevent layout issues on mobile */
  position: relative;
}

/* Fixed background with scroll animation */
.hollow-bg-fixed {
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

.hollow-bg-fixed::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(240, 249, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
  z-index: 1;
}

/* Container */
.hollow-word {
  width: 100%;
  text-align: center;
  position: relative;
  z-index: 2;
}

/* Text container */
.hollow-text-content {
  width: 100%;
  display: flex;
  justify-content: space-evenly; /* evenly space letters */
  align-items: center;
  font-size: clamp(15vw, 21vw, 20rem); /* Responsive font size with min/max */
  font-weight: 900;
  text-transform: uppercase;
  margin: 0;
  padding: 0;
  line-height: 1;
  letter-spacing: -0.1em; /* Adjusted for better spacing */
}

/* Masked shimmer effect on letters */
.outline {
  flex: 1;
  text-align: center;
  color: transparent;
  background-image: url('https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1600&q=80');
  background-size: 200%;
  background-repeat: repeat;
  background-position: 0 0;
  -webkit-background-clip: text;
  background-clip: text;
  animation: animate-background 4s linear infinite;
}

/* Animate background shimmer */
@keyframes animate-background {
  0% {
    background-position: 0% 50%;
  }
  100% {
    background-position: 200% 50%;
  }
}

/* Responsive adjustments for smaller screens */
@media (max-width: 768px) {
  .hollow-word-section {
    padding-top: 2rem; /* Add some vertical padding on mobile */
    padding-bottom: 4rem; /* Add some padding at the bottom on mobile */
  }
}
</style>

<!-- Scroll animation script -->
<script>
document.addEventListener("scroll", function() {
    const section = document.querySelector(".hollow-word-section");
    const bg = document.querySelector(".hollow-bg-fixed");
    const rect = section.getBoundingClientRect();

    if (rect.top < window.innerHeight && rect.bottom > 0) {
        const scrollY = rect.top * -0.3; // Adjust speed
        bg.style.setProperty("--scroll", `${scrollY}px`);
    }
});
</script>
