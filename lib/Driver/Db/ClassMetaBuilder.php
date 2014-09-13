<?php

namespace Krak\Orm\Driver\Db;

/**
 * ClassMeta Builder
 * Helper class for easy generation of ClassMeta Objects
 */
class ClassMetaBuilder
{
    /**
     * @var ClassMeta
     */
    private $meta;
    
    private $fields_map = [];

    public function __construct()
    {
        $this->init();
    }
    
    public function init()
    {
        $this->meta = new ClassMeta(); 
        return $this;
    }
    
    public function build()
    {
        $this->meta->setFieldMap($this->fields_map);
        $meta = $this->meta;
        $this->init();
        return $meta;
    }
    
    public function getClassMeta()
    {
        return $this->meta;
    }
    
    public function key($key)
    {
        $this->meta->setKey($key);
        return $this;
    }
    
    public function table($table)
    {
        $this->meta->setTable($table);
        return $this;
    }
    
    public function addField($name, $type, $sql_name = null)
    {
        $this->fields_map[$name] = new FieldMeta($name, $type, $sql_name);
        return $this;
    }
}
