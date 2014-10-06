<?php

namespace Krak\Orm\ClassMeta\Db\Fields;

class StrField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function toSqlType($val)
    {
        return (string) $val;
    }
    
    /**
     * @inheritDoc
     */
    public function toPhpType($val)
    {
        return $val;
    }
}
