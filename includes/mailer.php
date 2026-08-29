<?php

declare(strict_types=1);

/**
 * Envia e-mail com o código de redefinição de senha.
 * Retorna true se o envio (ou log local) foi concluído.
 */
function send_verification_email(string $toEmail, string $toName, string $token): bool
{
    $config = require __DIR__ . '/../config/mail.php';

    $subject = 'Redefinição de senha — VivaBem';
    $bodyText = "Olá, {$toName}!\n\n"
        . "Seu código para redefinir a senha do VivaBem é: {$token}\n\n"
        . "Ele expira em 15 minutos. Se você não solicitou esta redefinição, ignore este e-mail.\n\n"
        . "— Equipe VivaBem";

    $bodyHtml = '<div style="font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:24px;color:#1a1a1a;">'
        . '<h2 style="color:#4169E1;margin:0 0 12px;">VivaBem</h2>'
        . '<p>Olá, <strong>' . htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') . '</strong>!</p>'
        . '<p>Use o código abaixo para redefinir sua senha:</p>'
        . '<p style="font-size:32px;letter-spacing:8px;font-weight:700;background:#eef3fb;padding:16px 20px;border-radius:12px;text-align:center;color:#4169E1;">'
        . htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        . '</p>'
        . '<p style="color:#5a6270;font-size:14px;">O código expira em 15 minutos.</p>'
        . '<p style="color:#5a6270;font-size:13px;">Se você não solicitou a redefinição de senha, ignore este e-mail.</p>'
        . '</div>';

    $driver = (string) ($config['driver'] ?? 'log');
    $hasSmtp = trim((string) ($config['smtp_user'] ?? '')) !== ''
        && trim((string) ($config['smtp_pass'] ?? '')) !== '';

    if ($driver === 'smtp' && !$hasSmtp) {
        $driver = 'log';
    }

    return match ($driver) {
        'smtp' => send_mail_smtp($toEmail, $toName, $subject, $bodyText, $bodyHtml, $config),
        'mail' => send_mail_native($toEmail, $toName, $subject, $bodyText, $bodyHtml, $config),
        default => send_mail_log($toEmail, $token),
    };
}

function send_mail_log(string $toEmail, string $token): bool
{
    $dir = ROOT_PATH . '/storage';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $line = sprintf(
        "[%s] Para: %s | Token: %s%s",
        date('Y-m-d H:i:s'),
        $toEmail,
        $token,
        PHP_EOL
    );

    return file_put_contents($dir . '/mail.log', $line, FILE_APPEND) !== false;
}

function send_mail_native(
    string $toEmail,
    string $toName,
    string $subject,
    string $bodyText,
    string $bodyHtml,
    array $config
): bool {
    $fromEmail = (string) $config['from_email'];
    $fromName  = (string) $config['from_name'];
    $boundary  = 'vb_' . bin2hex(random_bytes(8));

    $headers = [
        'MIME-Version: 1.0',
        'From: ' . sprintf('%s <%s>', mb_encode_mimeheader($fromName, 'UTF-8'), $fromEmail),
        'Reply-To: ' . $fromEmail,
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];

    $body = "--{$boundary}\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n\r\n{$bodyText}\r\n"
        . "--{$boundary}\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n\r\n{$bodyHtml}\r\n"
        . "--{$boundary}--";

    $ok = @mail($toEmail, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));

    if (!$ok) {
        send_mail_log($toEmail, 'FALHA_MAIL_NATIVO');
    }

    return $ok;
}

function send_mail_smtp(
    string $toEmail,
    string $toName,
    string $subject,
    string $bodyText,
    string $bodyHtml,
    array $config
): bool {
    $host = (string) $config['host'];
    $port = (int) $config['port'];
    $user = (string) $config['smtp_user'];
    $pass = (string) $config['smtp_pass'];
    $enc  = strtolower((string) ($config['encryption'] ?? 'tls'));
    $fromEmail = (string) $config['from_email'];
    $fromName  = (string) $config['from_name'];

    if ($fromEmail === '' || $fromEmail === 'noreply@vivabem.local') {
        $fromEmail = $user;
    }

    try {
        $remote = ($enc === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $socket = @stream_socket_client($remote, $errno, $errstr, 20, STREAM_CLIENT_CONNECT);

        if (!$socket) {
            throw new RuntimeException("SMTP connect: {$errstr} ({$errno})");
        }

        stream_set_timeout($socket, 20);
        smtp_expect($socket, [220]);

        smtp_command($socket, 'EHLO vivabem.local', [250]);

        if ($enc === 'tls') {
            smtp_command($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Falha ao iniciar TLS.');
            }
            smtp_command($socket, 'EHLO vivabem.local', [250]);
        }

        smtp_command($socket, 'AUTH LOGIN', [334]);
        smtp_command($socket, base64_encode($user), [334]);
        smtp_command($socket, base64_encode($pass), [235]);

        smtp_command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
        smtp_command($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
        smtp_command($socket, 'DATA', [354]);

        $boundary = 'vb_' . bin2hex(random_bytes(8));
        $headers = [
            'Date: ' . date('r'),
            'From: ' . sprintf('%s <%s>', mb_encode_mimeheader($fromName, 'UTF-8'), $fromEmail),
            'To: ' . sprintf('%s <%s>', mb_encode_mimeheader($toName, 'UTF-8'), $toEmail),
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        $data = implode("\r\n", $headers) . "\r\n\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n\r\n{$bodyText}\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n\r\n{$bodyHtml}\r\n"
            . "--{$boundary}--\r\n.";

        fwrite($socket, $data . "\r\n");
        smtp_expect($socket, [250]);
        smtp_command($socket, 'QUIT', [221]);
        fclose($socket);

        return true;
    } catch (Throwable $e) {
        error_log('VivaBem mail SMTP: ' . $e->getMessage());
        send_mail_log($toEmail, 'FALHA_SMTP');
        return false;
    }
}

/** @param resource $socket */
function smtp_command($socket, string $command, array $okCodes): void
{
    fwrite($socket, $command . "\r\n");
    smtp_expect($socket, $okCodes);
}

/** @param resource $socket */
function smtp_expect($socket, array $okCodes): void
{
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }

    $code = (int) substr($response, 0, 3);
    if (!in_array($code, $okCodes, true)) {
        throw new RuntimeException('SMTP erro: ' . trim($response));
    }
}

/**
 * Gera token numérico de 6 dígitos para redefinição de senha.
 */
function create_password_reset_token(int $userId, string $name, string $email): string
{
    $config = require __DIR__ . '/../config/mail.php';
    $ttl = (int) ($config['token_ttl'] ?? 900);
    $token = (string) random_int(100000, 999999);

    $_SESSION['pending_password_reset'] = [
        'user_id'    => $userId,
        'name'       => $name,
        'email'      => $email,
        'token_hash' => hash('sha256', $token),
        'expires_at' => time() + $ttl,
        'attempts'   => 0,
        'created_at' => time(),
        'verified'   => false,
    ];

    return $token;
}

function pending_password_reset(): ?array
{
    return $_SESSION['pending_password_reset'] ?? null;
}

function clear_pending_password_reset(): void
{
    unset($_SESSION['pending_password_reset'], $_SESSION['pending_token_preview']);
}

function mask_email(string $email): string
{
    [$user, $domain] = array_pad(explode('@', $email, 2), 2, '');
    if ($user === '' || $domain === '') {
        return $email;
    }

    $visible = mb_substr($user, 0, 2);
    return $visible . str_repeat('*', max(mb_strlen($user) - 2, 1)) . '@' . $domain;
}
