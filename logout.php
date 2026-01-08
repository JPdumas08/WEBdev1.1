<?php
require_once __DIR__ . '/includes/auth.php';
init_session();
logout_user();

// Get redirect target - prefer referer but ensure it's valid
$redirect = $_GET['redirect_to'] ?? '';
if (empty($redirect)) {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (!empty($referer)) {
        $parsed = parse_url($referer);
        $path = $parsed['path'] ?? '';
        $basename = basename($path);
        
        // Protected pages that require login - redirect to home instead
        $protectedPages = ['account_settings.php', 'notifications.php', 'order_history.php', 
                          'address.php', 'checkout.php', 'order_confirmation.php', 'wishlist.php'];
        
        if (in_array($basename, $protectedPages)) {
            $redirect = 'home.php';
        } else {
            $redirect = $path;
            // Add query string if present
            if (!empty($parsed['query'])) {
                $redirect .= '?' . $parsed['query'];
            }
        }
    }
}

// Default to home if no valid redirect
if (empty($redirect) || $redirect === '/logout.php' || basename($redirect) === 'logout.php') {
    $redirect = 'home.php';
}

// Make sure it's a relative path (no scheme)
if (preg_match('#^https?://#i', $redirect)) {
    $redirect = 'home.php';
}

header('Location: ' . $redirect);
exit;
