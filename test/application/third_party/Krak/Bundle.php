<?php
namespace Krak;

defined('BASEPATH') || exit('No direct script access allowed');

class Bundle
{
	public $table;
	public $model;
	public $has_one;
	public $has_many;
	public $primary_key;
	public $created_field;
	public $updated_field;
}