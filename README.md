Krak
====

Simple ORM for CI 3.0

# TODO

- Finish implementing latest rendition of Krak\Model
	- <s>related get</s>
	- <s>saveing relations</s>
	- deleting relations
	- accessing join table
	- finish mapping out how save, get, and delete work without krak obj
	- adding syntactic sugar for saving, deleting, and getting relations
	- Add the unbuffered iterator for Krak
	- <s>Add the buffered iterator for Krak</s>
- Create Result class
	-	add ability for Krak\Result to support all of the same methods,
		so we need to create duplicate methods in Krak\Model to allow for syntactic sugar
		for Krak\Results
- Finish Documentation
	- api
	- Relationships
	- Krak in Controllers
	- Using Krak
	- Streamlining Krak
	- How Krak works
- Testing
	- Test Krak\model: getting, saving, deleting, related material
	- Test the iterators
	- Test Result class
	- Profile against older versions of Krak (krakatoa, qb_model) and Datamapper

# Sample World

Let's come up with a sample database world to use in the coming examples. Our database has the following
entities

- Riders
- Rider\Videos (riders have their own videos not related to the main videos table)
- Divisions (each rider can be apart of many divisions
- Sports

with the following relationships

````
Riders          -> many -> Rider\Videos
Riders          -> many -> Divisions
Rider\Videos    -> one  -> Riders
Rider\Videos    -> one  -> Divisions
Divisions       -> many -> Riders
Divisions       -> many -> Videos
Divisions       -> one  -> Sports
Sport           -> many -> Divisions
````

# Relationships


There are three types of relationships in Krak: parent, child, and buddy. And setting up
relationships in krak is easy to use.

## Parent, Child, and Buddy

Parent and child both refer to foreign key constraint relationship the two tables have. If a table2 has an
fkey to table1, then table2 is a child of table1 and table1 is a parent of table2. The buddy relationship is
equivalent to many-to-many. Every buddy relationship needs to have a special "join" table (a table to
join the main two table together).

Let's see how we define that in Krak syntax.

**Rider**
````php
<?php
class Rider extends Krak\Model
{
    protected $parent_of = array(
        'Rider\Video'
    );
    
    protected $buddy_of = array(
        'Division'
    );
}

/* End of file Rider.php */
/* Location: ./application/models/Rider.php */
````

**Rider\Video**
````php
<?php
namespace Rider;

class Video extends \Krak\Model
{
    protected $child_of = array(
        'Rider', 'Division'
    );
}

/* End of file Video.php */
/* Location: ./application/models/Rider/Video.php */
````

**Division**
````php
<?php
class Division extends Krak\Model
{
    protected $parent_of = array(
        'Rider\Video'
    );
    
    protected $child_of = array(
        'Sport'
    );
    
    protected $buddy_of = array(
        'Rider'
    );
}

/* End of file Division.php */
/* Location: ./application/models/Division.php */
````

**Sport**
````php
<?php
class Sport extends Krak\Model
{
    protected $parent_of = array(
        'Division'
    );
}

/* End of file Sport.php */
/* Location: ./application/models/Sport.php */
````

## Using Relationships

To use these relationships in your controller code, you use code like so.

````php
$r = new Rider();
$r->where('id', 1)->get();
$r->rider_video->get();
$r->division->get();

$d = new Division(1);
$d->sport->get();
$d->rider_video->get();
$d->rider->get();

/* ... */
````
Relationships don't need to be specified on both sides if you only use the relationship from
one model meaning if you specify that Rider is a parent of Rider\Video in the Rider model **AND** only access
that relationship from the Rider model, then you *don't* need to specify that Rider\Video is a child of Rider
in the Rider\Video model. Same goes for the Rider\Video model. If you specify that Rider\Video is a child of
Rider and only access that relationship from Rider\Video then you don't need to specify that Rider is a parent
of Rider\Video in the Rider model.

# Advanced Relationships

Krak also supports advanced relationships and complete customization in its models also. Krak can support
recursive relationships, multiple relationships to the same model. It also can allow customization
of these relationships are named in the table.

## Relationship customization

**Division**
````php
<?php
class Division extends Krak\Model
{
    /*
     * The index of the array will be the same name of the related object.
     * e.g.
     * $d = new Division();
     * $d->rider_video->get();  // rider_video is also the index of the array
     *
     * rider_video was something like 'd_vids', then we'd access the related
     * object with that name
     * e.g.
     * $d = new Division();
     * $d->d_vids->get();
     */
    protected $parent_of = array(
        'rider_video'   => array(   // the index of the array will default to the class name strtolower(str_replace('\\', '_', $class_name))
            'class'         => 'Rider\Video',
            'this_column'   => 'division_id'    // defaults to $this->model . '_' . $this->primary_key
        )
    );
    
    protected $child_of = array(
        'sport' => array(
            'class'         => 'Sport',
            'parent_column' => 'sport_id'   // defaults to parent->model . '_' . parent->primary_key
        )
    );
    
    protected $buddy_of = array(
        'rider' => array(
            'class'         => 'Rider',
            'this_column'   => 'division_id',       // defaults to $this->model . '_' . $this->primary_key
            'buddy_column'  => 'rider_id'           // defaults to buddy->model . '_' . buddy->primary_key
            'join_table'    => 'divsions_riders'    // defaults to ($this->table < $buddy->table) ? $this->table . '_' . $buddy->table : $buddy->table . '_' . $this->table
        )
    );
}

/* End of file Division.php */
/* Location: ./application/models/Division.php */
````

Let's look more closely at how this all works.

## Parent_of

The parent_of array can only take two parameters: `class` and `this_column`. Furthermore, when the `class`
value is provided, the index to that array becomes the name of the related object for the Division
model as noted above in the comments for parent_of.

### class

refers to the name of the actual class to instantiate, simple enough. Krak will run code
like so
````php
// $class refers to the string provided for the class value
$rel_obj = new $class;
````
**Note:** the index of relationship array will only be the name of the related_object
*if and only if* the class paramater is given. Let's take a look at the following code.

````php
protected $parent_of = array(
    'Rider\Video'   => array(
        'this_column'   => 'division_id'    // defaults to $this->model . '_' . $this->primary_key
    )
);
````

The `class` value was not provided, so `class` will equal the index of the array, and the
index of the array will then be `strtolower(str_replace('\\', '_', $index))` and that new
index will become the related_object name to turn into this code.

````php
protected $parent_of = array(
    'rider_video'   => array(   // the index of the array will default to the class name strtolower(str_replace('\\', '_', $class_name))
        'class'         => 'Rider\Video',
        'this_column'   => 'division_id'    // defaults to $this->model . '_' . $this->primary_key
    )
);
````

This property about class holds for all of the other different relationship types: parent_of, child_of, buddy_of.

### this_column

this_column refers to the name of the foriegn key column name in the child table. Only the child
tables hold the ITFK (in-table-foreign-key) for the relationship by definition of what a child table
is. So the child model will be referring to the parent models column name which defaults to `$this->model . '_' . $this->primary_key`
where `$this` refers to the parent table.