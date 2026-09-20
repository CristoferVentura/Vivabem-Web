<?php
/**
 * Compatibilidade de configuração — VivaBem
 *
 * A aplicação deixou de usar MySQL/PDO local e agora acessa o banco PostgreSQL
 * do Supabase pela API REST/PostgREST.
 */

declare(strict_types=1);

require_once __DIR__ . '/supabase.php';
