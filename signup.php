<?php
$message = '';
$errors = [];
$fieldErrors = [];
$displayData = '';
$sticky = [];
$fields = [
    'firstName' => 'First Name',
    'lastName' => 'Last Name',
    'gender' => 'Gender',
    'address' => 'Address',
    'contact' => 'Contact Number',
    'email' => 'Email Address',
    'dob' => 'Date of Birth',
    'username' => 'Username',
    'password' => 'Password'
];
$defaultInfoHtml = '<h3>Submitted Information</h3><ul>';
foreach ($fields as $label) {
    $defaultInfoHtml .= '<li><strong>' . htmlspecialchars($label) . ':</strong> </li>';
}
$defaultInfoHtml .= '</ul>';
$submittedInfoHtml = $defaultInfoHtml;

function build_submitted_info_html($fields, $data) {
    $html = '<h3>Submitted Information</h3><ul>';
    foreach ($fields as $field => $label) {
        $val = $data[$field] ?? '';
        if ($field === 'password') {
            $disp = str_repeat('•', max(0, strlen($val)));
        } else {
            $disp = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        }
        $html .= '<li><strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ':</strong> ' . $disp . '</li>';
    }
    $html .= '</ul>';
    return $html;
}

function api_post_customer($data) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base = rtrim(dirname($_SERVER['REQUEST_URI']), '/\\');
    $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $base . '/api/customers.php';
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ignore_errors' => true,
            'timeout' => 10,
        ]
    ]);
    $body = @file_get_contents($url, false, $context);
    $code = 0;
    if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) {
        $code = (int)$m[1];
    }
    $json = json_decode($body, true);
    return [$code, $json, $body];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    foreach ($fields as $field => $label) {
        $data[$field] = isset($_POST[$field]) ? trim((string)$_POST[$field]) : '';
    }

    $apiBody = [
        'firstName' => $data['firstName'] ?? '',
        'lastName'  => $data['lastName'] ?? '',
        'gender'    => $data['gender'] ?? '',
        'address'   => $data['address'] ?? '',
        'contact'   => $data['contact'] ?? '',
        'email'     => $data['email'] ?? '',
        'dob'       => $data['dob'] ?? '',
        'username'  => $data['username'] ?? '',
        'password'  => $data['password'] ?? '',
    ];

    [$code, $resp, $rawBody] = api_post_customer($apiBody);

    if ($code === 201) {
        $_SESSION['flash_message'] = 'Signup successful. You can now <a href="login.php">log in</a>.';
        $_SESSION['submitted_info_html'] = build_submitted_info_html($fields, $data);
        header('Location: signup.php');
        exit;
    }

    $errorList = [];
    if (is_array($resp) && isset($resp['errors']) && is_array($resp['errors'])) {
        $errorList = $resp['errors'];
    } elseif (is_array($resp) && isset($resp['error'])) {
        $errorList = [ (string)$resp['error'] ];
    } else {
        $errorList = [ 'Signup failed with status ' . $code ];
    }

    $errorFields = [];
    foreach ($errorList as $e) {
        $msg = strtolower((string)$e);
        if (strpos($msg, 'firstname') !== false) $errorFields['firstName'] = true;
        if (strpos($msg, 'lastname') !== false) $errorFields['lastName'] = true;
        if (strpos($msg, 'gender') !== false) $errorFields['gender'] = true;
        if (strpos($msg, 'address') !== false) $errorFields['address'] = true;
        if (strpos($msg, 'contact') !== false || strpos($msg, 'phone') !== false) $errorFields['contact'] = true;
        if (strpos($msg, 'email') !== false) $errorFields['email'] = true;
        if (strpos($msg, 'dob') !== false) $errorFields['dob'] = true;
        if (strpos($msg, 'username') !== false) $errorFields['username'] = true;
        if (strpos($msg, 'password') !== false) $errorFields['password'] = true;
    }

    $message = '<ul style="margin:0;padding-left:1.2em;color:#b94a48">' . implode('', array_map(function($e){ return '<li>'.htmlspecialchars((string)$e).'</li>'; }, $errorList)) . '</ul>';
    $sticky = $data;
    foreach (array_keys($errorFields) as $f) {
        $sticky[$f] = '';
    }
    $sticky['password'] = '';

    $_SESSION['flash_message'] = $message;
    $_SESSION['sticky'] = $sticky;
    header('Location: signup.php');
    exit;
}

