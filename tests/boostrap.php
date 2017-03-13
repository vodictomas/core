<?php

require __DIR__ . '/../vendor/autoload.php'; 

require __DIR__ . '/../src/Core/Entity.php';
require __DIR__ . '/../src/Core/Repository.php';

require __DIR__ . '/models/AnimalEntity.php';
require __DIR__ . '/models/ZooEntity.php';
require __DIR__ . '/models/AnimalRepository.php';
require __DIR__ . '/models/Service.php';

\Tester\Environment::setup();

date_default_timezone_set('UTC');

\Tester\Environment::lock('core', __DIR__ . '/../temp');
