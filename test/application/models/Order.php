<?php

namespace Km;

/**
 * Order model
 *
 * This is an api model. Orders are tied with a 
 * rider and event. Only one order should ever be created for an event.
 * See [\Api\Order](/api-docs/classes/Api.Order.html) for more information.
 *
 * @author RJ Garcia <rj@bighead.net>
 * @package Ewt Model
 */
class Order extends \Krak\Model\Api
{
	protected $created_field	= 'created_at';
	protected $updated_field	= 'updated_at';
	
	public static $valid_fields	= array(
		'id'			=> 'orders.id',
		'order_status'	=> 'orders.order_status_id',
		'rider.id'		=> 'orders.rider_id',
		'rider.name'	=> array(
			'riders.first_name, riders.last_name',
			'rider'
		),
		'event.id'		=> 'orders.event_id',
		'event.name'	=> array(
			'events.name as event_name',
			'event'
		),
		'item.name'	=> array(
			'order_goods.name',
			'order_item',
			'order_good'
		),
		'item.sku'	=> array(
			'order_goods.sku',
			'order_item',
			'order_good'
		),
		'item.price'	=> array(
			'order_goods.price',
			'order_item',
			'order_good'
		),
		'item.quantity'	=> array(
			'order_items.quantity',
			'order_item',
		),
	);
	
	public static $valid_wheres	= array(
		'rider_id'		=> 'orders.rider_id',
		'event'			=> 'orders.event_id',
		'order_status'	=> 'orders.order_status_id'
	);
	
	protected function api_process_where($key, &$data)
	{
		static $event_m = null;
		static $status_m = null;
		
		if ($event_m == null)
		{
			$event_m = new Event();
			$status_m = new Order\Status();
		}
		
		switch ($key)
		{
			case 'rider_id':
				$data = intval($data);
				break;
			case 'event':
				$data = $event_m->slug_to_id($data);
				break;
			case 'order_status':
				$data = $status_m->slug_to_id($data);
				break;
		}
	}
	
	public function api_build_join($joins, $j_type)
	{
		if (array_key_exists('order_item', $joins))
		{
			$this->db->join('order_items', 'order_items.order_id = orders.id', $j_type, false);
		}
		
		if (array_key_exists('order_good', $joins))
		{
			$this->db->join('order_goods', 'order_items.order_good_id = order_goods.id', $j_type, false);
		}
		
		if (array_key_exists('event', $joins))
		{
			$this->db->join('events', 'orders.event_id = events.id', $j_type, false);
		}
		
		if (array_key_exists('rider', $joins))
		{
			$this->db->join('riders', 'riders.id = orders.rider_id', $j_type, false);
		}
	}
}
