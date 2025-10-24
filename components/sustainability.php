<section class="sustainability-section">
  <div class="pinned-content">
    <h1 class="scroll-text" id="scroll-text">
      Sustainability – A Drop Towards Change<br><br>
      We believe every bottle can make a difference. From eco-friendly production to reusable packaging, our mission is to ensure every sip supports a sustainable tomorrow.
    </h1>
  </div>
</section>

<style>
  .sustainability-section {
    position: relative;
    height: 300vh; /* Creates space for the effect */
  }

  .pinned-content {
    position: sticky;
    top: 0;
    height: 100vh;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(248, 250, 252, 0.8) 100%), 
                url('https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1600&q=80') center/cover;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
  }

  .scroll-text {
    width: min(90%, 1200px);
    font-size: clamp(2em, 4vw, 3em);
    text-align: center;
    line-height: 2;
    font-weight: 600;
    color: #0ea5e9;
    margin: 0 auto;
    text-transform: uppercase;
  }

  .scroll-text span {
    display: inline-block;
    color: #0ea5e9;
    transition: color 0.4s ease, transform 0.3s ease, text-shadow 0.4s ease;
    letter-spacing: 2px;
  }

  .scroll-text span.show-down {
    color: #10b981; /* green */
    transform: translateY(-2px);
    text-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
  }

  .scroll-text span.show-up {
    color: #0ea5e9; /* back to blue */
    transform: translateY(0);
    text-shadow: none;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const textElement = document.getElementById("scroll-text");
    const section = document.querySelector(".sustainability-section");
    const words = textElement.textContent.split(/\s+/).filter(Boolean);

    // Wrap words in spans
    textElement.innerHTML = words.map(w => `<span>${w}</span>`).join(" ");
    const spans = textElement.querySelectorAll("span");
    let lastScrollY = window.scrollY;

    window.addEventListener("scroll", () => {
      const rect = section.getBoundingClientRect();
      const scrollDirection = window.scrollY > lastScrollY ? "down" : "up";
      lastScrollY = window.scrollY;

      // Section active: while it's pinned
      if (rect.top <= 0 && rect.bottom > window.innerHeight) {
        const totalScrollable = rect.height - window.innerHeight;
        const currentScroll = Math.abs(rect.top);
        const progress = Math.min(currentScroll / totalScrollable, 1);
        const index = Math.floor(progress * spans.length);

        spans.forEach((span, i) => {
          if (scrollDirection === "down") {
            if (i <= index) {
              span.classList.add("show-down");
              span.classList.remove("show-up");
            }
          } else {
            if (i >= index) {
              span.classList.add("show-up");
              span.classList.remove("show-down");
            }
          }
        });
      }
    });
  });
</script>
