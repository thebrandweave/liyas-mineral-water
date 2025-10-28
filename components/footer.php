    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-about">
                    <div class="footer-logo">
                        <img src="assets/images/logo/logo.png" alt="LIYAS Mineral Water" class="footer-logo-img">
                        <h3>LIYAS Mineral Water</h3>
                    </div>
                    <p>Premium natural spring water sourced from protected mountain springs. Committed to quality, sustainability, and your health since 2003.</p>
                    <div class="social-links">
                        <a href="https://facebook.com" target="_blank" class="social-icon" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://x.com" target="_blank" class="social-icon" aria-label="Twitter">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" class="social-icon" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://linkedin.com" target="_blank" class="social-icon" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://github.com" target="_blank" class="social-icon" aria-label="GitHub">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>
                </div>

                <div class="footer-links">
                    <div class="footer-column">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="#home">Home</a></li>
                            <li><a href="#about">About Us</a></li>
                            <li><a href="#product">Products</a></li>
                            <li><a href="#benefits">Benefits</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Services</h4>
                        <ul>
                            <li><a href="#order">Order Now</a></li>
                            <li><a href="#delivery">Delivery</a></li>
                            <li><a href="#contact">Contact</a></li>
                            <li><a href="#support">Support</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Contact Info</h4>
                        <div class="contact-info">
                            <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                            <p><i class="fas fa-envelope"></i> info@liyaswater.com</p>
                            <p><i class="fas fa-map-marker-alt"></i> 123 Water Street, City, State 12345</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2025 LIYAS Mineral Water. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="#privacy">Privacy Policy</a>
                        <a href="#terms">Terms of Service</a>
                        <a href="#cookies">Cookie Policy</a>
                    </div>
                </div>
                <p class="footer-credit">Made with 💙 by The Brand Weave</p>
            </div>
        </div>
    </footer>

    <style>
        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: var(--white);
            padding: var(--space-xxxl) 0 var(--space-lg) 0;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--space-md);
        }

        .footer-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-xxl);
            margin-bottom: var(--space-xxl);
        }

        .footer-about {
            text-align: center;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            margin-bottom: var(--space-md);
        }

        .footer-logo-img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .footer-logo h3 {
            font-size: var(--text-2xl);
            font-weight: 800;
            color: var(--primary);
            margin: 0;
        }

        .footer-about p {
            font-size: var(--text-base);
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.8);
            max-width: 500px;
            margin: 0 auto var(--space-lg) auto;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: var(--space-md);
        }

        .social-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: var(--white);
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .social-icon:hover {
            background: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(14, 165, 233, 0.3);
        }

        .footer-links {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-xl);
        }

        .footer-column h4 {
            font-size: var(--text-lg);
            font-weight: 700;
            color: var(--white);
            margin-bottom: var(--space-md);
            text-align: center;
        }

        .footer-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: center;
        }

        .footer-column li {
            margin-bottom: var(--space-sm);
        }

        .footer-column a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: var(--text-sm);
            transition: color 0.3s ease;
        }

        .footer-column a:hover {
            color: var(--primary);
        }

        .contact-info {
            text-align: center;
        }

        .contact-info p {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-xs);
            margin-bottom: var(--space-sm);
            font-size: var(--text-sm);
            color: rgba(255, 255, 255, 0.7);
        }

        .contact-info i {
            color: var(--primary);
            width: 16px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: var(--space-lg);
            text-align: center;
        }

        .footer-bottom-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-md);
            margin-bottom: var(--space-md);
        }

        .footer-bottom p {
            font-size: var(--text-sm);
            color: rgba(255, 255, 255, 0.6);
            margin: 0;
        }

        .footer-bottom-links {
            display: flex;
            gap: var(--space-lg);
            flex-wrap: wrap;
            justify-content: center;
        }

        .footer-bottom-links a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: var(--text-xs);
            transition: color 0.3s ease;
        }

        .footer-bottom-links a:hover {
            color: var(--primary);
        }

        .footer-credit {
            font-size: var(--text-xs);
            color: rgba(255, 255, 255, 0.5);
            margin: 0;
        }

        /* Responsive Styles */
        @media (min-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr 2fr;
                align-items: start;
            }

            .footer-about {
                text-align: left;
            }

            .footer-logo {
                justify-content: flex-start;
            }

            .footer-about p {
                margin-left: 0;
                margin-right: 0;
            }

            .social-links {
                justify-content: flex-start;
            }

            .footer-links {
                grid-template-columns: repeat(3, 1fr);
            }

            .footer-column h4 {
                text-align: left;
            }

            .footer-column ul {
                text-align: left;
            }

            .contact-info {
                text-align: left;
            }

            .contact-info p {
                justify-content: flex-start;
            }

            .footer-bottom-content {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }

        @media (min-width: 992px) {
            .footer {
                padding: var(--space-xxxl) 0 var(--space-xl) 0;
            }

            .footer-content {
                gap: var(--space-xxxl);
            }

            .footer-links {
                gap: var(--space-xxl);
            }
        }

        @media (max-width: 767px) {
            .footer {
                padding: var(--space-xxl) 0 var(--space-md) 0;
            }

            .footer-container {
                padding: 0 var(--space-sm);
            }

            .footer-content {
                gap: var(--space-xl);
            }

            .footer-logo-img {
                width: 40px;
                height: 40px;
            }

            .footer-logo h3 {
                font-size: var(--text-xl);
            }

            .footer-about p {
                font-size: var(--text-sm);
            }

            .social-icon {
                width: 40px;
                height: 40px;
            }

            .footer-bottom-links {
                gap: var(--space-md);
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            .social-icon {
                transition: none;
            }
        }

        /* Focus styles */
        .social-icon:focus,
        .footer-column a:focus,
        .footer-bottom-links a:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }
    </style>