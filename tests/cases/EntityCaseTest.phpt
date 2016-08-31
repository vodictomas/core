<?php 

require_once __DIR__ . '/../boostrap.php';

use Tester\Assert;

/**
 * @testCase
 */
class EntityCaseTest extends \Tester\TestCase
{
    protected $Service;
    
    public function setUp()
    {
        $this->Service = new Service();
        $this->Service->cache->clean([\Nette\Caching\Cache::ALL]);
    }

    /**
     * If possible set NULL thruw new reflection?
     */
    public function testEntitySetNull()
    {
        $zooEntity = new ZooEntity;
        $zooEntity->name = 'Zoo Plzeň';
        $zooEntity->motto = NULL;
        
        Assert::same(NULL, $zooEntity->motto);
    }
    
    /** 
     * Entity to Array
     */
    public function testEntityToArray()
    {
        $animalEntity = new AnimalEntity();
        $animalEntity->name = 'Kangaroo';
        $animalEntity->weight = 15;
        $animalEntity->birth = new \Nette\Utils\DateTime('2015-01-01 12:00:00');
        $animalEntity->parameters = ['color' => 'brown', 'ears' => 2, 'eyes' => 1];

        $array = $animalEntity->toArray(['id']);
        
        Assert::true(is_array($array));
    }

    /** 
     * Entity filled from Array
     */
    public function testEntityFromArray()
    {
        $array = [
            'name' => 'Kangaroo',
            'weight' => 15,
            'birth' => new Nette\Utils\DateTime(),
            'parameters' => [
                'color' => 'brown',
                'ears' => 2,
                'eyes' => 1
            ]            
        ];
        
        $kangarooEntity = new AnimalEntity();
        $kangarooEntity->fillFromArray($array);

        Assert::same(15, $kangarooEntity->weight);        
    }
    
    /** 
     * Entity save to database
     */
    public function testEntitySaveToDatabase()
    {
        $animalEntity = new AnimalEntity();
        $animalEntity->name = 'Kangaroo';
        $animalEntity->weight = 15;
        $animalEntity->birth = new \Nette\Utils\DateTime('2015-01-01 12:00:00');
        $animalEntity->parameters = ['color' => 'brown', 'ears' => 2, 'eyes' => 1];
        
        $repository = new AnimalRepository($this->Service->database);
        $repository->save($animalEntity);

        /* @var $loadedEntity AnimalEntity */
        $loadedEntity = $repository->getBy(['name' => 'Kangaroo']);
        
        /** 
         * TEST: save entity to database
         */
        Assert::true($loadedEntity instanceof \Core\Entity);            
        
        /** 
         * TEST: save & load it back like array via JSON
         */
        Assert::same(['color' => 'brown', 'ears' => 2, 'eyes' => 1], $loadedEntity->parameters);               
        
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
    }
}

$testCase = new EntityCaseTest();
$testCase->run();