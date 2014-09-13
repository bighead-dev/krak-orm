<?php

namespace Krak\Orm\Driver\Db;

class ClassMeta
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
    
    public function setFieldMap($field_map)
    {
        $this->field_map = $field_map;
    }
    
    public function setTable($table)
    {
        $this->table = $table;
    }
}
