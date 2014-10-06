<?php

namespace Krak\Orm;

use Krak\Orm\ClassMeta\ClassMetaMap;
use Traversable;

/**
 * Orm - The Object Relation Mapper
 * Consistent api for querying, hydrating, marshalling, and psersisting objects
 */
class Orm
{
    /**
     * @var ClassMetaMap
     */
    private $class_meta_map;
    
    /**
     * @var QueryFactory
     */
    private $query_factory;

    /**
     * @var Persistor
     */
    private $persistor;

    /**
     * @param ClassMetaMap $map
     */
    public function __construct(ClassMetaMap $map)
    {
        $this->class_meta_map = $map;
    }

    /**
     * Sets the query factory
     *
     * @param QueryFactory $qf
     */
    public function setQueryFactory(QueryFactory $qf)
    {
        $this->query_factory = $qf;
        $this->query_factory->setClassMetaMap($this->class_meta_map);
        
        return $this;
    }
    
    /**
     * returns the query factory
     *
     * @return QueryFactory
     */
    public function getQueryFactory()
    {
        return $this->query_factory;
    }
    
    /**
     * Sets the persistor
     *
     * @param Persistor $persistor
     */
    public function setPersistor(Persistor $persistor)
    {
        $this->persistor = $persistor;
        $this->persistor->setClassMetaMap($this->class_meta_map);
        
        return $this;
    }
    
    /**
     * Returns the persistor
     *
     * @return Persistor
     */
    public function getPersistor()
    {
        return $this->persistor;
    }

    /**
     * Helper function for creating queryies
     *
     * @return Query
     */
    public function query($key)
    {
        return $this->query_factory->create($key);
    }

    /**
     * Retrieve models via query
     */
    public function get(Query $query)
    {
        
    }
    
    /**
     * Persist models
     */
    public function save($data, $fields = null)
    {
        if ($this->isTraversable($data)) {
            return $this->persistor->saveCollection($data, $fields);
        }
        
        return $this->persistor->save($data, $fields);
    }
    
    /**
     * update models
     */
    public function update($data, $fields = null)
    {
        if ($this->isTraversable($data)) {
            return $this->persistor->updateCollection($data, $fields);
        }
        
        return $this->persistor->update($data, $fields);
    }
    
    private function isTraversable($item)
    {
        return is_array($item) || $item instanceof Traversable;
    }
}
