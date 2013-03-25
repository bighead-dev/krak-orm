<?php
namespace Krak;

defined('BASEPATH') || exit('No direct script access allowed');

class Result
{
	protected $krak;
	protected $primary_key;
	
	public function __construct(&$krak)
	{
		$this->krak = &$krak;
	}

	public function save()
	{
		
	}

	public function update()
	{
	
	}

	public function delete()
	{
	
	}
}