<?php

namespace Core;

class DatabaseReflection extends \Nette\Database\Conventions\StaticConventions
{
    public function getHasManyReference($table, $key)
    {
        $table = $this->getColumnFromTable($table);

        $array = explode('_', $table);
        
        if(count($array) > 1)
        {
            $table_prefix = str_replace($array[0] . '_', '', $table);
        }
        else
        {
            $table_prefix = $table;
        }
        
        return [
                sprintf($this->table, $key, $table),
                sprintf($this->foreign, $table_prefix, $key),
        ];
	

    }
    
    public function getBelongsToReference($table, $key)
    {
        parent::getBelongsToReference($table, $key);
    }
}
