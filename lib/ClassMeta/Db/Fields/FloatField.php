<?php

namespace Krak\Orm\ClassMeta\Db\Fields;

class FloatField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function toSqlType($val)
    {
        return (float) $val;
    }
    
    /**
     * @inheritDoc
     */
    public function toPhpType($val)
    {
        return (float) $val;
    }
}
