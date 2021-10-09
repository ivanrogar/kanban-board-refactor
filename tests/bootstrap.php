<?php

use Symfony\Component\Dotenv\Dotenv;

(new Dotenv(false))->loadEnv(dirname(__DIR__) . '/.env');

$_SERVER += $_ENV;

$appEnv = $_ENV['APP_ENVIRONMENT'];
