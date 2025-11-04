<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About | Company</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #ffffff;
      color: #1e293b;
      line-height: 1.6;
    }

    .about-section {
      display: flex;
      align-items: center;
      justify-content: center;
      flex-wrap: wrap;
      gap: 4rem;
      padding: 6rem 8%;
    }

    .about-image img {
      width: 450px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .about-content {
      max-width: 500px;
    }

    .about-content .tag {
      display: inline-block;
      background-color: #e2e8f0;
      color: #0f172a;
      font-weight: 600;
      font-size: 0.9rem;
      border-radius: 999px;
      padding: 5px 15px;
      margin-bottom: 1rem;
    }

    .about-content h2 {
      font-size: 2.2rem;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 1rem;
      line-height: 1.2;
    }

    .about-content p {
      color: #475569;
      margin-bottom: 1.8rem;
    }

    .about-content .btn {
      display: inline-block;
      background-color: #1e3a8a;
      color: #fff;
      font-weight: 600;
      text-decoration: none;
      padding: 12px 28px;
      border-radius: 8px;
      transition: 0.3s;
    }

    .about-content .btn:hover {
      background-color: #1d4ed8;
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

    @media (max-width: 900px) {
      .about-section {
        flex-direction: column;
        text-align: center;
      }
      .about-content {
        max-width: 90%;
      }
      .about-image img {
        width: 100%;
        max-width: 400px;
      }
    }
  </style>
</head>
<body>

  <!-- About Section -->
  <section class="about-section">
    <div class="about-image">
      <img src="https://i.ibb.co/MZzTqtZ/technician.jpg" alt="Technician">
    </div>

    <div class="about-content">
      <span class="tag">Welcome</span>
      <h2>Air Conditioning and Heating Specialists</h2>
      <p>
        Eu cupidatat sit dolore enim consequat veniam adipisicing et quis ut in eiusmod consectetur dolore qui aliqua sunt culpa ad qui mollit et irure nisi laborum commodo minim commodo occaecat ut sint dolor mollit culpa excepteur magna pariatur.
      </p>
      <a href="#" class="btn">About Company</a>
    </div>
  </section>

  <!-- Badge Row -->
  <div class="badge-row">
    <img src="https://i.ibb.co/Xt1z1pH/iso1.png" alt="ISO 14001">
    <img src="https://i.ibb.co/bbHYqW3/iso2.png" alt="ISO 9001">
    <img src="https://i.ibb.co/2F9WRd2/iso3.png" alt="ISO Certified">
    <img src="https://i.ibb.co/vHfVL7P/iso4.png" alt="ISO Quality">
    <img src="https://i.ibb.co/khd8jBL/iso5.png" alt="ISO 22000">
  </div>

</body>
</html>
