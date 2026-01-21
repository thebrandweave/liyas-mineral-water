<?php
require_once 'config/config.php';
$page_title = 'Contact Us | LIYAS Mineral Water';

// Fetch social links
$social_links_stmt = $pdo->query("SELECT * FROM social_links WHERE status = 'active' ORDER BY sort_order ASC");
$social_links = $social_links_stmt->fetchAll(PDO::FETCH_ASSOC);

include 'components/public-header.php';
?>

<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@700&display=swap" rel="stylesheet">


<div class="social-sidebar">
    <?php foreach ($social_links as $link): ?>
        <a href="<?= htmlspecialchars($link['url']) ?>" class="social-icon" target="_blank" aria-label="<?= htmlspecialchars($link['platform']) ?>">
            <i class="<?= htmlspecialchars($link['icon_class']) ?>"></i>
        </a>
    <?php endforeach; ?>
</div>

<style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: #ffffffff;
      color: #000000ff;
      font-family: 'Inter', sans-serif;
      overflow-x: hidden;
    }

    section {
      min-height: 120vh;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 10vw;
      position: relative;
    }

    .left-content {
      flex: 1;
    }

    p.label {
      letter-spacing: 3px;
      color: #000000ff;
      margin-bottom: 1rem;
      font-size: 0.9rem;
    }

    h1 {
      font-family: 'Oswald', sans-serif;
      font-size: 10vw;
      font-weight: 700;
      line-height: 0.9;
      text-transform: uppercase;
      opacity: 0;
      transform: translateY(100px);
      transition: all 1s ease;
    }

    h1.show {
      opacity: 1;
      transform: translateY(0);
    }

    .right-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
    }

    .circle-btn {
      width: 220px;
      height: 220px;
      border: 3px solid #e74c3c;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      animation: rotateCircle 10s linear infinite;
      margin-bottom: 40px;
    }

    .circle-btn::before {
      content: '';
      position: absolute;
      width: 200px;
      height: 200px;
      border: 2px solid #e74c3c;
      border-radius: 50%;
    }

    @keyframes rotateCircle {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    .circle-btn span {
      font-family: 'Oswald', sans-serif;
      text-transform: uppercase;
      font-size: 1.3rem;
      transform: rotate(-15deg);
      color: #000000ff;
      font-weight: 700;
    }

    .right-text {
      font-size: 1rem;
      color: #000000ff;
      line-height: 1.6;
      max-width: 350px;
      opacity: 0;
      transform: translateY(50px);
      transition: all 1s ease;
    }

    .right-text.visible {
      opacity: 1;
      transform: translateY(0);
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(50px); }
      to { opacity: 1; transform: translateY(0); }
    }

    footer {
      text-align: center;
      padding: 80px 0;
      color: #777;
      font-size: 0.9rem;
    }
  </style>

  <section id="contact">
    <div class="left-content">
      <p class="label">CONTACT</p>
      <h1 id="title">LET'S<br>WORK<br>TOGETHER</h1>
    </div>

    <div class="right-content">
      <div class="circle-btn">
        <span>LET’S<br>CONNECT!</span>
      </div>
      <div class="right-text" id="rightText">
        FEELING GOOD ABOUT A NEW PROJECT?<br>
        WRITE ME WHAT’S IN YOUR MIND<br>
        AND LET’S TALK ABOUT IT!
      </div>
    </div>
  </section>

  <script>
    window.addEventListener("scroll", () => {
      const title = document.getElementById("title");
      const rightText = document.getElementById("rightText");
      const windowHeight = window.innerHeight;

      const titlePosition = title.getBoundingClientRect().top;
      const textPosition = rightText.getBoundingClientRect().top;

      if (titlePosition < windowHeight - 100) title.classList.add("show");
      if (textPosition < windowHeight - 50) rightText.classList.add("visible");
    });

    // Show immediately when page loads (for shorter pages)
    window.addEventListener("load", () => {
      document.getElementById("title").classList.add("show");
      document.getElementById("rightText").classList.add("visible");
    });
  </script>

<?php
include 'components/footer.php';
?>