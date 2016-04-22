<?php

namespace Core;

abstract class Entity extends \YetORM\Entity
{
    /** 
     * Special behaviour for json & \Nette\Utils\DateTime properties
     * @see parent     
     * 
     * @param string $name
     *      
     */
    function __get($name)
    {
        $value = parent::__get($name);
        
        $ref = static::getReflection();
        
        if($ref->getEntityProperty($name)->description == 'json')
        {        
            $value = \Nette\Utils\Json::decode($value, \Nette\Utils\Json::FORCE_ARRAY);
        }
 
        return $value;
    }

    /** 
     * Special behaviour for json & \Nette\Utils\DateTime properties
     * @see parent
     * 
     * @param string $name
     * @param mixed $value
     * 
     */
    function __set($name, $value)
    {
        $ref = static::getReflection();
        
        if($ref->getEntityProperty($name)->description == 'json')
        {
            if(is_array($value))
            {
                $value = \Nette\Utils\Json::encode($value);
            }
        }
        
        if($ref->getEntityProperty($name)->type == 'Nette\Utils\DateTime' && !is_null($value))
        {
            $value = \Nette\Utils\DateTime::from($value);
        }
        
        return parent::__set($name, $value);
    }
    
    /** 
     * Fill entity from array or ArrayHash
     * 
     * @param array|\Nette\Utils\ArrayHash
     * 
     * @return void
     */
    public function fillFromArray($values)
    {
        $ref = static::getReflection();
        
        foreach($ref->getEntityProperties() as $name => $property)
        {
            $functionName = 'set' . ucfirst($name);
            
            if(isset($values[$name]) && !$property->readonly)
            { 
                /** 
                 * Entity has special set function? Use it instead of simple set
                 */
                if(method_exists($this, $functionName))
                {
                    $this->$functionName($values[$name]);
                }
                else
                {
                    /** 
                     * Set NULL for nullable properties 
                     */
                    if(isset($property->nullable) && $property->nullable && empty($values[$name]))
                    {
                        $values[$name] = NULL;
                    } 
                    
                    /** 
                     * Convert strings to int 
                     */
                    if($property->getType() == 'integer' && !empty($values[$name]))
                    {
                        $values[$name] = intval($values[$name]);
                    }
                    
                    /** 
                     * Convert bool to int 
                     */
                    elseif($property->getType() == 'integer' && is_bool($values[$name]))
                    {
                        $values[$name] = $values[$name] ? 1 : 0;
                    }    
                        
                    /** 
                     * Convert array to json  
                     */
                    if($property->description == 'json' && is_array($values[$name]))
                    {                        
                        $this->$name = \Nette\Utils\Json::encode($values[$name]);
                    }
                    else
                    {
                        $this->$name = $values[$name];
                    }                                        
                }
            }
        }
    }
    
    /**
     * Transform entity Array
     *
     * @param array $excludedProperties - If you don't want some keys (hide ID or password)
     * 
     * @return Array
     */
    public function toArray($excludedProperties = [])
    {
        if(!$excludedProperties instanceof \Nette\Utils\ArrayHash && !is_array($excludedProperties))
        {
            throw new \Exception('Excluded properties should be Array or \Nette\Utils\ArrayHash');
        }
        
        $ref = static::getReflection();
	$values = [];
        
	foreach ($ref->getEntityProperties() as $name => $property) 
        {   
            if(array_search($name, $excludedProperties) === FALSE)
            {
                if($property instanceof \YetORM\Reflection\MethodProperty) 
                {
                    $value = $this->{'get' . $name}();
                } 
                else 
                {
                    $value = (!empty($this->$name)) ? $this->$name : NULL;
                }

                if(!($value instanceof \YetORM\EntityCollection || $value instanceof \YetORM\Entity))
                {
                    $values[$name] = $value;
                }
            }
	}

        return $values;
    }
}
