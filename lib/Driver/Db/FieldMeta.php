<?php

namespace Krak\Orm\Driver\Db;

/**
 * Field Meta Data
 * Acts simply as a struct
 */
class FieldMeta
{
    public $name;
    public $sql_name;
    public $type;
    
    /**
     * @param string $name
     * @param int $type One of Krak\Orm\Types::* constants
     * @param string $sql_name Name of the sql field name
     */
    public function __construct($name, $type, $sql_name = null)
    {
        $this->name     = $name;
        $this->type     = $type;
        $this->sql_name = ($sql_name) ?: $name;
    }
}
