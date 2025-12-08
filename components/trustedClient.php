<?php
// Calculate base path for assets based on where this component is included from
$script_path = $_SERVER['SCRIPT_NAME'];
$script_dir = dirname($script_path);
$path_segments = array_filter(explode('/', $script_dir));
$is_subdirectory = (count($path_segments) > 1);
$asset_base = $is_subdirectory ? '../' : '';
?>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: #ffffff;
      color: #1e293b;
      line-height: 1.6;
    }

    /* Badge Row */
    .badge-row {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      gap: 2rem;
      padding: 3rem 8%;
      background-color: #f8fafc;
    }

    .badge-row img {
      width: 110px;
      height: auto;
      transition: transform 0.3s ease;
    }

    .badge-row img:hover {
      transform: scale(1.1);
    }
  </style>

  <!-- Badge Row -->
  <div class="badge-row">
    <img src="<?php echo $asset_base; ?>assets/images/liyas-bottle.png" alt="ISO 14001">
    <img src="<?php echo $asset_base; ?>assets/images/liyas-bottle.png" alt="ISO 9001">
    <img src="<?php echo $asset_base; ?>assets/images/liyas-bottle.png" alt="ISO Certified">
    <img src="<?php echo $asset_base; ?>assets/images/liyas-bottle.png" alt="ISO Quality">
    <img src="<?php echo $asset_base; ?>assets/images/liyas-bottle.png" alt="ISO 22000">
  </div>
