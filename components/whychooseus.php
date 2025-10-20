<?php
/**
 * Why Choose Us component (blue theme, water content, bottle image)
 */
?>
<section class="why-choose-section" id="why-choose" data-aos="fade-up">
	<div class="container">
		<div class="why-title" data-aos="fade-up">
			<div class="why-subtitle">WHY CHOOSE LIYAS</div>
			<h2 class="why-heading">PURE, SAFE, AND TRUSTED HYDRATION</h2>
			<p class="why-intro">From advanced purification to fast doorstep delivery, we keep your family hydrated with crystal‑clear, great‑tasting water.</p>
		</div>

		<div class="features-grid">
			<div class="features-column left">
				<div class="feature-item" data-aos="fade-up" data-aos-delay="0">
					<span class="icon-emoji">💧</span>
					<h3>Multi‑Stage Purification</h3>
					<p>RO + UV + Mineral balance for water that is pure, safe, and naturally refreshing.</p>
				</div>
				<div class="feature-item" data-aos="fade-up" data-aos-delay="100">
					<span class="icon-emoji">🚚</span>
					<h3>On‑Time Delivery</h3>
					<p>Reliable doorstep delivery with flexible scheduling so you never run out of water.</p>
				</div>
			</div>

			<div class="image-column" data-aos="zoom-in" data-aos-delay="50">
				<div class="image-wrapper">
					<img src="assets/images/bottle.png" alt="Liyas Mineral Water Bottle">
				</div>
			</div>

			<div class="features-column right">
				<div class="feature-item" data-aos="fade-up" data-aos-delay="0">
					<span class="icon-emoji">🛡️</span>
					<h3>Hygienic & Sealed</h3>
					<p>Contact‑less bottling and tamper‑proof seals preserve freshness from plant to your glass.</p>
				</div>
				<div class="feature-item" data-aos="fade-up" data-aos-delay="100">
					<span class="icon-emoji">🌿</span>
					<h3>Eco‑Conscious</h3>
					<p>Recyclable bottles and optimized routes reduce plastic waste and carbon footprint.</p>
				</div>
			</div>
		</div>

	</div>

	<style>
		:root {
			--why-text-primary: #0b2e4e;
			--why-text-secondary: #5a6b7b;
			--why-accent: #0ea5e9; /* Site blue */
			--why-white: #ffffff;
			--why-bg: #f6fbff;
		}

		.why-choose-section {
			position: relative;
			padding: 80px 0;
			background: var(--why-white);
			border-radius: 20px;
		}

		.why-title { text-align: center; margin-bottom: 30px; }
		.why-subtitle { color: var(--why-accent); font-weight: 600; letter-spacing: 1px; }
		.why-heading { font-weight: 700; color: var(--why-text-primary); }
		.why-intro { color: var(--why-text-secondary); max-width: 720px; margin: 0 auto; }

		.features-grid { display: grid; gap: 2rem; align-items: center; grid-template-columns: 1fr; }
		.features-column { display: flex; flex-direction: column; gap: 2rem; }
		.features-column.left { order: 0; }
		.image-column { order: 1; }
		.features-column.right { order: 2; }

		.feature-item { background: white; display: flex; flex-direction: column; align-items: center; text-align: center; gap: .5rem; }
		.feature-item h3 { color: var(--why-text-primary); font-weight: 700; font-size: 1.125rem; }
		.feature-item p { color: var(--why-text-secondary); font-size: .95rem; max-width: 320px; }
		.icon-emoji { font-size: 2.2rem; color: var(--why-accent); line-height: 1; }

		.image-wrapper { position: relative; max-width: 360px; margin: 0 auto; }
		.image-wrapper img { max-width: 100%; height: auto; display: block; position: relative; z-index: 1; }

		@media (min-width: 1024px) {
			.features-grid { grid-template-columns: 1fr auto 1fr; }
			.features-column { justify-content: space-around; height: 100%; }
			.features-column.left .feature-item { align-items: flex-end; text-align: right; }
			.features-column.right .feature-item { align-items: flex-start; text-align: left; }
			.features-column.left { order: 0; }
			.image-column { order: 0; }
			.features-column.right { order: 0; }
		}
	</style>
</section>