$message = isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : '';
$sticky = isset($_SESSION['sticky']) ? $_SESSION['sticky'] : [];
$submittedInfoHtml = isset($_SESSION['submitted_info_html']) ? $_SESSION['submitted_info_html'] : $defaultInfoHtml;
unset($_SESSION['flash_message'], $_SESSION['sticky'], $_SESSION['submitted_info_html']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up | Coffee Cat Café</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
    <header>
        <div class="logo">
            <a href="landing.html"><img src="images/logo.png" class="logo-img"></a>
            <a href="landing.html"><img src="images/logo name.png" class="logo-name"></a>
        </div>
        <img src="images/cats/pawprint.png" class="header-paw-print" id="paw" >
        <div class="social-links">
            <a href="login.php" class="nav-link">Login</a>
        </div>
    </header>
    <main>
        <section class="signup-section">
            <div class="container">
                <div class="form-box">
                    <form id="signupForm" method="POST" autocomplete="off" novalidate>
                        <h2>Customer Signup</h2>
                        <?php if (!empty($message)): ?>
                        <div class="server-message" style="background:#fff6ef;border:1px solid #f0d7c2;color:#7b624c;border-radius:10px;padding:0.7em 1em;margin:0.6em 0;">
                            <?= $message ?>
                        </div>
                        <?php endif; ?>    
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" required value="<?= htmlspecialchars($sticky['firstName'] ?? '') ?>">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" required value="<?= htmlspecialchars($sticky['lastName'] ?? '') ?>">
                        <label>Gender</label>
                        <div class="gender-group">
                            <label><input type="radio" name="gender" value="Male" required <?php if (($sticky['gender'] ?? '') === 'Male') echo 'checked'; ?>> Male</label>
                            <label><input type="radio" name="gender" value="Female" <?php if (($sticky['gender'] ?? '') === 'Female') echo 'checked'; ?>> Female</label>
                            <label><input type="radio" name="gender" value="Other" <?php if (($sticky['gender'] ?? '') === 'Other') echo 'checked'; ?>> Other</label>
                        </div>
                        <label for="address">Address</label>
                        <textarea id="address" name="address" required><?= htmlspecialchars($sticky['address'] ?? '') ?></textarea>
                        <label for="contact">Contact Number</label>
                        <input type="text" id="contact" name="contact" required pattern="\d+" minlength="7" maxlength="15" inputmode="numeric" value="<?= htmlspecialchars($sticky['contact'] ?? '') ?>">
                        <div class="error-message" id="contactError" style="color:#b94a48; font-size:0.97em; margin-bottom:0.7em; display:none;"></div>
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($sticky['email'] ?? '') ?>">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" required value="<?= htmlspecialchars($sticky['dob'] ?? '') ?>">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required minlength="6" value="<?= htmlspecialchars($sticky['username'] ?? '') ?>">
                        <div class="error-message" id="usernameError" style="color:#b94a48; font-size:0.97em; margin-bottom:0.7em; display:none;"></div>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="6">
                        <div class="error-message" id="passwordError" style="color:#b94a48; font-size:0.97em; margin-bottom:0.7em; display:none;"></div>
                        <div class="button-group">
                            <button type="submit" class="cta-btn">Submit</button>
                            <button type="reset" class="cta-btn secondary" id="resetBtn">Reset</button>
                        </div>
                    </form>
                </div>
                <div class="display-info" id="displayInfo">
                    <?= $submittedInfoHtml ?>
                </div>
            </div>
        </section>
    </main>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('signupForm');
        const display = document.getElementById('displayInfo');
        const contact = document.getElementById('contact');
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        const contactError = document.getElementById('contactError');
        const usernameError = document.getElementById('usernameError');
        const passwordError = document.getElementById('passwordError');
        form.addEventListener('submit', function(e) {
            let valid = true;
            if (!/^\d{7,15}$/.test(contact.value)) {
                contactError.textContent = 'Contact number must be numerical';
                contactError.style.display = 'block';
                valid = false;
            } else {
                contactError.style.display = 'none';
            }
            if (username.value.length < 6) {
                usernameError.textContent = 'Username must be at least 6 characters.';
                usernameError.style.display = 'block';
                valid = false;
            } else {
                usernameError.style.display = 'none';
            }
            if (password.value.length < 6 || !/\d/.test(password.value)) {
                passwordError.textContent = 'Password must be at least 6 characters and contain at least 1 number.';
                passwordError.style.display = 'block';
                valid = false;
            } else {
                passwordError.style.display = 'none';
            }
            if (!valid) {
                e.preventDefault();
            } else {
            }
        });
        document.getElementById('resetBtn').addEventListener('click', function(e) {
            let info = '<h3>Submitted Information</h3><ul>';
            <?php foreach ($fields as $label): ?>
                info += '<li><strong><?= htmlspecialchars($label) ?>:</strong> </li>';
            <?php endforeach; ?>
            info += '</ul>';
            display.innerHTML = info;
            contactError.style.display = 'none';
            usernameError.style.display = 'none';
            passwordError.style.display = 'none';
        });
    });
    </script>
</body>
</html>