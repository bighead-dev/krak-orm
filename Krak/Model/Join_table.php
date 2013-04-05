<?php
namespace Krak\Model;

class Join_table extends \Krak\Model
{
	public function __construct($table)
	{
		$this->model = '';
		$this->table = $table;
		
		// implement the rest of the join tables
	}
}