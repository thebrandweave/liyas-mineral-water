<footer>
  <!-- background image (covers entire footer including overlay wrapper) -->
  <div class="footer-bg" aria-hidden="true"></div>

  <!-- content container (white layer INSIDE here) -->
  <div class="footer-container">
    <!-- white translucent layer that covers ONLY the container area -->
    <div class="footer-white-layer" aria-hidden="true"></div>

    <!-- CONTENT GRID (about + newsletter) -->
    <div class="footer-grid">
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

      <div class="newsletter">
        <h2>Subscribe to Our Newsletter</h2>
        <p>Stay hydrated and informed — get the latest offers and updates!</p>
        <form class="newsletter-form">
          <input type="email" placeholder="Enter your email" required aria-label="Email" />
          <button type="submit">Subscribe</button>
        </form>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; 2025 Liyas Mineral Water. All rights reserved.</p>
    </div>
  </div>
</footer>

  <!-- FOOTER OVERLAY WRAPPER — OUTSIDE .footer-container so NOT covered by white layer -->
  <div class="footer-overlay-wrapper" aria-hidden="true">
    <svg class="footer-overlay footer-overlay--inline" viewBox="0 0 1600 400" preserveAspectRatio="none" aria-hidden="true">
      <defs>
        <mask id="overlayCutoutInline" maskUnits="userSpaceOnUse">
          <rect x="0" y="0" width="1600" height="400" fill="white" />
          <g class="scroll-track" fill="black">
            <text x="0" y="300" class="hollow-text-svg">
              LIYAS LIYAS LIYAS LIYAS LIYAS LIYAS LIYAS LIYAS
            </text>
          </g>
        </mask>
      </defs>

      <rect x="0" y="0" width="1600" height="400" fill="#ffffff" fill-opacity="0.85" mask="url(#overlayCutoutInline)"/>
    </svg>
  </div>

<style>
/* layout + stacking */
footer {
  position: relative;
  overflow: visible;
  padding: 60px 0 0;
  font-family: 'Poppins', sans-serif;
}

/* background: full footer (z-index:0) */
.footer-bg {
  position: absolute;
  inset: 0;
  background: url('assets/images/bottle-1.jpg') center/cover no-repeat fixed;
  z-index: 0;
  pointer-events: none;
}

/* container: limited width and sits above background but below overlay */
.footer-container {
  position: relative;
  z-index: 2;
  max-width: 1200px;
  margin: 0 auto;
  padding: 60px 20px 40px;
  box-sizing: border-box;
}

/* white layer: absolutely positioned INSIDE container -> covers only container area */
.footer-white-layer {
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0.92); /* adjust opacity as needed */
  z-index: 1; /* sits under content (content uses z-index:3 below) */
  pointer-events: none;
}

/* content should be above the white layer */
.footer-grid,
.footer-bottom {
  position: relative;
  z-index: 3;
}

/* footer grid styling (same as previous) */
.footer-grid {
  display: grid;
  grid-template-columns: 1.3fr 1fr;
  gap: 3rem;
  align-items: start;
  text-align: left;
}
.footer-about h3 { color: #00aaff; font-weight:700; margin:0 0 12px; }
.footer-about p { margin:0 0 16px; color:#444; line-height:1.6; }

/* newsletter */
.newsletter { padding: 20px 24px; background: rgba(255,255,255,0.95); border-radius:14px; z-index:3; }
.newsletter h2 { color:#00aaff; margin:0 0 8px; }
.newsletter-form { display:flex; gap:10px; align-items:center; }

/* footer-bottom */
.footer-bottom { margin-top:30px; text-align:center; color:#555; z-index:3; }

/* OVERLAY WRAPPER: outside the white layer, higher z-index so not covered */
/* full-width band — sits above background but visually above container's white layer */
.footer-overlay-wrapper {
  position: relative;
  width: 100%;
  height: 200px;
  margin-top: 30px;
  z-index: 4; /* MUST be greater than .footer-container z-index so it's not covered */
  overflow: hidden;
}

/* SVG fills wrapper */
.footer-overlay--inline {
  width: 100%;
  height: 100%;
  display: block;
}

/* hollow text */
.hollow-text-svg {
  font-family: 'Poppins', sans-serif;
  font-weight: 900;
  font-size: 140px;
  letter-spacing: -0.06em;
}

/* scrolling animation */
.scroll-track { animation: scrollText 12s linear infinite; transform-box: fill-box; }
@keyframes scrollText { 0% { transform: translateX(0); } 100% { transform: translateX(-600px); } }

/* responsive tweaks */
@media (max-width: 991px) {
  .footer-grid { grid-template-columns: 1fr; text-align:center; }
  .newsletter-form { flex-direction: column; }
  .hollow-text-svg { font-size: 90px; }
  .footer-overlay-wrapper { height: 150px; margin-top: 16px; }
}
</style>
