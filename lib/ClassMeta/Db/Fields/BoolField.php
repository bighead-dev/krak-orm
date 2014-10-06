<?php

namespace Krak\Orm\ClassMeta\Db\Fields;

class BoolField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function toSqlType($val)
    {
        return (bool) $val;
    }
    
    /**
     * @inheritDoc
     */
    public function toPhpType($val)
    {
        return (bool) $val;
    }
}
