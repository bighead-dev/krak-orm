<?php

class Rider extends Krak\Model
{
	protected $created_field = 'created_at';
	protected $updated_field = 'updated_at';

	protected $parent_of = array(
		'Rider\Video'
	);
	
	protected $buddy_of = array(
		'Division'
	);
}