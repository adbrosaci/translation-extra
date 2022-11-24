<?php declare(strict_types = 1);

use Tester\Environment;
use Tester\Helpers;
use Tracy\Debugger;

require __DIR__ . '/../vendor/autoload.php';

Environment::setup();

define('TEMP_DIR', __DIR__ . '/temp/' . getmypid());

Helpers::purge(TEMP_DIR);
Debugger::$logDirectory = TEMP_DIR;
