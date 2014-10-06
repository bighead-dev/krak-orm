<?php

namespace Krak\Orm;

use Krak\Orm\ClassMeta\ClassMeta;

interface Query
{
    /**
     * Sets the class meta
     *
     * @param ClassMeta $cm
     */
    public function setClassMeta(ClassMeta $cm);

    /**
     * Turn the query into a hashed string to give an identification for the query.
     * A query built with the same parameters will have the same hash.
     *
     * @return string
     */
    public function hash();
    
    /**
     * Execute the query
     *
     * @return mixed
     */
    public function execute();
}
