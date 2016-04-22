<?php

class Service extends Nette\Object
{
    /* */
    public $database;
    
    public function __construct()
    {
        $connection = new \Nette\Database\Connection('mysql:host=127.0.0.1;dbname=test', 'root', '');
        \Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/../models/sql/db.sql');
        $structure = new \Nette\Database\Structure($connection, new \Nette\Caching\Storages\FileStorage(__DIR__ . '/../temp'));
        $conventions = new \Nette\Database\Conventions\DiscoveredConventions($structure);
        $this->database = new \Nette\Database\Context($connection, $structure, $conventions, new \Nette\Caching\Storages\FileStorage(__DIR__ . '/../temp'));
    }
    
}
