<?php
require_once __DIR__ . '/../vendor/autoload.php';

$stripe_secret_key = "sk_test_51T4cWhE26pjorH92F5fBx5RDW5z0fzjIrSUClcoCZW1mxeuWAyhLWZEQR08H2Tu1x2RCYCIl4VlyJ6fXQLnBLDEl008wsheRC2";

define('BASE_URL', 'https://kristovskis.lv/2023/cecotkins/liepajas_taksometrs/');

\Stripe\Stripe::setApiKey($stripe_secret_key);