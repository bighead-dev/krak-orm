<?php

namespace Krak\Ext;

class Compile
{
	const PREFIX = 'Krak\Ext\Compile_';

	public function compile_data($krak)
	{		
		$bundle = $krak->bundle();
		$prefix = preg_quote(self::PREFIX . 'compile_data_', '@');
		
		$file = \Krak\Model::$model_loader->load($bundle->class_name, true);
		
		$data = file_get_contents($file);

		// get rid of the old fields
		$old_expr = "@\s+// {$prefix}start[\s\S]+// {$prefix}end@m";
		$data = preg_replace($old_expr, '', $data);

		$field_var_exp = var_export($krak->fields(), true);
		
		// clean up the var exp
		$field_var_exp = preg_replace('/  (\d+)/', "\t\t$1", $field_var_exp);
		$field_var_exp = substr_replace($field_var_exp, "\t)", -1);
		
		$bndl_var_exp = var_export(get_object_vars($krak->bundle()), true);
		
		// clean up the bndl exp
		$bndl_var_exp = preg_replace('/^  /m', "\t\t", $bndl_var_exp);
		$bndl_var_exp = preg_replace("/=>\s+\n\s+array/", "=> array", $bndl_var_exp);
		$bndl_var_exp = str_replace('  ', "\t", $bndl_var_exp);
		$bndl_var_exp = substr_replace($bndl_var_exp, "\t)", -1);
		
		$replace = <<<repl
$1{
	// {$prefix}start
	public static \$fields = {$field_var_exp};
	
	public static \$bundle = {$bndl_var_exp};
	// {$prefix}end

	\$3
repl;

		$expr = '/(.*class.+extends.+Krak\\\Model\s+)({\s+)(.+)/m';
		$new_data = preg_replace($expr, $replace, $data);
		
		file_put_contents($file, $new_data);
	}
}
