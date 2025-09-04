<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coming Soon | TRIPSORUS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #6a11cb 100%);
      color: white;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 20px;
      position: relative;
      overflow: hidden;
    }

    .background-shapes {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      overflow: hidden;
    }

    .shape {
      position: absolute;
      border-radius: 50%;
      opacity: 0.1;
    }

    .shape-1 {
      width: 300px;
      height: 300px;
      background: linear-gradient(#ff9a9e, #fad0c4);
      top: -150px;
      left: -150px;
    }

    .shape-2 {
      width: 200px;
      height: 200px;
      background: linear-gradient(#a1c4fd, #c2e9fb);
      bottom: -100px;
      right: -100px;
    }

    .shape-3 {
      width: 150px;
      height: 150px;
      background: linear-gradient(#ffecd2, #fcb69f);
      top: 50%;
      left: 10%;
    }

    .shape-4 {
      width: 100px;
      height: 100px;
      background: linear-gradient(#84fab0, #8fd3f4);
      bottom: 20%;
      right: 20%;
    }

    .container {
      max-width: 800px;
      padding: 40px;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.1);
      animation: fadeIn 1s ease-out;
    }

    .logo {
      font-size: 3.5rem;
      margin-bottom: 20px;
      color: #fff;
      text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
      animation: pulse 2s infinite;
    }

    h1 {
      font-size: 3rem;
      margin-bottom: 20px;
      background: linear-gradient(to right, #fff, #c2e9fb);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    p {
      font-size: 1.3rem;
      margin-bottom: 40px;
      line-height: 1.6;
      color: #e6e6ff;
    }

    .countdown {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-bottom: 40px;
    }

    .countdown-item {
      background: rgba(255, 255, 255, 0.15);
      padding: 20px;
      border-radius: 10px;
      min-width: 100px;
      backdrop-filter: blur(5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .countdown-value {
      font-size: 2.5rem;
      font-weight: 700;
      display: block;
    }

    .countdown-label {
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 2px;
    }

    .btn {
      display: inline-block;
      padding: 15px 40px;
      background: linear-gradient(to right, #6a11cb, #2575fc);
      color: white;
      text-decoration: none;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1.1rem;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(106, 17, 203, 0.4);
      position: relative;
      overflow: hidden;
    }

    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(106, 17, 203, 0.6);
    }

    .btn:active {
      transform: translateY(1px);
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: 0.5s;
    }

    .btn:hover::before {
      left: 100%;
    }

    .social-icons {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 40px;
    }

    .social-icons a {
      color: white;
      font-size: 1.5rem;
      transition: all 0.3s ease;
    }

    .social-icons a:hover {
      transform: translateY(-5px);
      color: #c2e9fb;
    }

    .footer {
      margin-top: 50px;
      font-size: 0.9rem;
      color: #ddd;
    }

    /* Animations */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes pulse {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.05);
      }

      100% {
        transform: scale(1);
      }
    }

    /* Responsive design */
    @media (max-width: 768px) {
      h1 {
        font-size: 2.5rem;
      }

      p {
        font-size: 1.1rem;
      }

      .countdown {
        flex-wrap: wrap;
      }

      .container {
        padding: 30px 20px;
      }
    }

    @media (max-width: 480px) {
      h1 {
        font-size: 2rem;
      }

      .countdown-item {
        min-width: 70px;
        padding: 15px;
      }

      .countdown-value {
        font-size: 2rem;
      }
    }
  </style>
</head>

<body>
  <div class="background-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="shape shape-4"></div>
  </div>

  <div class="container">
    <div class="logo">
      <i class="fas fa-rocket"></i>
    </div>

    <h1>Coming Soon</h1>

    <p>We're crafting something amazing for you!<br>Our team is working hard to bring you an exceptional experience.</p>

    <div class="countdown">
      <div class="countdown-item">
        <span class="countdown-value" id="days">00</span>
        <span class="countdown-label">Days</span>
      </div>
      <div class="countdown-item">
        <span class="countdown-value" id="hours">00</span>
        <span class="countdown-label">Hours</span>
      </div>
      <div class="countdown-item">
        <span class="countdown-value" id="minutes">00</span>
        <span class="countdown-label">Minutes</span>
      </div>
      <div class="countdown-item">
        <span class="countdown-value" id="seconds">00</span>
        <span class="countdown-label">Seconds</span>
      </div>
    </div>

    <a href="index.php" class="btn">
      <i class="fas fa-arrow-left me-2"></i> Back to Home
    </a>

    <div class="social-icons">
      <a href="#"><i class="fab fa-facebook-f"></i></a>
      <a href="#"><i class="fab fa-twitter"></i></a>
      <a href="#"><i class="fab fa-instagram"></i></a>
      <a href="#"><i class="fab fa-linkedin-in"></i></a>
    </div>
  </div>

  <div class="footer">
    <p>&copy; 2025 TRIPSORUS. All rights reserved.</p>
  </div>

  <script>
    // Set the date we're counting down to (2 weeks from now)
    const countDownDate = new Date();
    countDownDate.setDate(countDownDate.getDate() + 14);

    // Update the countdown every 1 second
    const countdownFunction = setInterval(function () {
      // Get today's date and time
      const now = new Date().getTime();

      // Find the distance between now and the count down date
      const distance = countDownDate - now;

      // Time calculations for days, hours, minutes and seconds
      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      // Display the result
      document.getElementById("days").textContent = days.toString().padStart(2, '0');
      document.getElementById("hours").textContent = hours.toString().padStart(2, '0');
      document.getElementById("minutes").textContent = minutes.toString().padStart(2, '0');
      document.getElementById("seconds").textContent = seconds.toString().padStart(2, '0');

      // If the count down is finished, write some text
      if (distance < 0) {
        clearInterval(countdownFunction);
        document.getElementById("days").textContent = "00";
        document.getElementById("hours").textContent = "00";
        document.getElementById("minutes").textContent = "00";
        document.getElementById("seconds").textContent = "00";
      }
    }, 1000);
  </script>
</body>

</html>