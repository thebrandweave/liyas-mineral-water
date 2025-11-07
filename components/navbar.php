<!-- components/navbar.php -->
<nav class="navbar fixed-top liyas-navbar">
  <div class="container d-flex justify-content-between align-items-center">

    <!-- Logo (Left Side) -->
    <!-- <a href="#home" class="navbar-brand logo-text d-flex align-items-center">
      <img src="assets/images/logo/logo.png" alt="Liyas Logo" class="navbar-logo">
    </a> -->

    <!-- Center Nav Items -->
    <ul class="navbar-nav flex-row gap-4 nav-items-placeholder d-none d-md-flex mx-auto">
      <li class="nav-item"><a href="#home" class="nav-link">Home</a></li>
      <li class="nav-item"><a href="#about" class="nav-link">About</a></li>
      <li class="nav-item"><a href="#products" class="nav-link">Products</a></li>
      <li class="nav-item"><a href="#contact" class="nav-link">Contact</a></li>
    </ul>

    <!-- Login Icon (Right Side) -->
    <!-- <a href="login.php" class="login-icon" title="Login">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
           stroke-width="2.2" stroke="currentColor" class="login-svg">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M15.75 9V5.25A2.25 2.25 0 0013.5 3H6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 006 21h7.5a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
      </svg>
    </a> -->

    <!-- Hamburger Menu (Mobile) -->
    <button class="navbar-toggler d-md-none" type="button" id="mobileMenuBtn" aria-label="Toggle navigation">
      <span class="hamburger-icon"></span>
    </button>

  </div>

  <!-- Mobile Menu Overlay -->
  <div class="mobile-menu" id="mobileMenu">
    <ul>
      <li><a href="#home" class="nav-link">Home</a></li>
      <li><a href="#about" class="nav-link">About</a></li>
      <li><a href="#products" class="nav-link">Products</a></li>
      <li><a href="#contact" class="nav-link">Contact</a></li>
    </ul>
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
    transition: background 0.3s ease, box-shadow 0.3s ease;
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
  }

  .nav-items-placeholder .nav-link:hover {
    color: rgba(14, 165, 233, 1);
  }


  /* ======================
      HAMBURGER MENU
  ====================== */
  .navbar-toggler {
    background: none;
    border: none;
    cursor: pointer;
    width: 40px;
    height: 40px;
    position: relative;
  }

  .hamburger-icon,
  .hamburger-icon::before,
  .hamburger-icon::after {
    content: "";
    display: block;
    width: 26px;
    height: 2px;
    background-color: #0f172a;
    position: absolute;
    left: 7px;
    transition: all 0.3s ease-in-out;
  }

  .hamburger-icon {
    top: 50%;
    transform: translateY(-50%);
  }

  .hamburger-icon::before {
    top: -8px;
  }

  .hamburger-icon::after {
    top: 8px;
  }

  .navbar-toggler.active .hamburger-icon {
    background-color: transparent;
  }

  .navbar-toggler.active .hamburger-icon::before {
    transform: rotate(45deg);
    top: 0;
  }

  .navbar-toggler.active .hamburger-icon::after {
    transform: rotate(-45deg);
    top: 0;
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
    background: rgba(255, 255, 255, 0.97);
    backdrop-filter: blur(8px);
    display: flex;
    justify-content: center;
    align-items: center;
    transition: right 0.4s ease-in-out;
    z-index: 1050;
  }

  .mobile-menu.active {
    right: 0;
  }

  .mobile-menu ul {
    list-style: none;
    padding: 0;
    text-align: center;
  }

  .mobile-menu ul li {
    margin: 20px 0;
  }

  .mobile-menu ul .nav-link {
    font-size: 1.5rem;
    font-weight: 600;
    color: #0f172a;
    text-decoration: none;
    transition: color 0.3s ease;
  }

  .mobile-menu ul .nav-link:hover {
    color: rgba(14, 165, 233, 1);
  }

  /* ======================
      RESPONSIVE
  ====================== */
  @media (max-width: 767px) {
    .liyas-navbar {
      padding: 1rem 1.2rem;
    }
    .nav-items-placeholder {
      display: none !important;
    }
  }
</style>

<script>
  // Mobile menu toggle logic
  document.addEventListener('DOMContentLoaded', function () {
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    mobileBtn.addEventListener('click', () => {
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
  });
</script>
