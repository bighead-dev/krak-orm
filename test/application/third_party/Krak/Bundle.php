<?php
namespace Krak;

/**
 * A simple data class to allow access to protected/private Krak member variables.
 *
 * @author RJ Garcia <rj@bighead.net>
 * @package Krak
 */
class Bundle implements \ArrayAccess
{
	public $class_name;
	public $model;
	public $table;
	public $primary_key;
	public $created_field;
	public $updated_field;
	public $parent_of;
	public $child_of;
	public $buddy_of;
	public $event_queues;

	public function __construct($data)
	{		
		$this->class_name		= &$data['class_name'];
		$this->model			= &$data['model'];
		$this->table			= &$data['table'];
		$this->primary_key		= &$data['primary_key'];
		$this->created_field	= &$data['created_field'];
		$this->updated_field	= &$data['updated_field'];
		$this->parent_of		= &$data['parent_of'];
		$this->child_of			= &$data['child_of'];
		$this->buddy_of			= &$data['buddy_of'];
		$this->event_queues		= &$data['event_queues'];
	}
	
	/* Array Access Methods */
	
	public function offsetExists($index)
	{
		return property_exists($this, $index);
	}
	
	public function offsetGet($index)
	{
		return $this->{$index};
	}
	
	public function offsetSet($index, $value)
	{
		$this->{$index} = $value;
	}
	
	public function offsetUnset($index)
	{
		unset($this->{$index});
	}
}
