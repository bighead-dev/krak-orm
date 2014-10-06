<?php

namespace Krak\Orm\ClassMeta\Db\Fields;

use Krak\Orm\ClassMeta\Db\Field;

abstract class AbstractField implements Field
{
    protected $name;
    protected $sql_name;
    
    /**
     * @param string $name
     * @param string $sql_name Name of the sql field name
     */
    public function __construct($name, $sql_name = null)
    {
        $this->name     = $name;
        $this->sql_name = ($sql_name) ?: $name;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function getSqlName()
    {
        return $this->sql_name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function setSqlName($sql_name)
    {
        $this->sql_name = $name;
    }
    
    /**
     * @inheritDoc
     */
    abstract public function toSqlType($val);
    
    /**
     * @inheritDoc
     */
    abstract public function toPhpType($val);
}
