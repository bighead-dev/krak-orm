<?php

namespace Km\Order;

class Status extends \Krak\Model
{
	const OPEN			= 1;
	const INVOICE		= 2;
	const PAID			= 3;
	
	protected $table	= 'order_statuses';
	
	public function id_to_name($id)
	{
		$id = ($id) ?: $this->id;
		
		switch ($id)
		{
			case self::OPEN:
				return 'Open';
			case self::INVOICE:
				return 'Invoiced';
			case self::PAID:
				return 'Paid';
		}
	}
	
	public function slug_to_id($slug)
	{
		$slug = ($slug) ?: $this->slug;
		
		switch ($slug)
		{
			case 'open':
				return self::OPEN;
			case 'invoice':
				return self::INVOICE;
			case 'paid':
				return self::PAID;
		}
		
		return '';
	}
}
