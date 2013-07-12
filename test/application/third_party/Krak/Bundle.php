<?php
namespace Krak;

/**
 * A simple data class to allow access to protected/private Krak member variables.
 *
 * @author RJ Garcia <rj@bighead.net>
 * @package Krak
 */
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
