<?php

namespace Krak\Orm\ClassMeta\Db;

use Krak\Orm\ClassMeta\Db\Fields;

/**
 * Field Builder
 * Clean interface for building fields
 */
class FieldBuilder
{
    /**
     * create a boolean field
     */ 
    public function bool($name, $sql_name = null)
    {
        return new Fields\BoolField($name, $sql_name);
    }

    /**
     * create a datetime field
     */
    public function dateTime($name, $sql_name = null)
    {
        return new Fields\DateTimeField($name, $sql_name);
    }
    
    /**
     * create a float field
     */ 
    public function float($name, $sql_name = null)
    {
        return new Fields\FloatField($name, $sql_name);
    }
    
    /**
     * create an int field
     */ 
    public function int($name, $sql_name = null)
    {
        return new Fields\IntField($name, $sql_name);
    }
    
    /**
     * create a serializeable field
     */ 
    public function serializable($name, $sql_name = null)
    {
        return new Fields\SerializableField($name, $sql_name);
    }
    
    /**
     * create a string field
     */ 
    public function string($name, $sql_name = null)
    {
        return new Fields\StringField($name, $sql_name);
    }
    
    /**
     * create a model field
     */ 
    public function model($name, $sql_name = null)
    {
        return new Fields\ModelField($name, $sql_name);
    }
    
    /**
     * create a collection field
     */ 
    public function collection($name, $sql_name = null)
    {
        return new Fields\Field($name, $sql_name);
    }
}
