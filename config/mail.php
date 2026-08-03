<?php
/**
 * Configuração de e-mail — VivaBem
 *
 * Para envio real (Gmail, Outlook etc.), preencha SMTP abaixo.
 * Com smtp_user vazio, o sistema usa o modo "log" (útil no XAMPP)
 * e exibe o código na tela de verificação para testes locais.
 */

declare(strict_types=1);

return [
    // 'smtp' | 'mail' | 'log'
    'driver' => 'smtp',

    'host'       => 'smtp.gmail.com',
    'port'       => 587,
    'encryption' => 'tls', // tls (587) ou ssl (465)
    'smtp_user'  => 'hashtechsuport@gmail.com', // ex.: seuemail@gmail.com
    'smtp_pass'  => 'fzbf ofvh hrek knak', // senha de app (Gmail)

    'from_email' => 'noreply@hashtech.com.br',
    'from_name'  => 'HashTech',

    // Se true e o envio falhar (ou driver=log), mostra o código na tela de verificação
    'show_code_fallback' => true,

    // Validade do token em segundos (15 minutos)
    'token_ttl' => 900,
];
