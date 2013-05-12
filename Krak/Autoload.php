<?php
namespace Krak;

require_once 'Model.php';
require_once 'Result.php';
require_once 'Bundle.php';
require_once 'Exception.php';
require_once 'Iterator/Buffered.php';
require_once 'Model/Join_table.php';

function model_autoloader($class, $ret = false)
{
	$file  = '';
	$namespace = '';
	
	if ($last_ns_pos = strrpos($class, '\\'))
	{
		$namespace = substr($class, 0, $last_ns_pos);
		$class = substr($class, $last_ns_pos + 1);
		$file  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
	}
	// don't convert _'s to directory separators, let's let users have underscores in
	// their model names

	$path = APPPATH . 'models/' . $file . $class . '.php';
	
	if (file_exists($path))
	{
		if ($ret == false)
		{
			require_once $path;
		}
		else
		{
			return $path;
		}
	}
	else
	{
		if ($ret == true)
		{
			return false;
		}
	}
}

/*** register the loader functions ***/
spl_autoload_register('\Krak\model_autoloader');
