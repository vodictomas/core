<?php

namespace Core\StoreManager;

use \Nette\Utils\Finder as Finder;

/** 
 * Store manager allow save all changes in data
 */
class StoreManager extends \Nette\Object
{
    const DATA_PATH = '/../../../data/';
    
    /** 
     * List active store tables 
     */
    private $avaibleTables;
    
    /** 
     * If exist some user - print his ID otherwise set 0 (system) 
     */
    private $userId;
    
    /** 
     * Constructor
     * 
     * @note Maybe get User by DI
     */
    public function __construct() 
    {
        if(class_exists('\Nette\Environment'))
        {
            $this->userId = \Nette\Environment::getUser()->id;
            $this->avaibleTables = \Nette\Environment::getConfig('store');
        }
        else
        {
            $this->userId = 0;
            $this->avaibleTables = [];
        }
    }
    
    /** 
     * Load all files and prepare data for output
     * 
     * @param string $dir
     * @param int $id
     * 
     * @return array
     */
    public function load($dir, $id)
    {
        /** 
         * No store data yet 
         */
        if(!is_dir(__DIR__ . self::DATA_PATH . $dir . '/' . $this->fileSystemRange($id) . '/' . $id . '/'))
        {
            return NULL;
        }
            
        $finder = iterator_to_array(Finder::findFiles('*.dat')
            ->exclude('origin.dat')
            ->from(__DIR__ . self::DATA_PATH . $dir . '/' . $this->fileSystemRange($id) . '/' . $id . '/')
        )
                ;
        krsort($finder);
        
        $array = [];        
        
        foreach($finder as $file)
        {
            $array[$file->getFileName()]['date'] = $this->parseDate($file->getFileName());

            /** 
             * PHP magic - SPLOBject is bugged on our version production 
             */
            $f = fopen($file->getPathName(), 'r');
            $array[$file->getFileName()]['data'] = json_decode(fread($f, filesize($file->getPathName())), TRUE);
            fclose($f);
        }
        
        return $array;                       
    }
    
    /**
     * Parse date from filename 
     * 
     * @param string $string
     * 
     * @return \Nette\Utils\DateTime
     */
    private function parseDate($string)
    {
        $year = substr($string, 0, 4);
        $month = substr($string, 4, 2);
        $day = substr($string, 6, 2);
        $hour = substr($string, 8, 2);
        $minute = substr($string, 10, 2);
        $second = substr($string, 12, 2);
        
        return new \Nette\Utils\DateTime($year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $second);            
    }
    
    /** 
     * Table is allowed use store module?
     * @note Check table list in config.neon (store)
     * 
     * @return bool
     */
    public function avaibleStore($tableName)
    {
        return in_array($tableName, (array) $this->avaibleTables) ? TRUE : FALSE;
    }

    /**
     * Set right range for ID (file system)
     * @note Number represent 5-thousand of ID's
     * @note Table shouldnt have more than 200000 records - otherwise increase limit (but keep same step)
     * 
     * @param int $item
     * 
     * @return int      
     */
    private function fileSystemRange($item)
    {
        foreach(range(0, 200000, 5000) as $value)
        {
            if($item < $value)
            {
                break;
            }
        }
        
        return substr($value, 0, -3);
    }
    
    /** 
     * Save custom information to data store
     * 
     * @param string $dir
     * @param int $id
     * @param array $data
     * 
     * @return bool
     */
    public function customSave($dir, $id, array $data)
    {
        return $this->fileSave($dir, $id, $data, TRUE);
    }
    
    /** 
     * Store all changes from previous DB stamp
     * 
     * @param \App\Repository\BaseRepository $repository - current Repository
     * @param \App\Entity\BaseEntity $entity - Child of BaseEntity
     * 
     * @return bool|NULL
     */
    public function store($repository, $entity)
    {
        $record = $entity->toRecord();

        /**
         * If entity has row - update - save changes
         */
	if($record->hasRow())
        {
            /**
             * Count differences 
             */
            $diff = array_diff_assoc($entity->toArray(), $repository->getBy(['id' => $entity->id])->toArray());

            /**
             * If exist some differences - save them
             */
            if(!empty($diff))
            {
                $this->fileSave($repository->table->name, $entity->id, $diff);
            }
            else
            {
                return NULL;
            }
        }
        else
        {
            return NULL;
        }
    }
    
    /** 
     * Save file to folders
     * @note Folders are created automatically
     * 
     * @param string $dir
     * @param int $id
     * @param array $data
     * @param bool $custom Save custom
     * 
     * @return bool
     */
    private function fileSave($dir, $id, $data, $custom = FALSE)
    {                
        $date = new \Nette\Utils\DateTime('now');
        
        $pathItem = $this->createPath($dir, $id) . $date->format('YmdHis') . '_' . rand(1000, 9999);
        
        if($custom)
        {
            $pathItem .= '_custom';
        }
        
        /** Who was iniciator */
        if(intval($this->userId) > 0)
        {
            $data['__user'] = $this->userId;
        }
        else
        {
            $data['__user'] = '0';
        }

        /**
         * Save changes
         */
        return file_put_contents($pathItem . '.dat', json_encode($data)) ? TRUE : FALSE;
    }     
    
    /** 
     * Save original data from modulis
     * 
     * @param string $dir
     * @param int $id
     * @param array $data
     * 
     * @return bool
     */
    public function originalSave($dir, $id, $data)
    {                   
        return file_put_contents($this->createPath($dir, $id) . 'origin.dat', json_encode($data)) ? TRUE : FALSE;
    }
    
    /** 
     * Generate path and create dirs
     * 
     * @param int $dir
     * @param int $id
     * 
     * @return string Path to record folder
     */
    private function createPath($dir, $id)
    {
        $path = __DIR__ . self::DATA_PATH . $dir . '/';
        
        /**
         * Create modul folder
         */
        if(!is_dir($path)) 
        {
            mkdir($path);
	}                
        
        $pathSub = $path . $this->fileSystemRange($id) . '/';
        
        /**
         * Check sub dir (file system problem)
         */
        if(!is_dir($pathSub))
        {
            mkdir($pathSub);
        }                
        
        $pathRecord = $pathSub . '/' . $id . '/';
        
        /**
         * Create record folder (id)
         */
        if(!is_dir($pathRecord))
        {
            mkdir($pathRecord);
        }
                
        return $pathRecord;
    }            
}
