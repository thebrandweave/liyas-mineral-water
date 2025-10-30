<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIYAS Mineral Water - Coming Soon</title>
    <meta name="description" content="Our new website is coming soon! Stay tuned for the launch of LIYAS Mineral Water.">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        /* Base Styles */
        :root {
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
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

        /* Fallback Background*/
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
            text-shadow: 0 0 20px rgba(14, 165, 233, 0.3);
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
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.3);
        }

        /* Responsive Styles for Social Icons */
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

        /* Accessibility: Focus styles */
        .social-icon:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        /* Accessibility: Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .social-icon {
                transition: none;
            }
        }
    </style>
</head>
<body>

    <!-- Video Background with multiple format support -->
    <video class="video-background" autoplay muted loop playsinline id="bgVideo" preload="auto">
        <source src="assets/videos/coming-soon-bg.mp4" type="video/mp4">
        <source src="assets/videos/coming-soon-bg.webm" type="video/webm">
        <source src="assets/videos/coming-soon-bg.ogv" type="video/ogg">
        Your browser does not support the video tag.
    </video>
    
    <div class="overlay"></div>

    <main class="coming-soon-container">
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
            let isReversing = false;
            let reverseInterval = null;

            // Check browser support
            function checkVideoSupport() {
                if (!video || !video.canPlayType) {
                    return false;
                }
                
                const mp4Support = video.canPlayType('video/mp4');
                const webmSupport = video.canPlayType('video/webm');
                const oggSupport = video.canPlayType('video/ogg');
                
                return mp4Support !== '' || webmSupport !== '' || oggSupport !== '';
            }

            // Fallback to image if video is not supported
            function useFallback() {
                console.log('Video not supported, using fallback image');
                if (video) video.style.display = 'none';
            }

            if (!checkVideoSupport()) {
                useFallback();
                return;
            }

            // Handle video load errors
            video.addEventListener('error', function(e) {
                console.error('Video loading error:', e);
                useFallback();
            }, true);

            // Handle autoplay
            video.addEventListener('loadedmetadata', function() {
                const playPromise = video.play();
                
                if (playPromise !== undefined) {
                    playPromise.catch(function(error) {
                        console.log('Autoplay prevented, will play on click');
                        document.body.addEventListener('click', function playOnClick() {
                            video.play();
                            document.body.removeEventListener('click', playOnClick);
                        }, { once: true });
                    });
                }
            });

            // When video ends, start reversing
            video.addEventListener('ended', function() {
                console.log('Video ended, starting reverse');
                startReverse();
            });

            function startReverse() {
                if (isReversing) return;
                
                isReversing = true;
                const fps = 60;
                const interval = 1000 / fps;
                const step = video.duration / (fps * (video.duration)); // Smooth step calculation
                const intervalDuration = 1000 / fps;
                const step = intervalDuration / 1000; // The step should be the interval duration in seconds
                
                video.pause();
                if (!video.paused) video.pause();
                
                reverseInterval = setInterval(function() {
                    if (video.currentTime <= 0) {
                        clearInterval(reverseInterval);
                        reverseInterval = null;
                        isReversing = false;
                        video.currentTime = 0;
                        console.log('Reverse complete, playing forward');
                        video.play().catch(function(e) {
                            console.error('Play error after reverse:', e);
                        });
                    } else {
                        video.currentTime = Math.max(0, video.currentTime - step);
                    }
                }, interval);
                }, intervalDuration);
            }

            // Handle page visibility
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    if (reverseInterval) {
                        clearInterval(reverseInterval);
                        reverseInterval = null;
                        isReversing = false;
                    }
                    video.pause();
                } else {
                    if (!isReversing) {
                        video.play().catch(function(e) {
                            console.error('Play error on visibility change:', e);
                        });
                    } else {
                        startReverse();
                    }
                }
            });

            // Cleanup
            window.addEventListener('beforeunload', function() {
                if (reverseInterval) {
                    clearInterval(reverseInterval);
                }
                video.pause();
            });

        })();
    </script>

</body>
</html>