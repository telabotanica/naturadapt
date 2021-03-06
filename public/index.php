<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();

if ($_SERVER[ 'TRUST_ALL' ] ?? $_ENV[ 'TRUST_ALL' ] ?? FALSE) {
	Request::setTrustedProxies( [ '127.0.0.1', $request->server->get( 'REMOTE_ADDR' ) ], Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST );
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
