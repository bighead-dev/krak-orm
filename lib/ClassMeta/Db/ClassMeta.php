<?php

namespace Krak\Orm\ClassMeta\Db;

use RuntimeException;
use Krak\Orm\ClassMeta\ClassMeta;

class ClassMeta implements ClassMeta
{
    /**
     * The unique identifier in the ClassMetaMap
     * @var string
     */
    private $key;
    
    /**
     * @var array
     */
    private $field_map;
    
    /**
     * @var string
     */
    private $table;
    
    public function setKey($key)
    {
        $this->key = $key;
    }
    
    public function getKey()
    {
        return $this->key;
    }
    
    public function setFieldMap($field_map)
    {
        $this->field_map = $field_map;
    }
    
    public function setTable($table)
    {
        $this->table = $table;
    }
    
    /**
     * Gets a field from the field_map
     * @param string $field
     */
    public function getField($field)
    {
        if (!array_key_exists($field, $this->field_map))
        {
            throw new RuntimeException(
                sprintf(
                    "Field '%s' not found for ClassMeta '%s'",
                    $field,
                    $this->key
                )
            );
        }
        
        return $this->field_map[$field];
    }
}
