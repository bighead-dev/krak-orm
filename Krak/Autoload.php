<?php
namespace Krak;

require_once BASEPATH . 'helpers/inflector_helper.php';
require_once 'Exception.php';
require_once 'Model.php';
require_once 'Result.php';
require_once 'Bundle.php';
require_once 'Iterator/Buffered.php';
require_once 'Iterator/Simple.php';
require_once 'Model/Join_table.php';

defined('Krak\USER_PATH') || define('Krak\USER_PATH', './application/Krak/');

require_once USER_PATH . 'Autoload.php';
require_once USER_PATH . 'Constants.php';

define('Krak\MODEL_NS_LEN', strlen(MODEL_NS . '\\'));

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
		
		$class = substr($class, MODEL_NS_LEN);
		
		if ($last_ns_pos = strrpos($class, '\\'))
		{
			$namespace = substr($class, 0, $last_ns_pos);
			$class = substr($class, $last_ns_pos + 1);
			$file  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		// don't convert _'s to directory separators, let's let users have underscores in
		// their model names

		$path = BASE_DIR . $file . $class . '.php';

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
}

/*** register the loader functions ***/
spl_autoload_register('\Krak\model_autoloader');
