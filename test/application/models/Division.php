<?php

namespace Km;

/**
 * Division model
 *
 * This model holds simple helper functions to reduce querying, and is apart
 * of the API models. The divisions are related to event registrations AND not riders.
 *
 * @author RJ Garcia <rj@bighead.net>
 * @package Ewt Model
 */
class Division extends \Krak\Model
{
	protected $created_field	= 'created_at';
	protected $updated_field	= 'updated_at';
	
	protected $buddy_of = array(
		'Event\Wco\Registration'
	);
	
	public function is_professional()
	{
		return (strpos($this->slug, 'open') === 0);
	}
	
	public function api_build_join_with_event_registration($joins, $j_type)
	{
		$jt = $this->get_jt('event_registrations');
		$this->db->join($jt, "divisions.id = {$jt}.division_id", null, false);
		$this->db->join('event_registrations', "{$jt}.event_registration_id = event_registrations.id", 'left', false);
		
		/*if (array_key_exists('event', $joins))
		{
			$this->db->join('events', 'events.id = event_registrations.event_id', $j_type, false);
		}
		
		if (array_key_exists('rider', $joins))
		{
			$this->db->join('riders', 'riders.id = event_registrations.rider_id', $j_type, false);
		}
		
		if (array_key_exists('primary_division', $joins))
		{
			$this->db->join('divisions as primary_division', 'primary_division.id = event_registrations.primary_division_id', $j_type, false);
		}*/
	}
}
