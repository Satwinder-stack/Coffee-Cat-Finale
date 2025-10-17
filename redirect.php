<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
    $contact = isset($_POST['contact']) ? trim((string)$_POST['contact']) : '';
    $email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
    $message = isset($_POST['message']) ? trim((string)$_POST['message']) : '';
    $platform = isset($_POST['platform']) ? (string)$_POST['platform'] : '';

    $path = __DIR__ . '/data/messages.json';
    $list = [];
    if (is_file($path)) {
        $raw = @file_get_contents($path);
        if ($raw !== false) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $list = $decoded;
            }
        }
    }

    $maxId = 0;
    foreach ($list as $row) {
        if (is_array($row) && isset($row['id']) && is_numeric($row['id'])) {
            $maxId = max($maxId, (int)$row['id']);
        }
    }

    $record = [
        'id' => $maxId + 1,
        'name' => $name,
        'contact' => $contact,
        'email' => $email,
        'message' => $message,
        'platform' => $platform,
        'date' => date('Y-m-d'),
    ];

    $list[] = $record;
    @file_put_contents($path, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

    if ($platform === 'email') {
        header('Location: email_marketing_mockup.html');
    } elseif ($platform === 'social') {
        header('Location: social_marketing_mockup.html');
    } else {
        header('Location: contact.php');
    }
    exit;
}

header('Location: contact.php');
exit;
