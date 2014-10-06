<?php

namespace Krak\Orm\Exception;

use RuntimeException;

class ClassMetaNotFoundException extends RuntimeException
{
    public function __construct($class_name)
    {
        parent::__construct(
            sprintf(
                "The ClassMeta instance could not be found for class '%s'",
                $class_name
            )
        );
    }
}
