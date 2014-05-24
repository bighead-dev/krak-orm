# Krak Model

A krak model is just a simple ORM around CodeIgniter's query builder. In fact, every method supported in query builder is supported in a Krak model. In other means, a krak model is a php object that allows CRUD operations in a simple OO manner.

## Defining a Krak Model

````php
<?php

namespace MyModelNamespace;

use Krak;

class User extends Krak\Model
{
    /* optional fields that get filled with timestamps */
    protected $created_field = 'created_at';
    protected $updated_field = 'updated_at';
}
````

## Querying a database

````php
// Km is just an example of a typical model namespace.
$user_m = new Km\User();

$user_m->where()->where_in()->order_by() // these functions match CI's query builder methods exactly
        ->get(); 
        
$res = $user_m->result(); // returns the CI result set

foreach ($user_m as $user)
{
    // loops over the CI result set internally
}
````

## Saving/Updating

````php
$user_m = new Km\User();
$user_m->data1      = 'data';
$user_m->key        = 'value';
$user_m->group_id   = 5;
//...
$user_m->save();

// when you save an object, the insert id is placed into the object

echo $user_m->id; // prints out '10' or whatever the insert id was
// if the primary key is set
// i.e.
$user_m->id = 1;
$user_m->update();
````

## Deleting

You can delete a single object or you can delete a set of objects.

*Single*
````php
$user_m = new Km\User();
$user_m->id = 5;
$user_m->delete();

// or

$user_m = new Km\User();
$user_m->where('id', 5)->get();
$user_m->delete();

// or

$user_m = new Km\User(5);
$user_m->delete();
````

All of the above methods will delete the user with ID 5. The delete method only looks for the primary key (id) in the current object, and then runs a delete query with that id. Example just shows creating an object then manually setting the primary key and running delete. There are no select queries. The second and third show a way to query the database to get that user and then delete the queried user.

*Set*
````
$user_m = new Km\User();
$user_m->where('id > ', 9)->delete_set(); /* this deletes all users greater than 9 */
````

## Saving/Deleting Relationships

Technically supported, but deprecated, don't use it

## Events

Krak Models have a simple event system to allow user's to write specific hooks at specific points in the Model process. The two methods responsible for the events are: `add_event_listener` and `trigger`.

`public add_event_listener($callback_method_name, $event_name)`

`public trigger($event_name)`

When you add an event listener for a specific event name string, it creates a queue for that specific event name. Whenever trigger is called with that same event name, all of the event callbacks on that queue will be called.

## Full API

A list of the full API isn't here yet, but you can look at the source code of Krak to get an example of everything that's supported.

