<?php
namespace Vegetable;

/*
 * The default values for krak
 */
class Broccoli extends \Krak\Model
{
	/*
	 * If no array is provided, then
	 * Krak will query the database to
	 * get the fields from the given table and
	 * populates the array at runtime. It's not
	 * an issue for development, but it's much better
	 * if you set these fields before you push your code
	 * live. If you have more than 10 fields for your database
	 * it's better if you insert your data like so
	 * e.g.
	 *	public static $fields = array(
	 *		'field_1'	=> NULL,
	 *		'field_2'	=> NULL,
	 *		'field_3'	=> NULL
	 *	);
	 *
	 * This allows us to array_key_exists which is
	 * O(1) where as in_array is O(n). if the keys aren't
	 * NULL then we always use array_flip to allow us to use
	 * array_key_exists, but it's a bit of overhead if you have
	 * a lot of fields
	 */
	public $fields = array(
		'field_1', 'field_2', 'field_3'
	);
	
	/*
	 * Just the name of the actual table to query from.
	 * Defaults to the plural version of $this->model
	 * according to the inflector helper. Don't assume
	 * the inflector helper will always get your table name
	 * right. If you have any doubt, just put the name in 
	 * here. It'll also save processing time in construction
	 */
	protected $table = 'vegetable_broccolis';
	
	/*
	 * The name of the primary key field for this model.
	 * This just defaults to id
	 */
	protected $primary_key = 'id';
	
	/*
	 * These two fields are for populating a timestamp
	 * on save and update. If these two fields are populated
	 * with the name of DateTime columns, then whenever
	 * a save occurs we'll write a current timestamp to
	 * both of these columns. If updating, then only
	 * the updated_field column will get set to the
	 * current timestamp.
	 * Adding these columns is basically allowing
	 * this code every save and update
	 *
	 *	$this->{$created_field} = date('Y-m-d H:i:s');
	 *	...
	 */
	 
	protected $created_field	= '';
	protected $updated_field	= '';
	
	/*
	 * Below are valid input types for the $this->parent_of
	 * array. $this->child_of and $this->buddy_of accept the same types
	 * of values. The array looks something like this
	 * 	array(
	 * 		[0]	=> 'User',
	 *		[1]	=> 'Client',
	 *		['Fruit\Orange]	=> array(...)
	 *		...
	 *	)
	 * If the value of the array is an array, then
	 * the key must be equal to the class name to
	 * instantiate. e.g. $buddy = new User();...
	 *
	 * Else, the value will be the name of the class
	 * to instantiate.
	 *
	 * parent_of means that this object is the 
	 * parent of other objects meaning, those other
	 * objects have a foreign key to this.
	 */
	
	protected $parent_of = array(
		'User', 'Client', 'Country', 'User\Video',
		'Fruit\Orange'	=> array(
			'this_column'	=> 'fruit_orange_id'
		),
		'alias_name'	=> array(
			'class'			=> 'User',
			'this_column'	=> 'other_user_id'
		)
	);
	
	protected $child_of = array(
		
	);
	
	protected $buddy_of = array();

	/*
	 * There are only 4 values that may be specified in a relationship
	 * 
	 * this_column	= the name of the column the buddy krak will refer
	 * to this as. e.g.
	 * $this = Vegetable\Broccoli
	 * users
	 * +----+-------------+------------+-----------------------+
	 * | id | data_field1 | datafield2 | vegetable_broccoli_id |
	 * +----+-------------+------------+-----------------------+
	 * etc...
	 *
	 * buddy_clumn	= the name of the column to refer to the buddy column
	 * as for this. Doesn't actually get populated to till a relational
	 * query is made. Unless specified otherwise
	 * e.g.
	 * $buddy = User
	 * vegetable_broccolis
	 * +----+-------------+-------------+---------+
	 * | id | data_field1 | data_field2 | user_id |
	 * +----+-------------+-------------+---------+
	 * etc...
	 *
	 * join_table	= the name of the join table for many to many
	 * relationships. This value only gets populated once a relational
	 * query is made (get_related, save_related...), but if left empty
	 * it will default to the name of both tables concatenated together
	 * by an '_'. The names will be concatenated in lexicographical order
	 *
	 * To access a buddy object from krak object, you use the model property
	 * of the buddy object.
	 * e.g.
	 * buddy = 'Vegetable\Carrot'
	 * buddy->model = 'vegetable_carrot'
	 *
	 * $broc = new Vegetable\Broccoli();
	 * $broc->vegetable_carrot->get()
	 *
	 * The values below show you what Krak will default everything
	 * to incase you don't specify.
	 */
	protected $has_many = array(
		'User'	=> array(
			'this_column'	=> 'vegetable_broccoli_id',		// $this->model . '_' . $this->primary_key
			'buddy_column'	=> 'user_id',					// $buddy->model . '_' . $buddy->primary_key
			'join_table'	=> 'users_vegetable_broccolis'	// ($buddy->table < $this->table) ? $buddy->table . '_' . $this->table : $this->table . '_' . $buddy->table;
		),
		'Vegetable\Carrot'	=> array(
			'this_column'	=> 'vegetable_broccoli_id',
			'buddy_column'	=> 'vegetable_carrot_id',
			'join_table'	=> 'vegetable_broccolis_vegetable_carrots'
		),
	);
	
	// recursive
	protected $child_of = array(
		'manager'	=> array(
			'class'			=> 'Vegetable\Broccoli',
			'parent_col'	=> 'manager_id'
		)
	);
	
	protected $parent_of = array(
		'manager'
	);
	
	$k = new this();
	$k->manager
}