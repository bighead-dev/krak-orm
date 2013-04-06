<?php
namespace Krak;

class Exception extends \Exception
{
	public function __construct($message)
	{
		$string = <<<error_message

# Krakception

message: $message

error_message;
		parent::__construct($string);
	}
}