<style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
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
    }

    .about-content {
      max-width: 500px;
    }

    .about-content .tag {
      display: inline-block;
      background-color: rgba(14, 165, 233, 0.12);
      color: #0b2e4e;
      font-weight: 600;
      font-size: 0.9rem;
      border-radius: 999px;
      padding: 5px 15px;
      margin-bottom: 1rem;
    }

    .about-content h2 {
      font-size: clamp(1.5rem, 4vw, 2.25rem);
      font-weight: 700;
      color: #0b2e4e;
      margin-bottom: 1rem;
      line-height: 1.2;
    }

    .about-content p {
      color: #5a6b7b;
      margin-bottom: 1.8rem;
    }

    .about-content .btn {
      display: inline-block;
      background-color: #0ea5e9;
      color: #fff;
      font-weight: 600;
      text-decoration: none;
      padding: 12px 28px;
      border-radius: 8px;
      transition: 0.3s;
    }

    .about-content .btn:hover {
      background-color: #0284c7;
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


  <!-- About Section -->
  <section class="about-section" id="about" data-aos="fade-up">
    <div class="about-image" data-aos="fade-right" data-aos-delay="50">
      <img src="assets/images/bottle.png" alt="Liyas Mineral Water Bottle">
    </div>

    <div class="about-content" data-aos="fade-left" data-aos-delay="100">
      <span class="tag">Welcome</span>
      <h2>Pure, Refreshing, and Trusted</h2>
      <p>
        Eu cupidatat sit dolore enim consequat veniam adipisicing et quis ut in eiusmod consectetur dolore qui aliqua sunt culpa ad qui mollit et irure nisi laborum commodo minim commodo occaecat ut sint dolor mollit culpa excepteur magna pariatur.
      </p>
      <a href="#" class="btn">About Company</a>
    </div>
  </section>