<!-- components/navbar.php -->
<?php
// Get the script path and determine base path
$script_path = $_SERVER['SCRIPT_NAME'];
$script_dir = dirname($script_path);

// Get all path segments
$path_segments = array_filter(explode('/', $script_dir));

// If we're in a subdirectory (like contact/ or about/), remove the last segment
if (count($path_segments) > 1) {
    array_pop($path_segments);
}

// Build base path from remaining segments
$base_path = '/' . implode('/', $path_segments);
if ($base_path !== '/') {
    $base_path .= '/';
}

// Set navigation links to actual pages
$home_link = $base_path;
$about_link = $base_path . 'about/';
$products_link = $base_path . 'products/';
$contact_link = $base_path . 'contact/';
?>
<nav class="navbar fixed-top liyas-navbar">
  <div class="container d-flex justify-content-between align-items-center">

    <!-- Logo (Left Side) -->
    <!-- <a href="<?php echo $home_link; ?>" class="navbar-brand logo-text d-flex align-items-center">
      <img src="<?php echo $base_path; ?>assets/images/logo/logo.png" alt="Liyas Logo" class="navbar-logo">
    </a> -->

    <!-- Center Nav Items -->
    <ul class="navbar-nav flex-row gap-4 nav-items-placeholder d-none d-md-flex mx-auto">
      <li class="nav-item"><a href="<?php echo $home_link; ?>" class="nav-link">Home</a></li>
      <li class="nav-item"><a href="<?php echo $about_link; ?>" class="nav-link">About</a></li>
      <li class="nav-item"><a href="<?php echo $products_link; ?>" class="nav-link">Products</a></li>
      <li class="nav-item"><a href="<?php echo $contact_link; ?>" class="nav-link">Contact</a></li>
    </ul>

    <!-- Cart Icon -->
    <a href="#" id="cart-icon" class="cart-icon" title="View Cart">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.343 1.087-.835l1.823-6.423a.75.75 0 00-.67-1.03H6.088l-.523-1.974M16.5 21a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM8.25 21a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
        </svg>
    </a>

    <!-- Hamburger Menu (Mobile) -->
    <button class="navbar-toggler d-md-none" type="button" id="mobileMenuBtn" aria-label="Toggle navigation">
      <span class="hamburger-icon"></span>
    </button>

  </div>

  <!-- Mobile Menu Overlay -->
  <div class="mobile-menu" id="mobileMenu">
    <ul>
      <li><a href="<?php echo $home_link; ?>" class="nav-link">Home</a></li>
      <li><a href="<?php echo $about_link; ?>" class="nav-link">About</a></li>
      <li><a href="<?php echo $products_link; ?>" class="nav-link">Products</a></li>
      <li><a href="<?php echo $contact_link; ?>" class="nav-link">Contact</a></li>
    </ul>
  </div>

  <!-- Cart Sidebar -->
    <div id="cart-sidebar" class="cart-sidebar">
        <div class="cart-header">
            <button id="close-cart-btn" class="close-cart-btn">&times;</button>
            <h4>Your Cart</h4>
        </div>
        <div class="cart-body">
            <!-- Cart items will be dynamically inserted here -->
            <p class="cart-empty-message">Your cart is empty.</p>
        </div>
        <div class="cart-footer">
            <div class="cart-subtotal">
                <span>Subtotal</span>
                <span id="subtotal-price">₹0.00</span>
            </div>
            <a href="<?php echo BASE_URL; ?>/checkout.php" class="checkout-btn">Proceed to Checkout</a>
        </div>
    </div>
</nav>

