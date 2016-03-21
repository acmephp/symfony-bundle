<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

require_once __DIR__.'/bootstrap.php';
require_once __DIR__.'/AppKernel.php';

$kernel = new AppKernel('test', false);
$kernel->loadClassCache();

$request = Request::createFromGlobals();
$response = $kernel->handle($request, HttpKernelInterface::MASTER_REQUEST, false);

$response->send();
$kernel->terminate($request, $response);
