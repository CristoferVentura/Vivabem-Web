<?php
/**
 * Conexão PDO segura — VivaBem
 * Ajuste host/dbname/user/pass conforme seu ambiente (XAMPP padrão abaixo).
 */

declare(strict_types=1);

$dbHost = '127.0.0.1';
$dbName = 'vivabem';
$dbUser = 'root';
$dbPass = '';
$dbCharset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    error_log('VivaBem DB: ' . $e->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados. Verifique a configuração e se o MySQL está ativo.');
}
