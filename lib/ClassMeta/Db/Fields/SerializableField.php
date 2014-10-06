<?php

namespace Krak\Orm\ClassMeta\Db\Fields;

/**
 * Field for storing serialized data
 */
class SerializableField extends AbstractField
{
    const TYPE_PHP          = 1; /* php serialize */
    const TYPE_JSON_OBJ     = 2; /* json_encode */
    const TYPE_JSON_ASSOC   = 3; /* json_encode assoc true */

    private $serialize_type = self::TYPE_PHP;
    
    public function setSerializeType($type)
    {
        $this->serialize_type = $type;
    }
        
    /**
     * Converts the given value into the appropriate field type for sql
     */
    public function toSqlType($val)
    {
        switch ($this->serialize_type)
        {
            case self::TYPE_PHP:
                return serialize($val);
            case self::TYPE_JSON_OBJ:
            case self::TYPE_JSON_ASSOC:
                return json_encode($val);
            default:
                throw new \RuntimeException(
                    'Invalid serialize type'
                );
        }
    }
    
    /**
     * Converts the given value into the appropriate php field type
     */
    public function toPhpType($val)
    {
        switch ($this->serialize_type)
        {
            case self::TYPE_PHP:
                return unserialize($val);
            case self::TYPE_JSON_OBJ:
                return json_decode($val);
            case self::TYPE_JSON_ASSOC:
                return json_decode($val, true);
        }
    }
}
