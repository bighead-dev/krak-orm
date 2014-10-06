<?php

namespace Krak\Orm\Exception;

use RuntimeException;

class NotImplementedException extends RuntimeException
{
    public function __construct($feature)
    {
        parent::__construct(
            sprintf(
                "The feature '%s' has not yet been implemented",
                $feature
            )
        );
    }
}
