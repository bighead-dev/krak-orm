<?php

/**
 * Can an object be traversed?
 */
function krak_orm_is_traversable($val)
{
    return is_array($val) || $val instanceof Traversable;
}
