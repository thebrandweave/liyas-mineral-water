<section class="sustainability-section">
  <div class="pinned-content">
    <h1 class="scroll-text" id="scroll-text">
      Sustainability – <strong>A Drop Towards Change</strong><br><br>
      We believe every bottle can make a difference. From eco-friendly production to reusable packaging, our mission is to ensure every sip supports a sustainable tomorrow.
    </h1>
  </div>
</section>

<style>

  .sustainability-section {
    position: relative;
    height: 200vh; /* scroll zone */
  }

  .pinned-content {
    position: sticky;
    top: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .scroll-text {
    /* width: min(90%, 700px); */
    font-size: clamp(2em, 100vw, 4rem);
    text-align: center;
    line-height: 1.5;
    font-weight: 700;
    color: #0ea5e9;
    opacity: 0;
    transition: opacity 0.3s ease;
    text-transform: uppercase;
  }

  .scroll-text span {
    display: inline-block;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
  }

  .scroll-text span.show {
    opacity: 1;
    transform: translateY(0);
  }
</style>

<script>
  const textElement = document.getElementById("scroll-text");
  const words = textElement.textContent.split(" ");
  textElement.textContent = "";

  // Wrap each word in a <span>
  words.forEach(word => {
    const span = document.createElement("span");
    span.textContent = word + " ";
    textElement.appendChild(span);
  });

  const spans = textElement.querySelectorAll("span");

  window.addEventListener("scroll", () => {
    const section = document.querySelector(".sustainability-section");
    const rect = section.getBoundingClientRect();

    // Check if section is in the pinned area
    if (rect.top <= 0 && rect.bottom > window.innerHeight) {
      textElement.style.opacity = 1;
      const progress =
        (window.innerHeight - rect.bottom + window.innerHeight) /
        (rect.height - window.innerHeight);
      const wordIndex = Math.floor(progress * spans.length);

      spans.forEach((span, i) => {
        span.classList.toggle("show", i <= wordIndex);
      });
    } else {
      textElement.style.opacity = 0;
      spans.forEach(span => span.classList.remove("show"));
    }
  });
</script>
