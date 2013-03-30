Krak
====

Simple ORM for CI 3.0

# TODO

- Finish implementing latest rendition of Krak\Model
	- finish related getting,saving,deleting
- Create Result class

# Sample World

Let's come up with a sample database world to use in the coming examples. Our database has the following
entities

- Riders
- Rider\Videos (riders have their own videos not related to the main videos table)
- Divisions (each rider can be apart of many divisions
- Sports

with the following relationships

````
Riders			-> many	-> Rider\Videos
Riders			-> many	-> Divisions
Rider\Videos	-> one	-> Riders
Rider\Videos	-> one	-> Divisions
Divisions		-> many	-> Riders
Divisions		-> many	-> Videos
Divisions		-> one	-> Sports
Sport			-> many	-> Divisions
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

# Advanced Relationships

Krak also supports advanced relationships in its models also. Krak can support recursive relationships,
multiple relationships to the same model. It also can allow customization of these relationships
are named in the table.

