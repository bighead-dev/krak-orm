<?php

namespace Krak\Orm;

interface Query
{
    /**
     * Turn the query into a hashed string to give an identification for the query.
     * A query built with the same parameters will have the same hash.
     * @return string
     */
    public function hash();
    
    /**
     * Execute the query
     */
    public function execute();
}
