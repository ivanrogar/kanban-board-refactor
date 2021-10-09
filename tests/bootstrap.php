<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv(false))->loadEnv(dirname(__DIR__) . '/.env');

$_SERVER += $_ENV;

$appEnv = $_ENV['APP_ENVIRONMENT'];
