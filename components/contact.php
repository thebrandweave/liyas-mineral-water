<?php
/**
 * Contact Us component – Flow Layout (No Card)
 * Brand: Liyas Mineral Water
 */
?>
<section class="contact-section" id="contact" data-aos="fade-up">
    <div class="contact-bg-fixed"></div>

    <div class="container position-relative">

        <!-- Watermark -->
        <div class="contact-watermark">LIYAS</div>

        <!-- Form -->
        <form action="#" method="post" class="contact-form" data-aos="fade-up">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-12">
                            <input type="text" class="form-control contact-input" name="name" placeholder="Your name" required>
                        </div>
                        <div class="col-12">
                            <input type="email" class="form-control contact-input" name="email" placeholder="Where can we reach you?" required>
                        </div>
                        <div class="col-12">
                            <input type="tel" class="form-control contact-input" name="phone" placeholder="Contact number (optional)">
                        </div>
                        <div class="col-12">
                            <select class="form-control contact-input" name="reason" required>
                                <option value="">Reason for contacting</option>
                                <option>Bulk Orders</option>
                                <option>Distributorship</option>
                                <option>General Enquiry</option>
                                <option>Feedback</option>
                                <option>Support</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <textarea class="form-control contact-textarea h-100" name="message" placeholder="Tell us how we can help you…" rows="9" required></textarea>
                </div>
            </div>

            <div class="text-center mt-5">
                <button type="submit" class="btn contact-submit px-5">
                    Send Message
                </button>
                <p class="contact-note">
                    We usually respond within 24 hours.
                </p>
            </div>
        </form>
    </div>

    <!-- Wave -->
    <svg class="wave-svg" viewBox="0 0 1440 390" xmlns="http://www.w3.org/2000/svg">
        <path fill="#ffffff" d="M0,400L0,150C94.7,117.9,189.4,85.8,277,76
        C364.5,66.1,444.8,78.4,507,92
        C569.1,105.5,613,120.3,694,122
        C775,123.6,893.1,112.2,986,128
        C1078.8,143.7,1146.5,186.7,1218,195
        C1289.4,203.2,1364.7,176.6,1440,150
        L1440,400L0,400Z"/>
    </svg>

    <style>
        .contact-section {
            padding: var(--space-xxxl) 0 0;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
        }

        .contact-bg-fixed {
            position: absolute;
            inset: 0;
            background: url('https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1600&q=80') center/cover no-repeat;
            z-index: -2;
        }

        .contact-bg-fixed::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(240,249,255,.95), rgba(255,255,255,.9));
        }

        /* FORM FLOW */
        .contact-form {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            padding: 3rem 0;
        }

        /* Soft glow instead of card */
        .contact-form::before {
            content: "";
            position: absolute;
            inset: -60px;
            background: radial-gradient(circle at center,
                rgba(74,210,226,0.12),
                transparent 70%);
            z-index: -1;
        }

        .contact-form .form-control {
            background: rgba(255,255,255,0.88);
            border: 1px solid #e6f0f4;
            border-radius: 16px;
            padding: 15px 18px;
            transition: all .3s ease;
        }

        .contact-form .form-control:focus {
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 12px 30px rgba(74,210,226,.25);
        }

        .contact-textarea {
            resize: none;
        }

        /* CTA */
        .contact-submit {
            background: linear-gradient(135deg, #3bb6c4, #4ad2e2);
            color: #fff;
            border-radius: 50px;
            padding: 14px 44px;
            font-weight: 600;
            transition: .3s;
        }

        .contact-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(74,210,226,.35);
        }

        .contact-note {
            margin-top: .75rem;
            font-size: .9rem;
            color: var(--secondary);
        }

        /* Watermark */
        .contact-watermark {
            position: absolute;
            bottom: 40px;
            right: 30px;
            font-size: 6rem;
            font-weight: 800;
            color: rgba(74,210,226,.08);
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .contact-form {
                padding: 2rem 0;
            }
            .contact-watermark {
                font-size: 3.5rem;
            }
        }
    </style>
</section>
