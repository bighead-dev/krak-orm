<?php
namespace Krak;

class Result
{
	protected $_krak_uid;
	
	public function __call($name, $args = array())
	{
		static $k = NULL;
		
		/*
		 * If user runs a get request, then we need to create new Krak obj
		 */
		if ($name == 'get' || $name == 'get_where' || $name == 'query')
		{
			// do nothing
			return;
		}
		
		if ($k == NULL)
		{
			$k = Model::get_instance($this->_krak_uid);
		}

		$cache_krak = array();
		$this->store_krak_state($k, $cache_krak);
		
		$ret = call_user_func(array($k, $name), $args);
		
		$this->restore_krak_state($k, $cache_krak);
		
		if ($ret === $k)
		{
			return $this;
		}
		else
		{
			return $ret;
		}
	}
	
	public function set_uid($uid)
	{
		$this->_krak_uid = $uid;
	}
	
	private function store_krak_state(&$k, &$cache_krak)
	{
		// store and set the values of krak to this result object
		
		foreach ($k->fields as $field)
		{
			if (property_exists($this, $field))
			{
				if (property_exists($k, $field))
				{
					$cache_krak[$field] = $k->{$field};
					$k->{$field} = &$this->{$field};
				}
				else
				{
					// To assign by reference, we need an actual variable to assign to.
					$k->{$field} = $this->{$field};
				}
			}
		}
	}
	
	private function restore_krak_state(&$k, &$cache_krak)
	{
		// restore the krak values
		
		foreach ($k->fields as $field)
		{
			unset($k->{$field});
			
			if (array_key_exists($field, $cache_krak))
			{
				$k->{$field} = $cache_krak[$field];
			}
		}
	}
}
