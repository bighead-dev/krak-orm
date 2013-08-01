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
	private $data = array();
	
	public function __construct($data)
	{
		$this->data = &$data;
	}
	
	/* Array Access Methods */
	
	public function offsetExists($index)
	{
		return array_key_exists($this->data[$index]);
	}
	
	public function offsetGet($index)
	{
		return $this->data[$index];
	}
	
	public function offsetSet($index, $value)
	{
		$this->data[$index] = $value;
	}
	
	public function offsetUnset($index)
	{
		unset($this->data[$index]);
	}
}
