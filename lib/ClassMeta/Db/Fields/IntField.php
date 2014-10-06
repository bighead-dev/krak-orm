<?php

namespace Krak\Orm\ClassMeta\Db\Fields;

class IntField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function toSqlType($val)
    {
        return (int) $val;
    }
    
    /**
     * @inheritDoc
     */
    public function toPhpType($val)
    {
        return (int) $val;
    }
}
