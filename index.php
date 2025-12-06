<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIYAS Mineral Water - Coming Soon</title>
    <meta name="description" content="Our new website is coming soon! Stay tuned for the launch of LIYAS Mineral Water.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="assets/images/logo/logo-bg.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="assets/images/logo/logo-bg.jpg">
    <link rel="apple-touch-icon" href="assets/images/logo/logo-bg.jpg">
    <link rel="icon" type="image/jpeg" sizes="32x32" href="assets/images/logo/logo-bg.jpg">
    <link rel="icon" type="image/jpeg" sizes="16x16" href="assets/images/logo/logo-bg.jpg">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        /* Base Styles */
        :root {
            --primary: #4ad2e2;
            --primary-dark: #2cbac9;
            --dark: #0f172a;
            --white: #ffffff;
            --secondary: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--dark);
            color: var(--white);
            overflow: hidden;
            line-height: 1.6;
        }

        /* Loader */
        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            flex-direction: column;
            transition: opacity 0.2s ease;
        }

        .loader-ring {
            width: 80px;
            height: 80px;
            border: 4px solid rgba(255, 255, 255, 0.2);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
            box-shadow: 0 0 20px rgba(74, 210, 226, 0.3);
        }

        .loader-text {
            margin-top: 1rem;
            font-size: 1.1rem;
            color: var(--white);
            letter-spacing: 1px;
            opacity: 0.8;
            animation: fadeInText 1.5s ease-in-out infinite alternate;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeInText {
            0% { opacity: 0.4; }
            100% { opacity: 1; }
        }

        /* Video Background */
        .video-background {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: translate(-50%, -50%);
            z-index: -2;
        }

        /* Fallback Background */
        .fallback-background {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: translate(-50%, -50%);
            z-index: -2;
            display: none;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.47);
            z-index: -1;
        }

        /* Main Container */
        .coming-soon-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 2rem;
            animation: fadeIn 1.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Logo */
        .logo {
            margin-bottom: 2rem;
        }

        .logo img {
            width: clamp(80px, 15vw, 120px);
            height: auto;
        }

        /* Content */
        .coming-soon-title {
            font-size: clamp(2.5rem, 8vw, 5rem);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1rem;
            color: var(--primary);
            text-shadow: 0 0 20px rgba(74, 210, 226, 0.3);
        }

        .coming-soon-text {
            font-size: clamp(1rem, 3vw, 1.25rem);
            max-width: 600px;
            margin: 0 auto 2.5rem auto;
            color: rgba(255, 255, 255, 0.85);
        }

        /* Social Links */
        .social-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .social-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            color: var(--white);
            font-size: 1.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .social-icon:hover {
            background: var(--primary);
            border-color: var(--primary);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(74, 210, 226, 0.3);
        }

        @media (max-width: 576px) {
            .social-links {
                gap: 1rem;
                margin-top: 1.5rem;
            }

            .social-icon {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>

    <!-- Loader -->
    <div id="loader">
        <div class="loader-ring"></div>
        <div class="loader-text">Preparing freshness...</div>
    </div>

    <!-- Video Background -->
    <video class="video-background" autoplay muted loop playsinline id="bgVideo" preload="auto">
        <source src="assets/videos/coming-soon-bg.mp4" type="video/mp4">
        <source src="assets/videos/coming-soon-bg.webm" type="video/webm">
        <source src="assets/videos/coming-soon-bg.ogv" type="video/ogg">
        Your browser does not support the video tag.
    </video>

    <!-- Fallback -->
    <img src="assets/images/coming-soon-fallback.jpg" alt="Background Image" class="fallback-background">

    <div class="overlay"></div>

    <main class="coming-soon-container" style="display:none;">
        <div class="logo">
            <img src="assets/images/logo/logo.png" alt="LIYAS Mineral Water Logo">
        </div>

        <h1 class="coming-soon-title">Coming Soon</h1>
        <p class="coming-soon-text">
            We're working hard to bring you a fresh and hydrating new experience. Stay tuned for our launch!!!
        </p>

        <div class="social-links">
            <a href="#" class="social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-icon" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
            <a href="#" class="social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        </div>
    </main>

    <script>
        (function() {
            'use strict';

            const video = document.getElementById('bgVideo');
            const loader = document.getElementById('loader');
            const main = document.querySelector('.coming-soon-container');
            const fallback = document.querySelector('.fallback-background');

            function checkVideoSupport() {
                if (!video || !video.canPlayType) return false;
                return video.canPlayType('video/mp4') || video.canPlayType('video/webm') || video.canPlayType('video/ogg');
            }

            function useFallback() {
                if (video) video.style.display = 'none';
                fallback.style.display = 'block';
                showContent();
            }

            function showContent() {
                loader.style.opacity = '0';
                setTimeout(() => {
                    loader.style.display = 'none';
                    main.style.display = 'flex';
                }, 100); // very fast fade
            }

            // Show content as soon as video starts playing
            video.addEventListener('canplay', showContent);

            // Video error
            video.addEventListener('error', function() {
                useFallback();
            });

            // If browser doesn't support video
            if (!checkVideoSupport()) {
                useFallback();
            }
        })();
    </script>

</body>
</html>
