<footer>
  <div class="footer-container">
    <div class="footer-content">
      <div class="footer-about">
        <h3>Liyas Mineral Water</h3>
        <p>
          Premium natural spring water sourced from protected mountain springs.
          Committed to quality, sustainability, and your health since 2003.
        </p>
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-x-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-linkedin-in"></i></a>
          <a href="#"><i class="fab fa-github"></i></a>
        </div>
      </div>

      <section class="hollow-text-section">
  <svg class="footer-overlay" viewBox="0 0 1600 400" preserveAspectRatio="none" aria-hidden="true">
    <defs>
      <mask id="overlayCutout" maskUnits="userSpaceOnUse">
        <rect x="0" y="0" width="1600" height="400" fill="white" />
        <g class="scroll-track" fill="black">
          <text x="0" y="300" class="hollow-text-svg">LIYAS LIYAS LIYAS LIYAS LIYAS LIYAS LIYAS LIYAS</text>
        </g>
      </mask>
    </defs>
    <rect x="0" y="0" width="1600" height="400" fill="#ffffff" fill-opacity="0.85" mask="url(#overlayCutout)" />
  </svg>
      </section>
    </div>

    <div class="footer-bottom">
      <p>&copy; 2025 Liyas Mineral Water. All rights reserved.</p>
    </div>
  </div>

  <!-- Full background image -->
  <div class="footer-bg"></div>

  <!-- White overlay layer with scrolling text cutout -->
  <svg class="footer-overlay" viewBox="0 0 1600 400" preserveAspectRatio="none" aria-hidden="true">
    <defs>
      <mask id="overlayCutout" maskUnits="userSpaceOnUse">
        <rect x="0" y="0" width="1600" height="400" fill="white" />
        <g class="scroll-track" fill="black">
          <text x="0" y="300" class="hollow-text-svg">LIYAS LIYAS LIYAS LIYAS LIYAS LIYAS LIYAS LIYAS</text>
        </g>
      </mask>
    </defs>
    <rect x="0" y="0" width="1600" height="400" fill="#ffffff" fill-opacity="0.85" mask="url(#overlayCutout)" />
  </svg>
</footer>

<style>
footer {
  position: relative;
  overflow: hidden;
  color: #333;
  text-align: center;
  padding: 60px 0;
}

/* Background image full opacity */
.footer-bg {
  position: absolute;
  inset: 0;
  background: url('assets/images/bottle-1.jpg') center/cover no-repeat fixed;
  z-index: 0;
}

/* White overlay on top of the image */
.footer-overlay {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
  display: block;
}

/* Footer content sits above the overlay */
.footer-container {
  position: relative;
  z-index: 2;
}

/* Hollow text above overlay but shows image beneath via overlay mask */
.hollow-text-section {
  position: relative;
  z-index: 3;
  margin-top: 40px;
}

/* Scrolling hollow text */
/* Mask text metrics (used inside the overlay mask) */
.hollow-text-svg {
  font-family: 'Poppins', sans-serif;
  font-weight: 900;
  font-size: 140px;
  letter-spacing: -0.06em;
}

/* Visible outline */
.hollow-text-stroke {
  font-family: 'Poppins', sans-serif;
  font-weight: 900;
  font-size: 140px;
  letter-spacing: -0.06em;
  fill: none;
  stroke: #000;
  stroke-width: 2.5;
}

/* Removed duplicate background; reveal comes from overlay mask */

/* Animate the text scrolling */
.scroll-track {
  animation: scrollText 12s linear infinite;
}
@keyframes scrollText {
  0% { transform: translateX(0); }
  100% { transform: translateX(-600px); }
}

/* Footer about + social styles */
.footer-about h3 {
  color: #00aaff;
  font-weight: 700;
}
.social-links {
  margin-top: 20px;
}
.social-links a {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  width: 40px;
  height: 40px;
  margin: 0 5px;
  border-radius: 50%;
  background: #fff;
  color: #00aaff;
  font-size: 18px;
  transition: 0.3s;
}
.social-links a:hover {
  background: #00aaff;
  color: #fff;
}
.footer-bottom {
  margin-top: 30px;
  font-size: 14px;
  color: #555;
}
</style>
