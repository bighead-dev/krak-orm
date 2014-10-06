<?php

namespace Krak\Orm;

use Krak\Orm\ClassMeta\ClassMetaMapAware;

/**
 * Query Factory
 * Responbile for building the queries
 */
interface QueryFactory extends ClassMetaMapAware
{
    /**
     * Creates a query
     *
     * @param string $key
     * @return Query
     */
    public function create($key);
}
