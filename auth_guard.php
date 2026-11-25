<?php
// auth_guard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function ensure_csrf_token(): string {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function verify_csrf_token(?string $token): bool {
    return is_string($token)
        && isset($_SESSION["csrf_token"])
        && hash_equals($_SESSION["csrf_token"], $token);
}

function require_role(string $expected_role): void {
    if (!isset($_SESSION["user_id"], $_SESSION["role"])) {
        header("Location: login.php");
        exit;
    }

    if ($_SESSION["role"] !== $expected_role) {
        header("Location: " . ($_SESSION["role"] === "admin" ? "admin_dashboard.php" : "user_dashboard.php"));
        exit;
    }
}