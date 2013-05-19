<?php
namespace Krak;

/*
 * The base directory to run the autoloading from.
 * Normally it's: APPPATH . 'models/'
 */
const BASE_DIR = './application/models/';

/*
 * Set this variable to whatever namespace your models are located in
 * Leave empty if in the global namespace
 */
const MODEL_NS = '';

function model_autoloader($class, $ret = false)
{
	$file  = '';
	$namespace = '';

	if (MODEL_NS != '')
	{
		// let's make sure we are in the right namespace
		if (strpos($class, MODEL_NS) !== 0)
		{
			return;
		}
	}
	
	if ($last_ns_pos = strrpos($class, '\\'))
	{
		$namespace = substr($class, 0, $last_ns_pos);
		$class = substr($class, $last_ns_pos + 1);
		$file  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
	}
	// don't convert _'s to directory separators, let's let users have underscores in
	// their model names

	$path = BASE_DIR . $file . $class . '.php';

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

require_once 'Model.php';
require_once 'Result.php';
require_once 'Bundle.php';
require_once 'Exception.php';
require_once 'Iterator/Buffered.php';
require_once 'Model/Join_table.php';
