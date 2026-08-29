<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ROOT_PATH', dirname(__DIR__));

/**
 * URL base do projeto (ex.: /Vivabem-Web/index.php).
 */
function base_url(string $path = ''): string
{
    static $base = null;

    if ($base === null) {
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
        $root    = str_replace('\\', '/', ROOT_PATH);

        if ($docRoot !== '' && str_starts_with($root, $docRoot)) {
            $base = substr($root, strlen($docRoot));
        } else {
            $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
            if (str_ends_with($script, '/pages') || str_ends_with($script, '/actions')) {
                $script = dirname($script);
            }
            $base = ($script === '/' || $script === '\\' || $script === '.') ? '' : $script;
        }

        $base = rtrim(str_replace('\\', '/', (string) $base), '/');
    }

    $path = ltrim($path, '/');

    if ($base === '') {
        return '/' . $path;
    }

    return $base . '/' . $path;
}

function asset(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function current_user_role(): string
{
    return (string) ($_SESSION['user_role'] ?? '');
}

function is_company(): bool
{
    return current_user_role() === 'company';
}

function current_user_name(): string
{
    return (string) ($_SESSION['user_name'] ?? '');
}

function only_digits(string $value): string
{
    return preg_replace('/\D+/', '', $value) ?? '';
}

function format_cpf(string $value): string
{
    $digits = only_digits($value);
    if (strlen($digits) !== 11) {
        return $value;
    }

    return substr($digits, 0, 3) . '.' . substr($digits, 3, 3) . '.' . substr($digits, 6, 3) . '-' . substr($digits, 9, 2);
}

function format_cnpj(string $value): string
{
    $digits = only_digits($value);
    if (strlen($digits) !== 14) {
        return $value;
    }

    return substr($digits, 0, 2) . '.' . substr($digits, 2, 3) . '.' . substr($digits, 5, 3) . '/' . substr($digits, 8, 4) . '-' . substr($digits, 12, 2);
}

function current_user_first_name(): string
{
    $full = trim(current_user_name());
    if ($full === '') {
        return '';
    }

    $parts = preg_split('/\s+/u', $full) ?: [];
    return (string) ($parts[0] ?? $full);
}
