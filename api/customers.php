<?php
// Simple REST API for customers.json
// Supports: GET (list or by id), POST (create), PUT (update), DELETE (remove)
// Path: /api/customers.php[?id=]

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
// Prevent caching to always serve latest data
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$JSON_PATH = __DIR__ . '/../data/customers.json';

function respond($code, $data) {
    http_response_code($code);
    if ($data !== null) echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function load_customers($path) {
    if (!file_exists($path)) {
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    $raw = @file_get_contents($path);
    $list = json_decode($raw, true);
    if (!is_array($list)) $list = [];
    return $list;
}

function save_customers($path, $list) {
    $ok = @file_put_contents($path, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    if ($ok === false) {
        $err = error_get_last();
        respond(500, [ 'error' => 'Failed to persist data', 'detail' => isset($err['message']) ? $err['message'] : null, 'path' => $path ]);
    }
}

function get_next_id($list) {
    $max = 0;
    foreach ($list as $row) {
        if (isset($row['id']) && is_numeric($row['id'])) $max = max($max, (int)$row['id']);
    }
    return $max + 1;
}

function parse_json_body() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if ($raw !== '' && json_last_error() !== JSON_ERROR_NONE) {
        respond(400, [ 'error' => 'Invalid JSON: ' . json_last_error_msg() ]);
    }
    return is_array($data) ? $data : [];
}

function norm($v) { return is_string($v) ? trim($v) : $v; }
function sanitize_phone($v) { return preg_replace('/\D+/', '', (string)$v); }

$method = $_SERVER['REQUEST_METHOD'];
$list = load_customers($JSON_PATH);

switch ($method) {
    case 'GET': {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            foreach ($list as $row) {
                if ((int)($row['id'] ?? 0) === $id) respond(200, $row);
            }
            respond(404, [ 'error' => 'Customer not found' ]);
        } else {
            respond(200, $list);
        }
    }
    case 'POST': {
        $body = parse_json_body();
        $firstName = norm($body['firstName'] ?? '');
        $lastName  = norm($body['lastName'] ?? '');
        $gender    = norm($body['gender'] ?? '');
        $address   = norm($body['address'] ?? '');
        $contactRaw = norm($body['contact'] ?? ($body['phone'] ?? ''));
        $contact   = sanitize_phone($contactRaw);
        $email     = norm($body['email'] ?? '');
        $dob       = norm($body['dob'] ?? '');
        $username  = norm($body['username'] ?? '');
        $password  = (string)($body['password'] ?? '');

        $errs = [];
        if ($firstName === '') $errs[] = 'firstName is required';
        if ($lastName === '') $errs[] = 'lastName is required';
        if ($gender === '') $errs[] = 'gender is required';
        if ($address === '') $errs[] = 'address is required';
        if ($contact === '' || !preg_match('/^\d{7,15}$/', $contact)) $errs[] = 'contact must be 7-15 digits';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = 'email is invalid';
        if ($dob === '') $errs[] = 'dob is required';
        if ($username === '' || strlen($username) < 6) $errs[] = 'username must be at least 6 chars';
        if ($password === '' || strlen($password) < 6 || !preg_match('/\d/', $password)) $errs[] = 'password must be at least 6 chars incl. a number';

        foreach ($list as $row) {
            if (strcasecmp($row['username'] ?? '', $username) === 0) $errs[] = 'username already exists';
            if (strcasecmp($row['email'] ?? '', $email) === 0) $errs[] = 'email already exists';
            if (!empty($errs)) break;
        }

        if (!empty($errs)) respond(422, [ 'errors' => $errs ]);

        $record = [
            'id' => get_next_id($list),
            'firstName' => $firstName,
            'lastName' => $lastName,
            'gender' => $gender,
            'address' => $address,
            'phone' => $contact,
            'email' => $email,
            'dob' => $dob,
            'username' => $username,
            'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
            'createdAt' => date('c'),
        ];
        $list[] = $record;
        save_customers($JSON_PATH, $list);
        respond(201, $record);
    }
    case 'PUT': {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) respond(400, [ 'error' => 'id is required' ]);
        $idx = null;
        foreach ($list as $i => $row) {
            if ((int)($row['id'] ?? 0) === $id) { $idx = $i; break; }
        }
        if ($idx === null) respond(404, [ 'error' => 'Customer not found' ]);

        $body = parse_json_body();
        // Prepare updates
        $updates = [];
        $map = [
            'firstName' => 'firstName',
            'lastName'  => 'lastName',
            'gender'    => 'gender',
            'address'   => 'address',
            'contact'   => 'phone',
            'phone'     => 'phone',
            'email'     => 'email',
            'dob'       => 'dob',
            'username'  => 'username',
        ];
        foreach ($map as $in => $out) {
            if (array_key_exists($in, $body)) {
                $updates[$out] = norm($body[$in]);
            }
        }
        if (array_key_exists('password', $body)) {
            $pwd = (string)$body['password'];
            if (strlen($pwd) < 6 || !preg_match('/\d/', $pwd)) {
                respond(422, [ 'errors' => ['password must be at least 6 chars incl. a number'] ]);
            }
            $updates['passwordHash'] = password_hash($pwd, PASSWORD_DEFAULT);
        }
        if (isset($updates['phone'])) {
            $updates['phone'] = sanitize_phone($updates['phone']);
            if ($updates['phone'] !== '' && !preg_match('/^\d{7,15}$/', $updates['phone'])) {
                respond(422, [ 'errors' => ['contact/phone must be 7-15 digits'] ]);
            }
        }
        if (isset($updates['email']) && !filter_var($updates['email'], FILTER_VALIDATE_EMAIL)) {
            respond(422, [ 'errors' => ['email is invalid'] ]);
        }
        // Uniqueness checks when changing username/email
        foreach ($list as $i => $row) {
            if ($i === $idx) continue;
            if (isset($updates['username']) && strcasecmp($row['username'] ?? '', $updates['username']) === 0) {
                respond(422, [ 'errors' => ['username already exists'] ]);
            }
            if (isset($updates['email']) && strcasecmp($row['email'] ?? '', $updates['email']) === 0) {
                respond(422, [ 'errors' => ['email already exists'] ]);
            }
        }

        $list[$idx] = array_merge($list[$idx], $updates);
        save_customers($JSON_PATH, $list);
        respond(200, $list[$idx]);
    }
    case 'DELETE': {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) respond(400, [ 'error' => 'id is required' ]);
        $found = false;
        foreach ($list as $i => $row) {
            if ((int)($row['id'] ?? 0) === $id) { unset($list[$i]); $found = true; break; }
        }
        if (!$found) respond(404, [ 'error' => 'Customer not found' ]);
        $list = array_values($list);
        save_customers($JSON_PATH, $list);
        respond(204, null);
    }
    default:
        respond(405, [ 'error' => 'Method not allowed' ]);
}
