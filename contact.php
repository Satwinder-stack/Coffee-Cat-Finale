<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Coffee Cat Café</title>
    <link rel="stylesheet" href="css/contact.css">
</head>
<body>
    <header>
      <div class="logo">
        <a href="index.html"><img src="images/logo.png" class="logo-img"></a>
        <a href="index.html"><img src="images/logo name.png" class="logo-name"></a>
        <button class="hamburger" aria-label="Open navigation" aria-controls="mobile-nav" aria-expanded="false">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
      <img src="images/cats/pawprint.png" class="header-paw-print" id="paw">
      <div class="social-links" id="mobile-nav">
              <a href="features.html" class="nav-link">Features</a>
              <a href="about.html" class="nav-link">About</a>
              <a href="contact.php" class="nav-link">Contact</a>
              <a href="location.html" class="nav-link">Location</a>
              <a href="menu.html" class="nav-link">Menu</a>
              <a href="cart.html" class="nav-link">Cart</a>
      </div>
  </header>
    <nav class="breadcrumb">
        <a href="index.html">Home</a> / <span>Contact</span>
    </nav>
    <main>
        <section class="contact-intro about-intro">
            <h1>Contact Coffee Cat Café</h1>
            <p>Contact us below. All fields are required.</p>
        </section>
        <section class="contact-content">
            <div class="contact-form-container">
                <form class="contact-form" method="POST" autocomplete="off" action="redirect.php">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" placeholder="Your Name" required>

                    <label for="contact">Contact Number</label>
                    <input type="text" id="contact" name="contact" placeholder="Your Contact Number" required>

                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@email.com" required>

                    <label for="message">Message to the company</label>
                    <textarea id="message" name="message" rows="5" required></textarea>

                    <label for="platform">Select Marketing Platform</label>
                    <div class="dropdown-container">
                    <select id="platform" name="platform" required>
                        <option value="">-- Choose Platform --</option>
                        <option value="email">Email Marketing</option>
                        <option value="social">Social Media Marketing</option>
                    </select>
                    </div>


                    <button type="submit" class="cta-btn">Submit</button>
                </form>

                <div style="display: flex; justify-content: center; margin-top: 1em;">
                    <img src="images/cats/pawprint.png" alt="Paw Print" style="height: 8.5em; opacity: 0.7; margin-top: 3em;">
                </div>
            </div>
            <div class="contact-info">
                <h2 style="margin-bottom: 1.2rem;">Contact Information</h2>
                <ul style="list-style: none; padding: 0; color: #6d5c4a; font-size: 1.08rem;">
                    <li><strong>Address:</strong> taguette ave paradise subdivision brgy, Angeles, Pampanga</li>
                    <li><strong>Phone:</strong> (+63)917 729 4004</li>
                    <li><strong>Email:</strong> <a href="mailto:info@coffeecatcafe.com" style="color: #a98c6d; text-decoration: underline;">info@coffeecatcafe.com</a></li>
                </ul>
                <div class="map-placeholder" style="margin-top: 2rem; background: #f8f4f0; border-radius: 16px; display: flex; flex-direction: column; align-items: center; color: #a98c6d; font-size: 1.2rem; box-shadow: 0 2px 12px rgba(80,60,40,0.08); min-height: 380px;">
                    <img src="images/socmed1.jpg" alt="Coffee Cat Café Social Media 1" style="max-width: 100%; height: 9.5em;; margin-bottom: 1em; border-radius: 8px; box-shadow: 0 1px 6px rgba(80,60,40,0.07);">
                    <img src="images/socmed2.jpg" alt="Coffee Cat Café Social Media 2" style="max-width: 100%; height: 9.5em; border-radius: 8px; box-shadow: 0 1px 6px rgba(80,60,40,0.07);">
                </div>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 Coffee Cat. All rights reserved. Created by Satwinder Jeerh of WD-303.</p>
    </footer>
    <script src="js/contact.js"></script>
</body>
</html>
