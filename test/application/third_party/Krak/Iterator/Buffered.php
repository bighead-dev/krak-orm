<?php
namespace Krak\Iterator;

defined('BASEPATH') || exit('No direct script access allowed');

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