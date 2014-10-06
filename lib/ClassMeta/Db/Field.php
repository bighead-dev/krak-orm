<?php

namespace Krak\Orm\ClassMeta\Db;

/**
 * Field
 */
interface Field
{
    /**
     * Get the php classes field name
     */
    public function getName();
    
    /**
     * Get the php classes sql name
     */
    public function getSqlName();
    
    /**
     * Converts the given value into the appropriate field type for sql
     */
    public function toSqlType($val);
    
    /**
     * Converts the given value into the appropriate php field type
     */
    public function toPhpType($val);
}
