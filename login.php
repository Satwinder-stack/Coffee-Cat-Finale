<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$message = '';
if (isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
    $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
    $jsonPath = __DIR__ . '/data/customers.json';
    $users = [];
    if (file_exists($jsonPath)) {
        $raw = file_get_contents($jsonPath);
        $users = json_decode($raw, true);
        if (!is_array($users)) $users = [];
    }
    $found = null;
    foreach ($users as $u) {
        if (isset($u['username']) && strcasecmp($u['username'], $username) === 0) {
            $found = $u;
            break;
        }
    }
    if ($found && isset($found['passwordHash']) && password_verify($password, $found['passwordHash'])) {
        $_SESSION['user'] = [
            'id' => $found['id'] ?? null,
            'username' => $found['username'] ?? '',
            'name' => trim(($found['firstName'] ?? '') . ' ' . ($found['lastName'] ?? '')),
            'email' => $found['email'] ?? ''
        ];
        header('Location: index.html');
        exit;
    } else {
        $message = '<span style="color:#b94a48">Invalid username or password.</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | Coffee Cat Café</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #f8ede3, #fce7d3);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      overflow-x: hidden;
    }

    header {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px 0;
      position: relative;
      background: #fffaf5;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    header .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo-img {
      height: 60px;
    }

    .logo-name {
      height: 40px;
    }

    .header-paw-print {
      position: absolute;
      right: 40px;
      width: 50px;
      opacity: 0.2;
      transform: rotate(-20deg);
    }

    main {
      flex-grow: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
    }

    .login-section {
      width: 100%;
      max-width: 420px;
      background: #ffffff;
      border-radius: 20px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
      padding: 2.5rem;
      text-align: center;
      position: relative;
    }

    .login-section h2 {
      font-family: "Libre Baskerville", serif;
      color: #6a432d;
      font-size: 1.8rem;
      margin-bottom: 1.2rem;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    label {
      text-align: left;
      color: #6a432d;
      font-weight: 500;
    }

    input {
      padding: 12px 15px;
      border: 1px solid #e0cfc2;
      border-radius: 10px;
      background: #fffdfb;
      font-size: 1rem;
      outline: none;
      transition: all 0.2s ease;
    }

    input:focus {
      border-color: #b68963;
      box-shadow: 0 0 0 3px rgba(182, 137, 99, 0.2);
    }

    .server-message {
      background: #fff6ef;
      border: 1px solid #f0d7c2;
      color: #7b624c;
      border-radius: 10px;
      padding: 0.7em 1em;
      margin-bottom: 1em;
      font-size: 0.9rem;
    }

    .button-group {
      display: flex;
      flex-direction: column;
      gap: 0.8rem;
      margin-top: 1rem;
    }

    .cta-btn {
      background: #b68963;
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 12px;
      font-size: 1rem;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.25s ease;
    }

    .cta-btn:hover {
      background: #946b4e;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(150, 100, 60, 0.3);
    }

    .cta-btn.secondary {
      background: #f3e7db;
      color: #6a432d;
      border: 1px solid #d9c3aa;
    }

    .cta-btn.secondary:hover {
      background: #e9d4bb;
    }

    @media (max-width: 480px) {
      .login-section {
        padding: 2rem 1.5rem;
      }
    }
  </style>
</head>
<body>
<header>
  <div class="logo">
    <a href="index.html"><img src="images/logo.png" class="logo-img" alt="Coffee Cat Café logo"></a>
    <a href="index.html"><img src="images/logo name.png" class="logo-name" alt="Coffee Cat Café name"></a>
  </div>
  <img src="images/cats/pawprint.png" class="header-paw-print" alt="Pawprint">
</header>

<main>
  <section class="login-section">
    <form method="POST" autocomplete="off">
      <h2>Customer Login</h2>

      <?php if (!empty($message)): ?>
      <div class="server-message"><?= $message ?></div>
      <?php endif; ?>

      <label for="username">Username</label>
      <input type="text" name="username" id="username" required>

      <label for="password">Password</label>
      <input type="password" name="password" id="password" required>

      <div class="button-group">
        <button type="submit" class="cta-btn">Log In</button>
        <a href="signup.php" class="cta-btn secondary">Create Account</a>
      </div>
    </form>
  </section>
</main>
</body>
</html>
