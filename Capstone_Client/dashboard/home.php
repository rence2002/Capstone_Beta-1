<?php
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
  header("location: ../index.php");
  exit;
}

$userId = $_SESSION["user_id"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>
  <link rel="stylesheet" href="../static/css-files/Home.css">
  <!-- Google Icons Link -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
  <!-- Link Swiper's CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <!-- font awesome cdn link  -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
</head>
<body>

<header>
  <nav class="navbar">
    <a href="../dashboard/home.php" class="logo">
      <img src="../static/images/rm raw png.png" alt="" class="logo">
    </a>
    <ul class="menu-links">
      <li class="dropdown">
        <a href="../dashboard/home.php"  class="active">Home</a>
        <ul class="dropdown-menus">
          <li><a href="#about-section">About</a></li>
          <li><a href="#contact-section">Contacts</a></li>
          <li><a href="#offers-section">Offers</a></li>
        </ul>
      </li>
      <li><a href="../reviews/review.php">Reviews</a></li>
      <li><a href="../gallery/gallery.php">Gallery</a></li>
      <li><a href="../cart/cart.php" class="cart" id="cart">Cart</a></li>
      <li class="dropdown">
        <a href="../profile/profile.php" class="profile" id="sign_in">Profile</a>
        <ul class="dropdown-menus">
          <li><a href="../profile/profile.php">Profile</a></li>
          <li><a href="logout.php">Logout</a></li>
        </ul>

       
      </li>
      <span id="close-menu-btn" class="material-symbols-outlined">close</span>
    </ul>
   
    <span id="hamburger-btn" class="material-symbols-outlined">menu</span>
  </nav>
</header>

<main>
      <img src="../static/images/rmbg.jpg" alt="rm betis head pic" id="rmpic">
    </div>
   
      <div class="offer" id="offers-section">
        <div class="laman swiper mySwiper">
          <div class="wrapper swiper-wrapper">
            <div class="slide swiper-slide">
              <img src="../static/images/rm1.jpg" alt="" class="image" />
              <div class="image-data"></div>
            </div>
            <div class="slide swiper-slide">
              <img src="../static/images/pagawaan.jpg" alt="" class="image" />
              <div class="image-data">
              </div>
            </div>
            <div class="slide swiper-slide">
              <img src="../static/images/office.jpg" alt="" class="image" />
              <div class="image-data">
              </div>
            </div>
          </div>

            <div class="swiper-button-next nav-btn"></div>
            <div class="swiper-button-prev nav-btn"></div>
            <div class="swiper-pagination"></div>
    </div>
    <div class="transform_con">
      <h3 class="title" id="offers-section">OFFERS</h3>

      <p id="message">Transform Your Space with Our Exclusive Furniture Collection!
        Upgrade your home with our stylish, modern furniture designed for every room. Whether you're refreshing your living space,
         adding sophistication to your bedroom, or creating a cozy dining area, we have options to fit your needs.
        Choose from:
        <ul>
          <li>Pre-Order: Secure the latest designs early.</li>
          <li>Customized Furniture: Tailor pieces to your taste.</li>
          <li>Ready-Made: Shop our in-stock items for quick delivery.</li>
        </ul>
      </p>
        <button id="checkNow">Check Now!</button>
    </div>
  </div>

    <div class="about-container" id="about-section">
      <div class="about">
        <h3 class="title">ABOUT US</h3>
        <div>
          <h4 class="topic-title">The Legacy of RM Betis Furniture: A Journey from Adversity to Entrepreneurship</h4>
          <p class="details-of-titles">
            A RM Betis Furniture shop owned by Ralph Maninang. The owner's inspiration for the start up is stemming from the poverty Ralph Maninang's parents faced, which prevented the family's needs and desires from being met. The origins of this furniture business trace back to the owner's grandfather's initiative in the 1960s. Subsequently, Mr. Maninang has taken the reins, rebranding and managing the business according to the new founder vision. Under the owner's stewardship, the business, now operating for approximately two years under the name "RM Betis Furniture," has been advancing steadily.
          </p> 
        </div>
     
        <h4 class="topic-title">
          Revolutionizing Furniture Shopping: The Power of Augmented Reality
        </h4>
     <div class="ar-details">
      <p class="details-of-ar">
        This website offers an innovative online furniture ordering platform that incorporates cutting-edge augmented reality (AR) features, allowing users to visualize 3D models of furniture in their own space and measure their surroundings for accurate fit and size. The platform provides an immersive and interactive experience, enabling customers to make informed purchasing decisions by seeing how different pieces of furniture will look and fit in their homes. With features such as realistic scaling and measurement, user-friendly interface, interactive measurement tools, personalized recommendations, and a seamless ordering process, this website aims to revolutionize the furniture shopping experience. Additionally, a dedicated customer support team is available to assist users with any questions or concerns, ensuring a smooth and enjoyable shopping journey.
      </p>

      <div class="ar-demo">
        <model-viewer class="image-card three-d" 
        src="../static/3d/dining.glb" 
        shadow-intensity="1" 
        camera-controls 
        touch-action="pan-y" 
        auto-rotate 
        auto-rotate-delay="2000" 
        camera-orbit="90deg 45deg">
    </model-viewer>
  </div>
      </div>
      <div>
        <h4 class="topic-title">Customizable Solutions: Meeting Diverse Furniture Needs</h4>
        <p class="details-of-titles">
          RM Betis Furniture shop provides a diverse array of furniture tailored to meet various demands, encompassing home furnishings, restaurant fixtures, and condominium decor. The establishment offers customizable furniture options to accommodate specific customer preferences. Clients typically work directly with personal interior designers who relay inspirations, dimensions, colors, and other specifications to RM Betis Furniture's proprietor.
        </p>
      </div>
    </div>
</div>
<div class="contact-container" id="contact-section">
  <div class="info">
    <h3 class="title">CONTACT US</h3>
    <p>Store hours</p>
    <p>Our Shop is operating<br>Monday to Sunday :</p>
    <p class="hours">8 AM – 6 PM</p>
    <p>  <i class="fab fa-viber"></i> <!-- Viber icon -->Phone & Viber</p>
    <p class="phone">
      <span>+(63) 96596602006</span>
        <!-- <a href="viber://chat?number=6396596602006" class="viber-link">Chat with us on Viber</a> -->
    <p> <i class="fas fa-envelope"></i> <!-- Email icon --> Email</p>
  
    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=Rmbetisfurniture@yahoo.com&su=Your%20Subject%20Here&body=Your%20message%20here." class="email">Rmbetisfurniture@yahoo.com</a>
   
  </div>
  <div class="map">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3854.429758806135!2d120.64942327613596!3d14.96883028556239!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33965940933d78d1%3A0x5a2d751bc18e0b50!2sRM%20Betis%20Furniture!5e0!3m2!1sen!2sph!4v1730267643999!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
  </div>
</div>
   
</main>

<footer class="footer">
  <div class="footer-row">
    <div class="footer-col">
      <h4>Info</h4>
      <ul class="links">
        <li><a href="home.php">Home</a></li>
        <li><a href="#about-section">About Us</a></li>
        <li><a href="Gallery.php">Gallery</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Explore</h4>
      <ul class="links">
        <li><a href="#">Free Designs</a></li>
        <li><a href="#">Latest Designs</a></li>
        <li><a href="#">Themes</a></li>
        <li><a href="#">Popular Designs</a></li>
        <li><a href="#">Art Skills</a></li>
        <li><a href="#">New Uploads</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Legal</h4>
      <ul class="links">
        <li><a href="#">Customer Agreement</a></li>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">GDPR</a></li>
        <li><a href="#">Security</a></li>
        <li><a href="#">Testimonials</a></li>
        <li><a href="#">Media Kit</a></li>
      </ul>
    </div>


    <!-- <div class="footer-col">
      <h4>Newsletter</h4>
      <p>
        Subscribe to our newsletter for a weekly dose
        of news, updates, helpful tips, and
        exclusive offers.
      </p>
      <form action="#">
        <input type="text" placeholder="Your email" required>
        <button type="submit">SUBSCRIBE</button>
      </form> -->
      <div class="icons">
        <i class="fa-brands fa-facebook-f"></i>
        <i class="fa-brands fa-twitter"></i>
        <i class="fa-brands fa-linkedin"></i>
        <i class="fa-brands fa-github"></i>
      </div>
    </div>
  </div>
</footer>

<script src="../static/Javascript-files/script.js"></script>

 <!-- Swiper JS -->
 <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
 <script>
   var swiper = new Swiper(".mySwiper", {
     slidesPerView: 1,
     loop: true,
     pagination: {
       el: ".swiper-pagination",
       clickable: true,
     },
     navigation: {
       nextEl: ".swiper-button-next",
       prevEl: ".swiper-button-prev",
     },
   });
 </script>
 <script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/11.5.0/firebase-app.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.5.0/firebase-analytics.js";
  // TODO: Add SDKs for Firebase products that you want to use
  // https://firebase.google.com/docs/web/setup#available-libraries

  // Your web app's Firebase configuration
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  const firebaseConfig = {
    apiKey: "AIzaSyDqjDk0jpyHu2vT4RGZ-yZN31UDbK6cYw4",
    authDomain: "rm-betis-furniture.firebaseapp.com",
    projectId: "rm-betis-furniture",
    storageBucket: "rm-betis-furniture.firebasestorage.app",
    messagingSenderId: "509374756756",
    appId: "1:509374756756:web:b5daa7b886c122f4120181",
    measurementId: "G-18WDFEMF89"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const analytics = getAnalytics(app);
</script>
</body>
</html>
