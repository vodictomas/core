<?php

namespace Core;

class Repository extends \YetORM\Repository
{    
    /**
     * Return ResultSet by custom SQL
     * 
     * @param string $sql
     * 
     * @return \Nette\Database\ResultSet
     */
    public function query($sql)
    {        
        return $this->database->query($sql);
    }
    
    /** 
     * Return single entity by criteria
     * 
     * @param array $criteria
     * 
     * @return Entity|NULL
     */    
    public function getBy(array $criteria)
    {
        return parent::getBy($criteria);
    }
    
    /** 
     * Return EntityCollection by criteria
     * 
     * @param array $criteria
     * 
     * @return \YetORM\EntityCollection
     */
    public function findBy(array $criteria)
    {
        return parent::findBy($criteria);
    }
    
    /** 
     * Return EntityCollection (all)
     * 
     * @return \YetORM\EntityCollection
     */
    public function findAll()
    {
        return $this->findBy([]);
    }
    
    /** 
     * Return pairs from current table
     * 
     * @param string $key Column use like key
     * @param string $value Column use like value
     * @param array $criteria
     * @param string|NULL $order
     * 
     * @return array
     */
    public function fetchPairs($key, $value, array $criteria = [], $order = NULL)
    {       
        return ($order) ? $this->getTable()->where($criteria)->order($order)->fetchPairs($key, $value) : $this->getTable()->where($criteria)->fetchPairs($key, $value);
    }
    
    /**
     * Remove single instance from database
     * 
     * @param \Core\Entity $entity
     * 
     * @return bool
     */
    public function save(\Core\Entity $entity)
    {
        return $this->persist($entity);
    }
    
    /** 
     * Return bool (ID or FALSE)
     * 
     * @param \YetORM\Entity $entity
     * 
     * @return int|FALSE
     */
    public function persist(\YetORM\Entity $entity)
    {       
        $this->checkEntity($entity);

        $store = new \Core\StoreManager\StoreManager();

        /** Store changes if is allowed */
        if($store->avaibleStore($this->table))
        {
            $store->store($this, $entity);
        }
         
        $me = $this;
        
        return $this->transaction(function () use ($me, $entity) 
        {
            $record = $entity->toRecord();
            
            if($record->hasRow())
            {
                $record->update();
                
                return $record->id;
            }

            $inserted = $me->getTable()->insert($record->getModified());
            $record->setRow($inserted);            

            return $record->id;
        });
    }
    
    /**
     * Remove single instance from database
     * 
     * @param \Core\Entity $entity
     * 
     * @return bool
     */
    public function remove(\Core\Entity $entity)
    {
        return parent::delete($entity);
    }
    
    /** 
     * Check if column exist in table
     * 
     * @param string $column
     * 
     * @return bool
     */
    public function columnExist($column)
    {
        $query = $this->database->query("SHOW COLUMNS FROM " . $this->getTableName() . " LIKE '" . $column . "';");
        
        return $query->fetch() ? TRUE : FALSE;
    }
    
    /** 
     * Return Nette Selection for current table
     * 
     * @return \Nette\Database\Table\Selection
     */
    public function getTable($table = NULL)
    {
        return parent::getTable($table);
    }
}
