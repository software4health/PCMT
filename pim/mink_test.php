<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
/**
 * @todo - test Selenium2 driver connection - do not remove, will be removed in MR-285
 */
require __DIR__ .'/vendor/autoload.php';

use Behat\Mink\Session;
use Behat\Mink\Driver\GoutteDriver;

$driver = new GoutteDriver();
$session = new Session($driver);

$session->visit('http://jurassicpark.wikia.com');

echo 'Status code: ' . $session->getStatusCode() . "\n";
echo 'Current URL: ' . $session->getCurrentUrl() . "\n";
echo 'Not HTTP only cookie: ' . $session->getCookie('XDEBUG_SESSION');
$page = $session->getPage();


