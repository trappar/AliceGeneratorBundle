<?php

if (!is_file($autoloadFile = __DIR__ . '/../vendor/autoload.php')) {
    throw new \RuntimeException('Did not find vendor/autoload.php. Did you run "composer install --dev"?');
}
$loader = require $autoloadFile;
$loader->addPsr4('Trappar\AliceGeneratorBundle\Tests\\', __DIR__ . '/Trappar/AliceGeneratorBundle');
