<?php
namespace Km\Clinic;

/**
 * Clinic Registration model
 *
 * Handles all of the clinic registration CRUD actions,
 * is an Api model.
 *
 * @author RJ Garcia <rj@bighead.net>
 * @package Ewt Model
 */
class Registration extends \Krak\Model\Api
{
	protected $created_field	= 'created_at';
	protected $updated_field	= 'updated_at';
	
	protected $child_of = array(
		'Clinic\Instructor', 'Clinic\Type'
	);
	
	public static $valid_fields	= array(
		'id'						=> 'clinic_registrations.id',
		'is_skim'					=> 'clinic_registrations.is_skim',
		'clinic_type'				=> 'clinic_registrations.clinic_type_id',
		'skill_level'				=> 'clinic_registrations.skill_level',
		
		'clinic_instructor.id'		=> 'clinic_registrations.clinic_instructor_id',
		'clinic_instructor.name'	=> array(
			'clinic_instructors.name as clinic_instructor_name',
			'clinic_instructor'
		),
		
		'event.id'					=> 'clinic_registrations.event_id',
		'event.name'				=> array(
			'events.name as event_name',
			'event'
		),
		
		'rider.id'					=> 'clinic_registrations.rider_id',
		'rider.name'				=> array(
			'riders.first_name as rider_first_name, riders.last_name as rider_last_name',
			'rider'
		),
		'rider.email'				=> array(
			'riders.email as rider_email',
			'rider'
		),
		'rider.gender'				=> array(
			'riders.gender as rider_gender',
			'rider'
		),
		'rider.age'					=> array(
			'riders.birth_date as rider_birth_date',
			'rider'
		),
		'rider.location'			=> array(
			'riders.city as rider_city, riders.state as rider_state',
			'rider'
		),
		'order.id'				=> array(
			'orders.id as order_id',
			'order'
		),
		'order.order_status'	=> array(
			'orders.order_status_id',
			'order'
		),
	);
	public static $valid_wheres	= array(
		'is_skim'		=> 'clinic_registrations.is_skim',
		'clinic_type'	=> 'clinic_registrations.clinic_type_id',
		'event'			=> 'clinic_registrations.event_id',
	);
	
	public static $valid_sorts	= array();
	
	public function api_process_where($key, &$data)
	{
		static $clinic_t_m	= null;
		static $event_m		= null;
		
		switch ($key)
		{
			case 'is_skim':
				$data = (bool) ($data);
				break;
			case 'clinic_type':
				$clinic_t_m = ($clinic_t_m) ?: new \Km\Clinic\Type();
				$data = $clinic_t_m->slug_to_id($data);
				break;
			case 'event':
				$event_m = ($event_m) ?: new \Km\Event();
				$data = $event_m->slug_to_id($data);
				break;
		}
	}
	
	public function api_build_join($joins, $j_type)
	{
		if (array_key_exists('event', $joins))
		{
			$this->db->join('events', 'events.id = clinic_registrations.event_id', $j_type, false);
		}
		
		if (array_key_exists('rider', $joins))
		{
			$this->db->join('riders', 'riders.id = clinic_registrations.rider_id', $j_type, false);
		}
		
		if (array_key_exists('order', $joins))
		{
			$this->db->join('orders', 'event_id, rider_id', $j_type, false);	// this join will use 'USING' instead of 'ON'
		}
		
		if (array_key_exists('clinic_instructor', $joins))
		{
			$this->db->join('clinic_instructors', 'clinic_instructors.id = clinic_registrations.clinic_instructor_id', $j_type, false);
		}
	}
}
