<?php
namespace Krak\Iterator;

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
