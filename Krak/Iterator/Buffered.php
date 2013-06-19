<?php
namespace Krak\Iterator;

class Buffered extends \ArrayIterator
{
	public function __construct($objects, $uid)
	{
		foreach ($objects as &$obj)
		{
			$obj->set_uid($uid);
		}
		
		parent::__construct($objects);
	}
}

function buffered_create(&$krak)
{
	return new Buffered($krak->result(), $krak->get_uid());
}
