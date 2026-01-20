<?php
// backend/security/auth.php

/**
 * Verify if the token exists in the database and return the user ID.
 *
 * @param mysqli $mysqli
 * @param string $token
 * @return int|false User ID if valid, false otherwise.
 */
function verifyToken($mysqli, $token) {
    if (empty($token)) return false;

    $stmt = $mysqli->prepare("SELECT id FROM users WHERE token = ?");
    if (!$stmt) {
        // Log error if needed
        return false;
    }

    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        return (int)$user['id'];
    }

    return false;
}

/**
 * Check if the user has a specific permission.
 *
 * @param mysqli $mysqli
 * @param int $userId
 * @param string $permissionSlug
 * @return bool
 */
function hasPermission($mysqli, $userId, $permissionSlug) {
    if (!$userId) return false;

    // 1. Get user's role_id
    $stmt = $mysqli->prepare("SELECT role_id FROM users WHERE id = ?");
    if (!$stmt) return false;

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) return false;

    $user = $res->fetch_assoc();
    $roleId = $user['role_id'];

    if (!$roleId) return false; // User has no role

    // 2. Check for specific permission OR 'all_permissions'
    $stmt = $mysqli->prepare("
        SELECT 1 FROM role_permissions
        WHERE role_id = ?
        AND (permission_slug = ? OR permission_slug = 'all_permissions')
    ");

    if (!$stmt) return false;

    $stmt->bind_param("is", $roleId, $permissionSlug);
    $stmt->execute();

    return $stmt->get_result()->num_rows > 0;
}
?>
