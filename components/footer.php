    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-about">
                    <h3>Liyas Mineral Water</h3>
                    <p>Premium natural spring water sourced from protected mountain springs. Committed to quality, sustainability, and your health since 2003.</p>
                    <div class="social-links">
                        <a href="https://facebook.com" target="_blank" class="social-icon">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://x.com" target="_blank" class="social-icon">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" class="social-icon">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://linkedin.com" target="_blank" class="social-icon">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://github.com" target="_blank" class="social-icon">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>
                </div>
                <section class="hollow-text-section d-flex justify-content-center align-items-center">
                    <div class="hollow-carousel" id="hollowCarousel">
                        <h1 class="hollow-text">
                            <span class="outline-letter">L</span>
                            <span class="outline-letter">I</span>
                            <span class="outline-letter">Y</span>
                            <span class="outline-letter">A</span>
                            <span class="outline-letter">S</span>
                        </h1>
                    </div>
                </section>

                <script>
                // Clone the content dynamically for continuous loop
                (function(){
                    const carousel = document.getElementById('hollowCarousel');
                    if (!carousel) return;
                    const textHTML = carousel.innerHTML;
                    for (let i = 0; i < 10; i++) {
                        carousel.insertAdjacentHTML('beforeend', textHTML);
                    }
                })();
                </script>

                <style>
                .hollow-text-section {
                    background-color: transparent;
                    overflow: hidden;
                    position: relative;
                    padding: 10px 0 20px;
                }
                .hollow-carousel {
                    display: flex;
                    white-space: nowrap;
                    animation: scrollText 12s linear infinite;
                }
                .hollow-text {
                    font-size: clamp(5rem, 12vw, 12rem);
                    font-weight: 900;
                    letter-spacing: -0.06em;
                    color: transparent;
                    display: inline-block;
                    margin: 0 1em;
                }
                .outline-letter {
                    display: inline-block;
                    -webkit-text-stroke: 2px rgb(0, 0, 0);
                    transition: -webkit-text-stroke 0.3s ease;
                }
                .outline-letter:hover { -webkit-text-stroke: 2px #0ea5e9; }
                @keyframes scrollText { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
                </style>

            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Liyas Mineral Water. All rights reserved. | Made with 💙 by The Brand Weave</p>
            </div>
        </div>
        <div class="footer-backdrop"></div>
    </footer>