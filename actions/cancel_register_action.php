<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/mailer.php';

clear_pending_password_reset();
redirect('forgot_password.php');
