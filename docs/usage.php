<?php

// CASE 1 - simple get all users
$q = krak_orm()->createQuery('Mdl\User');

// returns an array of Mdl\User objects hydrated properly
$users = krak_orm()->get($q);

// CASE 1.1 - simple get with syntactic sugar
$users = Mdl\Queries\UserQuery::getAll();

// CASE 2 - simple wheres
$q = krak_orm()->createQuery('Mdl\User');
$q->where('id', 'eq', 1)
    ->andWhere('id > 50')
    ->orWhere("field", 'eq', 32);

// returns an array of Mdl\User objects hydrated properly
$users = krak_orm->get($q);

// CASE 2.1 - where's with expression
$q = krak_orm()->createQuery('Mdl\User');
$q->where(
    $q->expr()->andX(
        $q->eq('username', 'sammy'),
        $q->gt('age', 50)
    )
);
// ...

// CASE 2.2 - set parameters
$q = krak_orm()->createQuery('Mdl\User');

// set the parameters by either of these methods
$q->where('id = ?');
$q->setParameter(0, $id);
// or
$q->setParameters([$id]);

// or
$q->where('id = :id'); // using PDO
$q->setParameter(':id', $id);

// CASE 2.3 - using other models in the where
$q = krak_orm()->createQuery('Mdl\User');

$q->where('{Mdl\User}.id = 50')
    ->alias('u')
    ->andWhere('{u}.usename like "bob%"')
    ->andWhere('{Mdl\State}.code = "US"');

// CASE 3 - using with to load associations
$q = krak_orm()->createQuery('Mdl\User');

// load the user query with it's states
$q->alias('u')
    ->with('state') // returns a state query
    ->alias('s')
    ->where('{u}.id > 40')
    ->andWhere('{s}.code = "US"');

$users = krak_orm()->get($q);

print_r($users[0]->state); // the state model was loaded up with the user

// CASE 4 - hydration modes
$q = krak_orm()->createQuery('Mdl\User');

// builds up an array of associative arrays
$q->setHydrationMode(Krak\Orm\HydrationModes::ASSOC);

// or

// returns an array of numeric indexed arrays
$q->setHydrationMode(Krak\Orm\HydrationModes::ROW);

// or

// returns an array of the first field selected
$q->setHydrationMode(Krak\Orm\HydrationModes::SCALAR);

// or

// the default hydration mode, returns an array of hydrated models
$q->setHydrationMode(Krak\Orm\HydrationModes::MODEL);

// CASE 4.1 - using hydrators
$stmt = $pdo->query("select * from users");

$gen = function($rows) {
    foreach ($rows as $row)
    {
        yield [
            'id'        => $row[0],
            'username'  => $row[1],
            'email'     => $row[2],
        ];
    }
};

$hydrator = krak_orm()->getClassMetaMap()->get('Mdl\User')->createHydrator();
$hydrator->setHydrationMode(Krak\Orm\HydrationModes::MODEL);
$results = $hydrator->hydrate($gen($stmt));

// returns 
$users = krak_orm()->get($q);
