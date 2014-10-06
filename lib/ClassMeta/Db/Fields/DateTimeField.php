<?php

namespace Krak\Orm\ClassMeta\Db\Fields;

use DateTime;

class DateTimeField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function toSqlType($val)
    {
        if ($val instanceof DateTime == false) {
            throw new \RuntimeException('Value is not a valid DateTime object');
        }
        
        return $val->format("Y-m-d H:i:s");
    }
    
    /**
     * @inheritDoc
     */
    public function toPhpType($val)
    {
        return new DateTime($val);
    }
}
