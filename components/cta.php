<?php
/**
 * Call to Action Section component
 */
?>
<section class="cta-section" id="cta" data-aos="fade-up">
    <div class="cta-content">
        <h2 class="cta-title">Ready to Hydrate?</h2>
        <p class="cta-text">
            Experience the purest water that nature has to offer. 
            Join thousands of satisfied customers who trust Liyas for their daily hydration needs.
        </p>
        <div class="cta-buttons">
            <a href="#contact" class="cta-button primary">Order Now</a>
            <a href="#about" class="cta-button secondary">Learn More</a>
        </div>
    </div>
    


    <style>
        .cta-section {
            background: linear-gradient(135deg, #0ea5e9 0%, #10b981 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }

        .cta-title {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 700;
            margin-bottom: 20px;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .cta-text {
            font-size: clamp(1.1rem, 2.5vw, 1.4rem);
            line-height: 1.6;
            margin-bottom: 40px;
            color: rgba(255, 255, 255, 0.95);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-button {
            padding: 15px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .cta-button.primary {
            background: white;
            color: #0ea5e9;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }

        .cta-button.primary:hover {
            background: #10b981;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .cta-button.secondary {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.8);
        }

        .cta-button.secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            transform: translateY(-3px);
        }

        .cta-wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: auto;
            z-index: 1;
        }

        @media (max-width: 767px) {
            .cta-section {
                padding: 80px 0;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .cta-button {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</section>
