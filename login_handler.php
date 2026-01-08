<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db.php';

init_session();

// Helper to detect AJAX / JSON requests
function wants_json() {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xhr = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    return $xhr || stripos($accept, 'application/json') !== false;
}

function normalize_redirect(string $raw = ''): string {
    $raw = trim($raw);

    if ($raw === '') {
        return 'home.php';
    }

    // Prevent open redirects
    if (preg_match('#^https?://#i', $raw)) {
        return 'home.php';
    }

    if (!preg_match('/\.[a-z0-9]+($|\?)/i', $raw) && strpos($raw, '/') === false) {
        $raw .= '.php';
    }

    if (strpos($raw, '?') === false && strpos($raw, '&') !== false) {
        $raw = preg_replace('/&/', '?', $raw, 1);
    }

    return $raw;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

$emailOrUser = trim($_POST['email'] ?? $_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($emailOrUser === '' || $password === '') {
    // simple back redirect with error
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/') . '?login=missing');
    exit;
}

try {
    // try to find a usable identifier column
    $cols = ['email','email_address','username','user_name'];
    $foundCol = null;
    foreach ($cols as $c) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'users' AND COLUMN_NAME = :col");
        $stmt->execute([':db' => $pdo->query('select database()')->fetchColumn(), ':col' => $c]);
        if ($stmt->fetchColumn() > 0) { $foundCol = $c; break; }
    }
    if (!$foundCol) {
        // fall back to a guess
        $foundCol = 'email';
    }

    $sql = "SELECT * FROM users WHERE $foundCol = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $emailOrUser]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        if (wants_json()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'notfound']);
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/') . '?login=notfound');
        exit;
    }

    $stored = $user['password'] ?? $user['pass'] ?? $user['passwd'] ?? '';
    $verified = false;
    if ($stored && password_verify($password, $stored)) {
        $verified = true;
    } else {
        // In case passwords are stored plaintext (legacy), compare directly
        if (hash_equals((string)$stored, (string)$password)) {
            $verified = true;
        }
    }

    if (!$verified) {
        if (wants_json()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'bad_credentials']);
            exit;
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/') . '?login=bad');
        exit;
    }

    // success
    login_user($user);

    // Ensure a simple user id is present in session for other APIs
    init_session(); // make sure session is active after login_user
    $normalizedId = $user['user_id'] ?? $user['id'] ?? $user['userId'] ?? $user['userID'] ?? null;
    if ($normalizedId !== null) {
        // store a simple integer id at $_SESSION['user_id'] for compatibility
        $_SESSION['user_id'] = (int)$normalizedId;
        // also ensure the nested user id stays consistent
        if (!empty($_SESSION['user'])) {
            $_SESSION['user']['id'] = (int)$normalizedId;
        }
    }
    
    // Ensure session is written to storage before redirecting
    session_write_close();

    if (wants_json()) {
        header('Content-Type: application/json');
        $display = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['username'] ?? $user['email'] ?? '');
        echo json_encode(['success' => true, 'user' => ['display' => $display]]);
        exit;
    }

    // redirect back to the intended page and show a one-time success flag
    $redirect = normalize_redirect($_POST['redirect_to'] ?? 'home.php');
    $separator = (strpos($redirect, '?') === false) ? '?' : '&';
    header('Location: ' . $redirect . $separator . 'login=ok');
    exit;

} catch (Exception $e) {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/') . '?login=err');
    exit;
}
