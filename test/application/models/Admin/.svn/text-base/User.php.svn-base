<?php
namespace Km\Admin;

/**
 * Model for administrative users
 *
 * Administrative users are pretty much riders.
 *
 * @author RJ Garcia <rj@bighead.net>
 * @package Ewt Model
 */
class User extends \Krak\Model
{
	protected $created_field	= 'created_at';
	protected $updated_field	= 'updated_at';
	
	const SUPER_ADMIN = 9;
	const ENTRY_ADMIN = 0;
	
	protected $child_of = array(
		'Rider'
	);
}