<style>
  /* ======================
      NAVBAR BASE STYLING
  ====================== */
  .liyas-navbar {
    width: 100%;
    background: transparent;
    padding: 2.5rem 2rem;
    z-index: 1100;
    transition: background 0.3s ease, box-shadow 0.3s ease, padding 0.3s ease;
  }

  /* Navbar with background on scroll */
  .liyas-navbar.scrolled {
    backdrop-filter: blur(10px);
  }

  /* Center Nav Links */
  .nav-items-placeholder {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .nav-items-placeholder .nav-link {
    font-weight: 500;
    font-size: 1.05rem;
    color: #000000e6;
    text-decoration: none;
    transition: color 0.3s ease;
    letter-spacing: 0.5px;
    position: relative;
    padding: 0.5rem 0;
  }

  .nav-items-placeholder .nav-link:hover {
    color: rgba(74, 210, 226, 1);
  }

  /* Tablet adjustments */
  @media (min-width: 768px) and (max-width: 991px) {
    .liyas-navbar {
      padding: 1.8rem 1.5rem;
    }
    .nav-items-placeholder .nav-link {
      font-size: 0.95rem;
    }
    .nav-items-placeholder {
      gap: 1.5rem !important;
    }
  }


  /* ======================
      HAMBURGER MENU
  ====================== */
  .navbar-toggler {
    background: none;
    border: none;
    cursor: pointer;
    width: 44px;
    height: 44px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    /* Better touch target */
    min-width: 44px;
    min-height: 44px;
    z-index: 1101;
  }

  .hamburger-icon,
  .hamburger-icon::before,
  .hamburger-icon::after {
    content: "";
    display: block;
    width: 26px;
    height: 2.5px;
    background-color: #0f172a;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    transition: all 0.3s ease-in-out;
    border-radius: 2px;
  }

  .hamburger-icon {
    top: 50%;
    transform: translate(-50%, -50%);
  }

  .hamburger-icon::before {
    top: -9px;
    transform: translateX(-50%);
  }

  .hamburger-icon::after {
    top: 9px;
    transform: translateX(-50%);
  }

  .navbar-toggler.active .hamburger-icon {
    background-color: transparent;
  }

  .navbar-toggler.active .hamburger-icon::before {
    transform: translate(-50%, 0) rotate(45deg);
    top: 0;
  }

  .navbar-toggler.active .hamburger-icon::after {
    transform: translate(-50%, 0) rotate(-45deg);
    top: 0;
  }

  /* Active state color change */
  .navbar-toggler.active .hamburger-icon::before,
  .navbar-toggler.active .hamburger-icon::after {
    background-color: #0f172a;
  }

  /* ======================
      MOBILE MENU OVERLAY
  ====================== */
  .mobile-menu {
    position: fixed;
    top: 0;
    right: -100%;
    width: 100%;
    height: 100vh;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    display: flex;
    justify-content: center;
    align-items: center;
    transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1099;
    overflow-y: auto;
  }

  .mobile-menu.active {
    right: 0;
  }

  .mobile-menu ul {
    list-style: none;
    padding: 0;
    text-align: center;
    width: 100%;
    max-width: 300px;
  }

  .mobile-menu ul li {
    margin: 1.5rem 0;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.3s ease, transform 0.3s ease;
  }

  .mobile-menu.active ul li {
    opacity: 1;
    transform: translateY(0);
  }

  .mobile-menu.active ul li:nth-child(1) { transition-delay: 0.1s; }
  .mobile-menu.active ul li:nth-child(2) { transition-delay: 0.15s; }
  .mobile-menu.active ul li:nth-child(3) { transition-delay: 0.2s; }
  .mobile-menu.active ul li:nth-child(4) { transition-delay: 0.25s; }

  .mobile-menu ul .nav-link {
    font-size: clamp(1.25rem, 5vw, 1.75rem);
    font-weight: 600;
    color: #0f172a;
    text-decoration: none;
    transition: color 0.3s ease, transform 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    min-height: 44px;
  }

  .mobile-menu ul .nav-link:active {
    transform: scale(0.95);
    background: rgba(74, 210, 226, 0.1);
  }

  .mobile-menu ul .nav-link:hover {
    color: rgba(74, 210, 226, 1);
  }

  /* ======================
      RESPONSIVE
  ====================== */
  @media (max-width: 767px) {
    .liyas-navbar {
      padding: 1rem 1rem;
    }
    
    .liyas-navbar.scrolled {
      padding: 0.8rem 1rem;
    }

    .nav-items-placeholder {
      display: none !important;
    }

    .navbar-toggler {
      width: 40px;
      height: 40px;
      min-width: 40px;
      min-height: 40px;
    }

    .hamburger-icon,
    .hamburger-icon::before,
    .hamburger-icon::after {
      width: 24px;
      height: 2.5px;
    }

    .mobile-menu ul li {
      margin: 1.25rem 0;
    }

    .mobile-menu ul .nav-link {
      padding: 0.625rem 1.25rem;
      font-size: 1.35rem;
    }
  }

  /* Extra small devices */
  @media (max-width: 375px) {
    .liyas-navbar {
      padding: 0.875rem 0.875rem;
    }
    
    .liyas-navbar.scrolled {
      padding: 0.75rem 0.875rem;
    }
  }

  /* Prevent body scroll when menu is open */
  body.no-scroll {
    overflow: hidden;
    position: fixed;
    width: 100%;
  }
</style>

<script>
  // Mobile menu toggle logic
  document.addEventListener('DOMContentLoaded', function () {
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const navbar = document.querySelector('.liyas-navbar');

    // Toggle mobile menu
    if (mobileBtn && mobileMenu) {
      mobileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        mobileBtn.classList.toggle('active');
        mobileMenu.classList.toggle('active');
        document.body.classList.toggle('no-scroll');
      });

      // Close when clicking any link
      document.querySelectorAll('.mobile-menu .nav-link').forEach(link => {
        link.addEventListener('click', () => {
          mobileBtn.classList.remove('active');
          mobileMenu.classList.remove('active');
          document.body.classList.remove('no-scroll');
        });
      });

      // Close menu when clicking outside
      document.addEventListener('click', (e) => {
        if (mobileMenu.classList.contains('active') && 
            !mobileMenu.contains(e.target) && 
            !mobileBtn.contains(e.target)) {
          mobileBtn.classList.remove('active');
          mobileMenu.classList.remove('active');
          document.body.classList.remove('no-scroll');
        }
      });

      // Close menu on escape key
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
          mobileBtn.classList.remove('active');
          mobileMenu.classList.remove('active');
          document.body.classList.remove('no-scroll');
        }
      });
    }

    // Navbar scroll effect
    if (navbar) {
      let lastScroll = 0;
      window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 50) {
          navbar.classList.add('scrolled');
        } else {
          navbar.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
      });
    }

    // Handle smooth scrolling for anchor links (same page)
    document.querySelectorAll('.nav-link[href^="#"]').forEach(link => {
      link.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href && href.startsWith('#')) {
          const targetId = href.substring(1);
          const targetElement = document.getElementById(targetId);
          
          if (targetElement) {
            e.preventDefault();
            targetElement.scrollIntoView({ 
              behavior: 'smooth', 
              block: 'start' 
            });
            
            // Close mobile menu if open
            if (mobileMenu && mobileMenu.classList.contains('active')) {
              mobileBtn.classList.remove('active');
              mobileMenu.classList.remove('active');
              document.body.classList.remove('no-scroll');
            }
          }
        }
      });
    });

    // Handle anchor scrolling after page load (for cross-page navigation)
    if (window.location.hash) {
      const hash = window.location.hash.substring(1);
      setTimeout(() => {
        const targetElement = document.getElementById(hash);
        if (targetElement) {
          targetElement.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
          });
        }
      }, 100);
    }
  });
</script>
