<?php
// Calculate base path for assets based on where this component is included from
$script_path = $_SERVER['SCRIPT_NAME'];
$script_dir = dirname($script_path);
$path_segments = array_filter(explode('/', $script_dir));
$is_subdirectory = (count($path_segments) > 1);
$asset_base = $is_subdirectory ? '../' : '';
?>
<section class="products-list">
  <div class="container">
    <h2 class="section-title">Our Premium Bottles</h2>
    <p class="section-subtitle">Choose freshness — every drop matters</p>

    <div class="product-grid">
      <div class="product-card">
        <img src="<?php echo $asset_base; ?>assets/images/liyas-bottle.png" alt="Mineral Water">
        <div class="product-info">
          <p class="category">Classic</p>
          <h4>Chobani Complete Vanilla Greek</h4>
          <div class="rating">⭐⭐⭐⭐☆ <span>(4.0)</span></div>
          <p class="by">By <span>PureNest</span></p>
          <div class="price-section">
            <p class="new-price">₹54.85</p>
            <p class="old-price">₹55.8</p>
          </div>
          <button class="add-btn">Add</button>
        </div>
      </div>

      <div class="product-card">
        <img src="<?php echo $asset_base; ?>assets/images/liyas-bottle.png" alt="Ginger Ale">
        <div class="product-info">
          <p class="category">Sparkling</p>
          <h4>Canada Dry Ginger Ale - 2 L Bottle</h4>
          <div class="rating">⭐⭐⭐⭐☆ <span>(4.0)</span></div>
          <p class="by">By <span>PureNest</span></p>
          <div class="price-section">
            <p class="new-price">₹32.85</p>
            <p class="old-price">₹33.8</p>
          </div>
          <button class="add-btn">Add</button>
        </div>
      </div>

      <div class="product-card">
        <img src="<?php echo $asset_base; ?>assets/images/liyas-bottle.png" alt="Small Water Bottle">
        <div class="product-info">
          <p class="category">Mini Pack</p>
          <h4>PureLife Natural Spring Water - 1L</h4>
          <div class="rating">⭐⭐⭐⭐☆ <span>(4.3)</span></div>
          <p class="by">By <span>PureNest</span></p>
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
