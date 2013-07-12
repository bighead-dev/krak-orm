<?php
namespace Krak\Iterator;

/**
 * Default Krak Iterator
 *
 * This iterator use \Krak\Result for the result objects. This
 * is pretty much a simple wrapper for the \ArrayIterator class,
 * it just sets the Krak uid to each of the result objects
 *
 * @author RJ Garcia <rj@bighead.net>
 * @package Krak
 */
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

/**
 * The Buffered Iterator Create function.
 *
 * Every iterator has a create function that takes a reference
 * to the main Krak model and returns the ArrayIterator.
 *
 * @author RJ Garcia <rj@bighead.net>
 * @package Krak
 */
function buffered_create(&$krak)
{
	return new Buffered($krak->result(), $krak->get_uid());
}
