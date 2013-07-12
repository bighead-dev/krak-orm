<?php
namespace Krak;

/**
 * Krak exception wrapper class
 *
 * @author RJ Garcia
 * @package Krak
 */
class Exception extends \Exception
{
	public function __construct($message)
	{
		parent::__construct($message);
	}
}
