<?php
$script_path = $_SERVER['SCRIPT_NAME'];
$script_dir = dirname($script_path);
$path_segments = array_filter(explode('/', $script_dir));
$is_subdirectory = (count($path_segments) > 1);
$asset_base = $is_subdirectory ? '../' : '';
?>

<style>
/* ================= FILTER BAR ================= */
.product-filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 3rem;
  flex-wrap: wrap;
  z-index: 1000 !important;
}

.product-filters input,
.product-filters select {
  padding: 12px 18px;
  border-radius: 30px;
  border: 1px solid #e2e8f0;
  font-size: 0.95rem;
  outline: none;
}

.product-filters input {
  flex: 1;
  min-width: 240px;
}

/* ================= PRODUCTS ================= */
.products-list {
  padding: 4rem 0;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 2.5rem;
}

/* CARD */
.product-card {
  background: #fff;
  border-radius: 20px;
  padding: 1.6rem 1.6rem 2rem;
  text-align: center;
  box-shadow: 0 15px 35px rgba(145, 140, 140, 0.06);
  position: relative;
}

/* SIZE BADGE */
.size-badge {
  position: absolute;
  top: 14px;
  left: 14px;
  background: #e6fbff;
  color: #0b2e4e;
  font-size: 0.7rem;
  padding: 5px 12px;
  border-radius: 20px;
  font-weight: 600;
}

/* IMAGE WRAPPER */
.product-image-wrap {
  height: 180px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
}

.product-image-wrap img {
  height: 160px;
  width: auto;
  object-fit: contain;
}

/* INFO */
.product-info .category {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #4ad2e2;
  font-weight: 600;
}

.product-info h4 {
  font-size: 1rem;
  margin: 0.5rem 0;
}

.rating {
  font-size: 0.85rem;
  color: #fbbf24;
}

.by {
  font-size: 0.8rem;
  color: #64748b;
}

.by span {
  font-weight: 600;
}

.price-section {
  margin: 1rem 0;
}

.new-price {
  font-weight: 700;
  color: #0b2e4e;
}

.old-price {
  font-size: 0.85rem;
  text-decoration: line-through;
  color: #94a3b8;
}

.add-btn {
  background: #4ad2e2;
  color: #fff;
  border: none;
  padding: 11px 28px;
  border-radius: 30px;
  cursor: pointer;
  font-weight: 600;
}
</style>

<section class="products-list">
  <div class="container">

    <!-- SEARCH & FILTER -->
    <div class="product-filters">
      <input type="text" id="searchInput" placeholder="Search bottle..." />

      <select id="litreFilter">
        <option value="">All Sizes</option>
        <option value="1">1 L</option>
        <option value="2">2 L</option>
        <option value="20">20 L</option>
      </select>

      <select id="categoryFilter">
        <option value="">All Categories</option>
        <option value="classic">Classic</option>
        <option value="sparkling">Sparkling</option>
        <option value="mini">Mini Pack</option>
      </select>
    </div>

    <!-- PRODUCT GRID -->
    <div class="product-grid">

      <!-- PRODUCT 1 -->
      <div class="product-card"
           data-name="chobani complete vanilla greek"
           data-litre="1"
           data-category="classic">

        <div class="size-badge">1 L</div>

        <div class="product-image-wrap">
          <img src="<?php echo $asset_base; ?>assets/images/liyas-bottle.png" alt="LIYAS 1L Bottle">
        </div>

        <div class="product-info">
          <p class="category">Classic</p>
          <h4>Chobani Complete Vanilla Greek</h4>
          <div class="rating">⭐⭐⭐⭐☆ <span>(4.0)</span></div>
          <p class="by">By <span>Liyas</span></p>
          <div class="price-section">
            <p class="new-price">₹54.85</p>
            <p class="old-price">₹55.80</p>
          </div>
          <button class="add-btn">Add</button>
        </div>
      </div>

      <!-- PRODUCT 2 -->
      <div class="product-card"
           data-name="canada dry ginger ale"
           data-litre="2"
           data-category="sparkling">

        <div class="size-badge">2 L</div>

        <div class="product-image-wrap">
          <img src="<?php echo $asset_base; ?>assets/images/liyas-bottle.png" alt="LIYAS 2L Bottle">
        </div>

        <div class="product-info">
          <p class="category">Sparkling</p>
          <h4>Canada Dry Ginger Ale – 2L</h4>
          <div class="rating">⭐⭐⭐⭐☆ <span>(4.0)</span></div>
          <p class="by">By <span>Liyas</span></p>
          <div class="price-section">
            <p class="new-price">₹32.85</p>
            <p class="old-price">₹33.80</p>
          </div>
          <button class="add-btn">Add</button>
        </div>
      </div>

      <!-- PRODUCT 3 -->
      <div class="product-card"
           data-name="purelife natural spring water"
           data-litre="1"
           data-category="mini">

        <div class="size-badge">1 L</div>

        <div class="product-image-wrap">
          <img src="<?php echo $asset_base; ?>assets/images/liyas-bottle.png" alt="LIYAS 1L Bottle">
        </div>

        <div class="product-info">
          <p class="category">Mini Pack</p>
          <h4>PureLife Natural Spring Water – 1L</h4>
          <div class="rating">⭐⭐⭐⭐☆ <span>(4.3)</span></div>
          <p class="by">By <span>Liyas</span></p>
          <div class="price-section">
            <p class="new-price">₹29.50</p>
            <p class="old-price">₹31.00</p>
          </div>
          <button class="add-btn">Add</button>
        </div>
      </div>

    </div>
  </div>
</section>

<script>
const searchInput = document.getElementById("searchInput");
const litreFilter = document.getElementById("litreFilter");
const categoryFilter = document.getElementById("categoryFilter");
const products = document.querySelectorAll(".product-card");

function filterProducts() {
  const searchValue = searchInput.value.toLowerCase();
  const litreValue = litreFilter.value;
  const categoryValue = categoryFilter.value;

  products.forEach(product => {
    const name = product.dataset.name;
    const litre = product.dataset.litre;
    const category = product.dataset.category;

    const match =
      name.includes(searchValue) &&
      (!litreValue || litre === litreValue) &&
      (!categoryValue || category === categoryValue);

    product.style.display = match ? "block" : "none";
  });
}

searchInput.addEventListener("input", filterProducts);
litreFilter.addEventListener("change", filterProducts);
categoryFilter.addEventListener("change", filterProducts);
</script>
