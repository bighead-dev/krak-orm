<?php

namespace Krak\Tests\Db\ClassMeta;

use Krak\Orm;
use Krak\Tests\KrakTestCase;

class BuilderTest extends KrakTestCase
{
    public function testSimple()
    {
        $builder = new Orm\Driver\Db\ClassMetaBuilder();
        
        $meta = $builder->key('key-1')
            ->table('table-name')
            ->addField('id', Orm\Types::INT)
            ->addField('data', Orm\Types::STR, 'DataField')
            ->build();
        
        //print_r($meta);
        $this->assertEquals(true, true);
    }
    
    public function testBad()
    {
        $this->assertEquals(1, 0);
    }
}
