<?php

use KanbanBoard\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv(false))->loadEnv(dirname(__DIR__) . '/.env');

$_SERVER += $_ENV;

$appEnv = $_ENV['APP_ENVIRONMENT'];

$kernel = new Application($appEnv, $appEnv !== 'prod');

$request = Request::createFromGlobals();

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
