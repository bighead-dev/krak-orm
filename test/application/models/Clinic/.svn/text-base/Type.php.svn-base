<?php
namespace Km\Clinic;

/**
 * Clinic Type model
 *
 * This model isn't actually used for CRUD,
 * it's more just used for it's constants and slug_to_id
 * func.
 *
 * @author RJ Garcia <rj@bighead.net>
 * @package Ewt Model
 */
class Type extends \Krak\Model
{
	protected $created_field	= 'created_at';
	protected $updated_field	= 'updated_at';
	
	const BASIC		= 1;
	const _PRIVATE	= 2;	// prefix with underscore to make it valid php
	
	public function slug_to_id($slug = null)
	{
		$slug = ($slug) ?: $this->slug;

		switch ($slug)
		{
			case 'basic':
				return self::BASIC;
			case 'private':
				return self::_PRIVATE;
		}
		
		return 0;
	}
}
