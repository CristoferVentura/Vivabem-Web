<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/supabase.php';

if (is_logged_in()) {
    redirect('pages/dashboard.php');
}

$verifier = supabase_pkce_verifier();
$state = bin2hex(random_bytes(16));

$_SESSION['google_oauth_verifier'] = $verifier;
$_SESSION['google_oauth_state'] = $state;

$redirectTo = app_url('actions/google_callback.php') . '?' . http_build_query([
    'state' => $state,
], '', '&', PHP_QUERY_RFC3986);

header('Location: ' . supabase_google_authorize_url($redirectTo, supabase_pkce_challenge($verifier)));
exit;
