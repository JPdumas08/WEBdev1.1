<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';

init_session();

function normalize_redirect(string $raw = ''): string {
    $raw = trim($raw);

    if ($raw === '') {
        // Prefer a referring page if it is not this login shim
        $ref = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_PATH);
        $refBase = $ref ? basename($ref) : '';
        if ($refBase && $refBase !== 'login.php') {
            $raw = $refBase;
        }
    }

    if ($raw === '') {
        return 'home.php';
    }

    // Disallow absolute URLs to avoid open redirects
    if (preg_match('#^https?://#i', $raw)) {
        return 'home.php';
    }

    // Add .php when a bare slug like "address" is provided
    if (!preg_match('/\.[a-z0-9]+($|\?)/i', $raw) && strpos($raw, '/') === false) {
        $raw .= '.php';
    }

    // If query params exist without ?, convert the first & to ?
    if (strpos($raw, '?') === false && strpos($raw, '&') !== false) {
        $raw = preg_replace('/&/', '?', $raw, 1);
    }

    return $raw;
}

$redirectTarget = normalize_redirect($_GET['redirect'] ?? ($_GET['redirect_to'] ?? ''));

// If already authenticated, go straight where they intended
if (!empty($_SESSION['user'])) {
    header('Location: ' . $redirectTarget);
    exit;
}

// Not logged in: bounce back to the intended page and tell it to open the modal
$redirectUrl = $redirectTarget;
$separator = (strpos($redirectUrl, '?') === false) ? '?' : '&';
$redirectUrl .= $separator . 'showLogin=1';

// Preserve helpful flags (e.g., from registration flow)
if (isset($_GET['registered'])) {
    $redirectUrl .= '&registered=1';
}
if (isset($_GET['login'])) {
    $redirectUrl .= '&login=' . rawurlencode($_GET['login']);
}

header('Location: ' . $redirectUrl);
exit;