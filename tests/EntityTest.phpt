<?php
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php'; 

require __DIR__ . '/../src/Entity.php';
require __DIR__ . '/../src/Repository.php';
require __DIR__ . '/../src/StoreManager/StoreManager.php';

require __DIR__ . '/../models/AnimalEntity.php';
require __DIR__ . '/../models/AnimalRepository.php';
require __DIR__ . '/../models/Service.php';

\Tester\Environment::setup();

date_default_timezone_set('UTC');

$service = new Service();

/**
 * TEST: Entity->toArray()
 */
$animalEntity = new AnimalEntity();
$animalEntity->name = 'Kangaroo';
$animalEntity->weight = 15;
$animalEntity->birth = new \Nette\Utils\DateTime('2015-01-01 12:00:00');
$animalEntity->parameters = ['color' => 'brown', 'ears' => 2, 'eyes' => 1];

$array = $animalEntity->toArray(['id']);

Assert::true(is_array($array));

/**
 * TEST: Entity->fillFromArray()
 */
$kangarooEntity = new AnimalEntity();
$kangarooEntity->fillFromArray($array);

Assert::same(15, $kangarooEntity->weight);

/** 
 * TEST: save entity
 */
$rep = new AnimalRepository($service->database);
$rep->persist($kangarooEntity);

/* @var $loadedEntity AnimalEntity */
$loadedEntity = $rep->getBy(['name' => 'Kangaroo']);

/** 
 * TEST: save array to database & load it back like array via JSON
 */
Assert::true($loadedEntity instanceof \Kravcik\Core\Entity);    
        
/** 
 * TEST: save & load \Nette\Utils\DateTime
 */    
Assert::true($loadedEntity->birth instanceof \Nette\Utils\DateTime);

/** 
 * TEST: check right type of date
 */
Assert::same($loadedEntity->birth->format('Y'), '2015');
Assert::same($loadedEntity->birth->format('m-d'), '01-01');
Assert::same($loadedEntity->birth->format('H:i:s'), '12:00:00');