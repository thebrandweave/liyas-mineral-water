<?php
/**
 * Why Choose Us component
 */
?>
<section class="why-choose-section" id="why-choose" data-aos="fade-up">
    <div class="container">
        <div class="why-title fade-in" data-aos="fade-up">
            <div class="why-subtitle">WHY CHOOSE LIYAS</div>
            <h2 class="why-heading">PURE, SAFE, AND TRUSTED HYDRATION</h2>
            <p class="why-intro">We combine advanced purification with sustainable practices to deliver premium-quality water you can trust.</p>
        </div>

        <div class="row g-4 mt-2">
            <div class="col-md-6 col-lg-4">
                <div class="why-card product-card p-4 h-100" data-aos="fade-up" data-aos-delay="0">
                    <div class="why-icon">
                        <i class="fas fa-tint"></i>
                    </div>
                    <h5 class="mt-3">Pure RO + UV + Mineral Balance</h5>
                    <p>Multi-stage purification combines RO and UV with essential minerals restored for great taste and health.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="why-card product-card p-4 h-100" data-aos="fade-up" data-aos-delay="100">
                    <div class="why-icon">
                        <i class="fas fa-shield-heart"></i>
                    </div>
                    <h5 class="mt-3">Hygienically Packed & Sealed</h5>
                    <p>Automated bottling ensures zero contamination and tamper-proof freshness from plant to your door.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="why-card product-card p-4 h-100" data-aos="fade-up" data-aos-delay="200">
                    <div class="why-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h5 class="mt-3">Eco‑friendly Bottles</h5>
                    <p>We use recyclable materials and optimize packaging to reduce plastic and our carbon footprint.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="why-card product-card p-4 h-100" data-aos="fade-up" data-aos-delay="0">
                    <div class="why-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="mt-3">Trusted by 1000+ Customers</h5>
                    <p>Serving homes and businesses with on-time delivery and consistently high quality water.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="why-card product-card p-4 h-100" data-aos="fade-up" data-aos-delay="100">
                    <div class="why-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h5 class="mt-3">Certified Quality</h5>
                    <p>Meets standards set by BIS and local water authorities with frequent lab testing.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="why-card product-card p-4 h-100" data-aos="fade-up" data-aos-delay="200">
                    <div class="why-icon">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <h5 class="mt-3">Fast Delivery Service</h5>
                    <p>Quick, reliable doorstep delivery with flexible scheduling options for your convenience.</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .why-choose-section {
            position: relative;
            padding: 80px 0;
            background: #ffffff;
        }

        .why-subtitle {
            color: #00a2ed;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .why-heading {
            font-weight: 700;
            color: #0b2e4e;
        }

        .why-intro {
            color: #5a6b7b;
            max-width: 720px;
        }

        .why-card {
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 16px;
            background: #ffffff;
            transition: transform .3s ease, box-shadow .3s ease;
        }

        .why-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 30px rgba(0, 122, 212, 0.12);
        }

        .why-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #00a2ed 0%, #0078d4 100%);
            color: #fff;
            font-size: 22px;
            box-shadow: 0 10px 20px rgba(0, 120, 212, .25);
        }

        /* Match scroll reveal from index */
        .why-choose-section .product-card { opacity: 0; transform: translateY(50px); }
        .why-choose-section .product-card.visible { opacity: 1; transform: translateY(0); }

        @media (max-width: 767.98px) {
            .why-choose-section { padding: 56px 0; }
        }
    </style>
</section>


