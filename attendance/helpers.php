<?php
//learning branches
// Branch practice
// Git practice change
// includes/helpers.php — Utility functions

function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES);
}

function redirect(string $url): never {
    header("Location: $url");
    exit;
}

function flash(string $msg, string $type = 'success'): void {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function badgeClass(string $status): string {
    return match($status) {
        'present' => 'badge-present',
        'absent'  => 'badge-absent',
        'late'    => 'badge-late',
        'excused' => 'badge-excused',
        default   => 'badge-none',
    };
}

// CSRF helpers
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Auth helper
function require_admin(): void {
    if (empty($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
        redirect('login.php');
    }
}
