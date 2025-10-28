    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="assets/images/logo/logo.png" alt="LIYAS Mineral Water" class="logo-img">
                <span class="logo-text">LIYAS</span>
            </div>
            
            <!-- Mobile Menu Toggle -->
            <button class="nav-toggle" aria-label="Toggle navigation menu">
                <span class="hamburger"></span>
                <span class="hamburger"></span>
                <span class="hamburger"></span>
            </button>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#home" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="#about" class="nav-link">About</a>
                </li>
                <li class="nav-item">
                    <a href="#product" class="nav-link">Product</a>
                </li>
                <li class="nav-item">
                    <a href="#benefits" class="nav-link">Benefits</a>
                </li>
                <li class="nav-item">
                    <a href="#testimonials" class="nav-link">Reviews</a>
                </li>
                <li class="nav-item">
                    <a href="#gallery" class="nav-link">Gallery</a>
                </li>
                <li class="nav-item">
                    <a href="#contact" class="nav-link">Contact</a>
                </li>
                <li class="nav-item">
                    <a href="#order" class="nav-button">Order Now</a>
                </li>
            </ul>
        </div>
    </nav>

    <style>
        /* Navbar Styles */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(14, 165, 233, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--space-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            font-weight: 800;
            font-size: var(--text-xl);
            color: var(--primary);
        }

        .logo-img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .logo-text {
            font-size: var(--text-xl);
            font-weight: 800;
            color: var(--primary);
        }

        .nav-toggle {
            display: none;
            flex-direction: column;
            background: none;
            border: none;
            cursor: pointer;
            padding: var(--space-xs);
        }

        .hamburger {
            width: 25px;
            height: 3px;
            background: var(--primary);
            margin: 3px 0;
            transition: 0.3s;
            border-radius: 2px;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
            gap: var(--space-lg);
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            font-size: var(--text-base);
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-button {
            background: var(--primary);
            color: var(--white);
            padding: var(--space-xs) var(--space-md);
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: var(--text-sm);
            transition: all 0.3s ease;
            border: 2px solid var(--primary);
        }

        .nav-button:hover {
            background: transparent;
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(14, 165, 233, 0.3);
        }

        /* Mobile Styles */
        @media (max-width: 991px) {
            .nav-container {
                padding: 0 var(--space-sm);
            }

            .nav-toggle {
                display: flex;
            }

            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(10px);
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                padding-top: var(--space-xxl);
                transition: left 0.3s ease;
                gap: var(--space-lg);
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-item {
                margin: var(--space-sm) 0;
            }

            .nav-link {
                font-size: var(--text-lg);
                padding: var(--space-sm);
            }

            .nav-button {
                padding: var(--space-sm) var(--space-lg);
                font-size: var(--text-base);
                margin-top: var(--space-md);
            }

            /* Hamburger Animation */
            .nav-toggle.active .hamburger:nth-child(1) {
                transform: rotate(45deg) translate(5px, 5px);
            }

            .nav-toggle.active .hamburger:nth-child(2) {
                opacity: 0;
            }

            .nav-toggle.active .hamburger:nth-child(3) {
                transform: rotate(-45deg) translate(7px, -6px);
            }
        }

        @media (max-width: 767px) {
            .nav-container {
                height: 60px;
            }

            .nav-logo {
                font-size: var(--text-lg);
            }

            .logo-img {
                width: 35px;
                height: 35px;
            }

            .logo-text {
                font-size: var(--text-lg);
            }

            .nav-menu {
                top: 60px;
                height: calc(100vh - 60px);
            }

            .nav-link {
                font-size: var(--text-xl);
            }

            .nav-button {
                padding: var(--space-md) var(--space-xl);
                font-size: var(--text-lg);
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            .navbar,
            .nav-link,
            .nav-button,
            .hamburger {
                transition: none;
            }
        }

        /* Focus styles */
        .nav-link:focus,
        .nav-button:focus,
        .nav-toggle:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }
    </style>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.querySelector('.nav-toggle');
            const navMenu = document.querySelector('.nav-menu');
            const navLinks = document.querySelectorAll('.nav-link');

            // Toggle mobile menu
            navToggle.addEventListener('click', function() {
                navToggle.classList.toggle('active');
                navMenu.classList.toggle('active');
                document.body.classList.toggle('menu-open');
            });

            // Close menu when clicking on a link
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    navToggle.classList.remove('active');
                    navMenu.classList.remove('active');
                    document.body.classList.remove('menu-open');
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                    navToggle.classList.remove('active');
                    navMenu.classList.remove('active');
                    document.body.classList.remove('menu-open');
                }
            });

            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Smooth scrolling for anchor links
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href.startsWith('#')) {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) {
                            const offsetTop = target.offsetTop - 80; // Account for fixed navbar
                            window.scrollTo({
                                top: offsetTop,
                                behavior: 'smooth'
                            });
                        }
                    }
                });
            });
        });
    </script>