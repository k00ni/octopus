#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Octopus\Command\Update;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new Update());
$application->run();
