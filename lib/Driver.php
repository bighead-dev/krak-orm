<?php

namespace Krak\Orm;

/**
 * Orm Driver
 */
interface Driver
{
    /**
     * Retrieve models via query
     */
    public function get(Query $query);
    
    /**
     * Persist models
     */
    public function save($data, $fields);
    
    /**
     * update models
     */
    public function update($data, $fields);
}
