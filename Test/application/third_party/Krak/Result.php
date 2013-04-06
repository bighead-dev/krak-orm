<?php
namespace Krak;

defined('BASEPATH') || exit('No direct script access allowed');

class Result
{
	protected $krak;

	public function __get($name)
	{
		$cache_krak = array();
		
		// store and set the values of krak to this result object
		
		foreach ($this->krak->fields as $field)
		{
			if (property_exists($this, $field))
			{
				if (property_exists($this->krak, $field))
				{
					$cache_krak[$field] = $this->krak->{$field};
					$this->krak->{$field} = &$this->{$field};
				}
				else
				{
					// To assign by reference, we need an actual variable to assign to.
					$this->krak->{$field} = $this->{$field};
				}
			}
		}
		
		$ret_val = $this->krak->__get($name);
		
		// restore the krak values
		
		foreach ($this->krak->fields as $field)
		{
			unset($this->krak->{$field});
			
			if (array_key_exists($field, $cache_krak))
			{
				$this->krak->{$field} = $cache_krak[$field];
			}
		}
		
		if ($ret_val === $this->krak)
		{
			return $this;
		}
		else
		{
			return $ret_val;
		}
	}
	
	public function __call($name, $args = array())
	{
		$cache_krak = array();
		
		// store and set the values of krak to this result object
		
		foreach ($this->krak->fields as $field)
		{
			if (property_exists($this, $field))
			{
				if (property_exists($this->krak, $field))
				{
					$cache_krak[$field] = $this->krak->{$field};
					$this->krak->{$field} = &$this->{$field};
				}
				else
				{
					// To assign by reference, we need an actual variable to assign to.
					$this->krak->{$field} = $this->{$field};
				}
			}
		}
		
		$ret_val = call_user_func(array($this->krak, $name), $args);
		
		// restore the krak values
		
		foreach ($this->krak->fields as $field)
		{
			unset($this->krak->{$field});
			
			if (array_key_exists($field, $cache_krak))
			{
				$this->krak->{$field} = $cache_krak[$field];
			}
		}
		
		if ($ret_val === $this->krak)
		{
			return $this;
		}
		else
		{
			return $ret_val;
		}
	}
	
	public function set_krak(&$krak)
	{
		$this->krak = &$krak;
	}
}