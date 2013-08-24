<?php
namespace Krak;

require_once 'Loader.php';

defined('Krak\VENDOR_PATH') || define('Krak\VENDOR_PATH', './application/third_party/Krak/');
defined('Krak\USER_PATH') || define('Krak\USER_PATH', './application/Krak/');

// create & register loader for Krak
$kloader = new Loader('Krak', VENDOR_PATH);
$kloader->register();

// this file contains all of the "function pointers" for the iterator creation
$kloader->load('Krak\Iterator\Create');

$mloader = require USER_PATH . 'Autoload.php';

if ($mloader instanceof iLoader == false)
{
	throw new Exception(get_class($mloader) . " returned from " . USER_PATH . "Autoload.php is not an instance of Krak\iLoader.");
}

// make sure this constant was defined
defined('Krak\USE_NS_AS_PREFIX') || define('Krak\USE_NS_AS_PREFIX', false);

// register the loader
$mloader->register();

$mpackage = $mloader->get_package();

define('Krak\MODEL_NS_LEN', ($mpackage && !USE_NS_AS_PREFIX) ? strlen($mpackage) + 1 : 0);

// now let's initialize Krak
Model::init();
