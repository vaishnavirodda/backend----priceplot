<?php
// helpers/auth.php — shared token validation helper
require_once __DIR__ . '/../config/database.php';

/**
 * Validate X-Auth-Token header and return the user row, or die with 401.
 */
function requireAuth(): array {
    $token = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? '';
    
    // Fallback: search in all headers (case-insensitive)
    if (empty($token) && function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'x-auth-token') {
                $token = $value;
                break;
            }
        }
    }

    if (empty($token)) {
        error_log("[auth] No token found in any header.");
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required']);
        exit;
    }

    $db   = Database::getConnection();
    $stmt = $db->prepare('SELECT * FROM users WHERE auth_token = ? AND is_active = 1');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        error_log("[auth] Invalid or expired token");
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid or expired token']);
        exit;
    }
    return $user;
}
