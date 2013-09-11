<?php
namespace Krak\Iterator;

/**
 * Define all of the iterator create functions here
 */
 
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

/**
 * Simple Iterator Create method
 *
 * This is the simplest buffered iterator. It just
 * instantiates an ArrayIterator and returns it. The
 * result objects are just instances of stdClass. No
 * extra processing is done to the result set.
 *
 * @author RJ Garcia <rj@bighead.net>
 * @package Krak
 */
function simple_create(&$krak)
{
	return new \ArrayIterator($krak->result('stdClass'));
}
