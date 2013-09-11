<?php

namespace Km;

/**
 * Event model
 *
 * This model holds a lot constants and helper functions to help reduce
 * queries to the database. So always make sure the constants in this
 * class properly reflect the database values.
 *
 * Open events are defined here.
 *
 * @author RJ Garcia <rj@bighead.net>
 * @package Ewt Model
 */
class Event extends \Krak\Model
{
	// Krak\Ext\Compile_compile_data_start
	public static $fields = array (
		0 => 'id',
		1 => 'name',
		2 => 'slug',
		3 => 'year',
		4 => 'start',
		5 => 'end',
		6 => 'gmap_coords',
		7 => 'city',
		8 => 'state',
		9 => 'country_id',
		10 => 'is_tour',
		11 => 'event_pp_merchant_id',
		12 => 'created_at',
		13 => 'updated_at',
	);
	
	public static $bundle = array (
		'class_name' => 'Km\Event',
		'model' => 'event',
		'table' => 'events',
		'primary_key' => 'id',
		'created_field' => 'created_at',
		'updated_field' => 'updated_at',
		'parent_of' => array (
			'event_registration' => array (
				'class' => 'Km\Event\Registration',
				'join_clause' => '',
			),
		),
		'child_of' => array (
			'country' => array (
				'class' => 'Km\Country',
				'join_clause' => '',
			),
		),
		'buddy_of' => array (
			'rider' => array (
				'class' => 'Km\Rider',
				'join_clause' => array (
					0 => '',
					1 => '',
				),
			),
		),
		'event_queues' => array (
		),
	);
	// Krak\Ext\Compile_compile_data_end

	const EWT_2013	= 0;
	const WCO_2013	= 1;
	const WWS_2013	= 2;
	const BSB_2013	= 4;
	const TSS_2013	= 5;

	public static $open_events = array(
		self::BSB_2013,
		self::TSS_2013,
	);

	protected $created_field	= 'created_at';
	protected $updated_field	= 'updated_at';
	
	protected $parent_of = array(
		'Km\Event\Registration'
	);
	
	protected $child_of = array(
		'Km\Country'
	);
	
	protected $buddy_of = array(
		'Km\Rider'
	);
	
	public function slug_to_id($slug = null)
	{
		$slug = ($slug) ?: $this->slug . '_' . $this->year;
		switch ($slug)
		{
			case 'ewt_2013':
				return self::EWT_2013;
			case 'wco_2013':
				return self::WCO_2013;
			case 'wws_2013':
				return self::WWS_2013;
			case 'bsb_2013':
				return self::BSB_2013;
			case 'tss_2013':
				return self::TSS_2013;
		}
		
		return -1;
	}
	
	public function id_to_slug($id = null)
	{
		$id = ($id) ?: $this->id;
		switch ($id)
		{
			case self::EWT_2013:
				return 'ewt_2013';
			case self::WCO_2013:
				return 'wco_2013';
			case self::WWS_2013:
				return 'wws_2013';
			case self::BSB_2013:
				return 'bsb_2013';
			case self::TSS_2013:
				return 'tss_2013';
		}
		
		return '';
	}
	
	public function is_open($id = null)
	{
		$id = ($id) ?: $this->id;
		
		return in_array($id, self::$open_events);
	}
}
