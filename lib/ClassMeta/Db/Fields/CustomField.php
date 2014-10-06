<?php

namespace Krak\Orm\ClassMeta\Db\Fields;

class CustomField extends AbstractField;
{
    protected $to_sql_func;
    protected $to_php_func;
    
    /**
     * @param string $name
     * @param string $sql_name Name of the sql field name
     */
    public function __construct(
        $name,
        $sql_name = null,
        \Closure $to_sql_func,
        \Closure $to_php_func
    )
    {
        parent::__construct($name, $sql_name);
        $this->to_sql_func = $to_sql_func;
        $this->to_php_func = $to_php_func;
    }

    /**
     * @inheritDoc
     */
    public function toSqlType($val)
    {
        return $this->to_sql_func($val);
    }
    
    /**
     * @inheritDoc
     */
    public function toPhpType($val)
    {
        return $this->to_php_func($val);
    }
}
