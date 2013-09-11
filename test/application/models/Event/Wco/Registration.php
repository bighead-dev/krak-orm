<?php

namespace Km\Event\Wco;

class Registration extends \Krak\Model
{
	protected $created_field	= 'created_at';
	protected $updated_field	= 'updated_at';
	
	protected $buddy_of = array(
		'Division'
	);
}
