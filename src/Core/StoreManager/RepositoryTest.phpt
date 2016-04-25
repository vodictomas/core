<?php
use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php'; 

require __DIR__ . '/../src/Core/Entity.php';
require __DIR__ . '/../src/Core/Repository.php';
require __DIR__ . '/../src/Core/StoreManager/StoreManager.php';

require __DIR__ . '/../models/AnimalEntity.php';
require __DIR__ . '/../models/AnimalRepository.php';
require __DIR__ . '/../models/Service.php';

/**
 * @testCase <-- tohle ti bude každou metodu test*() pouštět paralelně a počítat jí jako samostatný test case
 */

\Tester\Environment::setup();



date_default_timezone_set('UTC');

$service = new Service();

$animalEntity = new AnimalEntity();
$animalEntity->name = 'Gorilla';
$animalEntity->weight = 350;
$animalEntity->birth = new \Nette\Utils\DateTime('1998-10-01 12:00:00');
$animalEntity->parameters = ['color' => 'black', 'ears' => 1, 'eyes' => 2];

$animalEntity2 = new AnimalEntity();
$animalEntity2->name = 'Giraffe';
$animalEntity2->weight = 600;
$animalEntity2->birth = new \Nette\Utils\DateTime('1992-03-01 12:00:00');
$animalEntity2->parameters = ['color' => 'yellow', 'ears' => 2, 'eyes' => 2];

/** 
 * TEST: Save new entities to DB 
 */
$repository = new AnimalRepository($service->database);
$result = $repository->save($animalEntity);

Assert::type('int', $result);

$result2 = $repository->save($animalEntity2);

Assert::type('int', $result2);

/** 
 * TEST: Load entities from DB
 */
$collection = $repository->findAll();

Assert::true($collection instanceof \YetORM\EntityCollection);
Assert::same(2, $collection->count());

die;

/**
 * TEST: Load entity from DB by criteria
 */
/* @var $entity AnimalEntity */
$entity = $repository->getBy(['name' => 'Giraffe']);

Assert::true($entity instanceof \Core\Entity);
Assert::same('Giraffe', $entity->name);

/** 
 * TEST: Update entity
 */
$entity->weight = 800;

$id = $repository->save($entity);

/* @var $loadedEntity AnimalEntity */
$loadedEntity = $repository->getBy(['id' => $id]);

Assert::same(800, $loadedEntity->weight);

/** 
 * TEST: Fetch pairs
 */
$pairs = $repository->fetchPairs('id', 'name', [], 'id DESC');

Assert::type('array', $pairs);

Assert::same('Giraffe', $pairs[2]);

/** 
 * TEST: Remove entity from DB
 */
$remove = $repository->remove($loadedEntity);

Assert::same(TRUE, $remove);

$deletedEntity = $repository->getBy(['id' => $id]);

Assert::same(NULL, $deletedEntity);