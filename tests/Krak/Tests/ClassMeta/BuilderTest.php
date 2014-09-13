<?php

namespace Krak\Tests\ClassMeta;

use Krak\Orm;

class BuilderTest implements \Krak\Tests\Test
{
    public function main($argv)
    {
        $builder = new Orm\Driver\Db\ClassMetaBuilder();
        
        $meta = $builder->key('key-1')
            ->table('table-name')
            ->addField('id', Orm\Types::INT)
            ->addField('data', Orm\Types::STR, 'DataField')
            ->build();
        
        print_r($meta);
    }
}
