<?php
namespace Krak;

class Result implements \IteratorAggregate, \ArrayAccess, \Countable
{
	protected $krak;
	protected $is_ref = TRUE;

	public function __get($name)
	{
		$cache_krak = array();
		
		if ($name != 'fields' && $this->is_ref == TRUE)
		{
			$this->krak = clone $this->krak;
			
			// we need to remove krak as a reference, and make it a complete instance
			$tmp = $this->krak;
			unset($this->krak);
			$this->krak = clone $tmp;
			$this->is_ref = FALSE;
			
			$this->store_krak_state($cache_krak);
		}
				
		$ret_val = $this->krak->__get($name);
		
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
		
		/*
		 * If user runs a get request, then we need to create new Krak obj
		 */
		if ($this->is_ref == TRUE && ($name == 'get' || $name == 'get_where' || $name == 'query'))
		{
			$this->krak = clone $this->krak;
			
			// we need to remove krak as a reference, and make it a complete instance
			$tmp = $this->krak;
			unset($this->krak);
			$this->krak = clone $tmp;
			$this->is_ref = FALSE;
			
			$this->store_krak_state($cache_krak);
		}
		
		if ($this->is_ref)
		{	
			$this->store_krak_state($cache_krak);
		}
		
		$ret_val = call_user_func(array($this->krak, $name), $args);
		
		if ($this->is_ref)
		{
			$this->restore_krak_state($cache_krak);
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
	
	public function get_krak($clone = TRUE)
	{
		$krak = $this->krak;
		if ($clone == TRUE && $this->is_ref == TRUE)
		{
			$tmp = $krak;
			$krak = clone $tmp;
		}
		
		return $krak;
	}
	
	private function store_krak_state(&$cache_krak)
	{
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
	}
	
	private function restore_krak_state(&$cache_krak)
	{
		// restore the krak values
		
		foreach ($this->krak->fields as $field)
		{
			unset($this->krak->{$field});
			
			if (array_key_exists($field, $cache_krak))
			{
				$this->krak->{$field} = $cache_krak[$field];
			}
		}
	}
	
	/* I T E R A T O R  A G G R E G A T E   M E T H O D S */
	
	public function getIterator()
	{
		if ($this->is_ref == FALSE)
		{
			return $this->krak->getIterator();
		}
			
		return new ArrayIterator();
	}
	
	/* Array Access Methods */
	
	public function offsetExists($index)
	{
		if ($this->is_ref == FALSE)
		{
			return $this->krak->offsetExists($index);
		}
		
		return FALSE;
	}
	
	public function offsetGet($index)
	{
		if ($this->is_ref == FALSE)
		{
			return $this->krak->offsetGet($index);
		}
		
		return NULL;
	}
	
	public function offsetSet($index, $value)
	{
		if ($this->is_ref == FALSE)
		{
			$this->krak->offsetSet($index, $value);
		}
	}
	
	public function offsetUnset($index)
	{
		if ($this->is_ref == FALSE)
		{
			return $this->krak->offsetUnset($index);
		}
	}
	
	/* Countable methods */
	
	public function count()
	{
		if ($this->is_ref == FALSE)
		{
			return $this->krak->count();
		}
			
		return 0;
	}
}
