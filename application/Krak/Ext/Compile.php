<?php

namespace Krak\Ext;

class Compile
{
	const PREFIX = 'Krak\Ext\Compile_';

	public function compile_fields(&$krak)
	{		
		$bundle = $krak->get_krak_bundle();
		$prefix = self::PREFIX;
		
		$file = \Krak\model_autoloader($bundle->class_name, true);
		
		$data = file_get_contents($file);
		
		// get rid of the old fields
		$old_expr = "@\s+//{$prefix}compile_fields_start[\s\S]+//{$prefix}compile_fields_end@m";
		$data = preg_replace($old_expr, '', $data);
		
		$var_exp = var_export($krak->fields(), true);
		
		// clean up the var exp
		$var_exp = preg_replace('/  (\d+)/', "\t\t$1", $var_exp);
		$var_exp = substr_replace($var_exp, "\t)", -1);
		
		$replace = <<<repl
\$1{
	//{$prefix}compile_fields_start
	public static \$fields = {$var_exp};
	//{$prefix}compile_fields_end

	\$3
repl;

		$expr = '/(.*class.+extends.+Krak\\\Model\s+)({\s+)(.+)/m';
		$new_data = preg_replace($expr, $replace, $data);
		
		file_put_contents($file, $new_data);
	}
}
