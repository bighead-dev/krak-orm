<?php
namespace Krak\Iterator;

class Buffered extends \ArrayIterator
{
	public function __construct($objects, &$krak)
	{
		foreach ($objects as &$obj)
		{
			$obj->set_krak($krak);
		}
		
		parent::__construct($objects);
	}
}
