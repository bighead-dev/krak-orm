<?php

namespace Krak\ClassMeta\Db\Relationship;

use Krak\Orm\ClassMeta\Db\ClassMeta;

/**
 * One to One relationship
 */
class OneToOne
{
    /**
     * @var ClassMeta
     */
    private $left;
    
    /**
     * @var string
     */
    private $left_key;
    
    /**
     * @var ClassMeta
     */
    private $right;
    
    /**
     * @var string
     */
    private $right_key;
    
    /**
     * @param ClassMeta $left
     * @param string $left_key Forein key column
     * @param ClassMeta $right
     * @param string $right_key Forein key column
     */
    public function __construct($left, $left_key, $right, $right_key)
    {
        $this->left         = $left;
        $this->left_key     = $left_key;
        $this->right        = $right;
        $this->right_key    = $right_key;
    }
    
    //public function 
}
