<?php

namespace Krak\Orm;

use Krak\Orm\ClassMeta\ClassMetaMapAware;

/**
 * Persistor
 * Handles the persistance of objects
 */
interface Persistor extends ClassMetaMapAware
{
    /**
     * Writes the model to the persistance layer for the specific
     * fields
     * @param mixed $model
     * @param array|traversable|null $fields The fields to save, null means save all fields
     */
    public function update($model, $fields = null);
    
    /**
     * Writes a collection of models to the persistance layer for the specific
     * fields
     * @param mixed $model
     * @param array|traversable|null $fields The fields to save, null means save all fields
     */
    public function updateCollection($model, $fields = null);
    
    /**
     * Writes the model to the persistance layer for the specific
     * fields
     * @param mixed $model
     * @param array|traversable|null $fields The fields to save, null means save all fields
     */
    public function save($model, $fields = null);
    
    /**
     * Writes a collection of models to the persistance layer for the specific
     * fields
     * @param mixed $model
     * @param array|traversable|null $fields The fields to save, null means save all fields
     */
    public function saveCollection($models, $fields = null);
}
