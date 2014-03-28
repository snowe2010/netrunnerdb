<?php
// application.php

use Netrunnerdb\BuilderBundle\Console\HighlightCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

require_once __DIR__.'/bootstrap.php.cache';
require_once __DIR__.'/AppKernel.php';

$kernel = new AppKernel('prod', false);
$application = new Application($kernel);
$application->add(new HighlightCommand);
$application->run();