<?php

namespace Km;

/**
 * 
 *
 * @author RJ Garcia <rj@bighead.net>
 * @package Ewt Model
 */
class Rider extends \Krak\Model
{
	protected $created_field	= 'created_at';
	protected $updated_field	= 'updated_at';
	
	protected $child_of = array(
		'Country'
	);
	
	/*
	 * Every key in this array corresponds to the interface the api.
	 * Every value can either be an array or a string. If an array, the first entry
	 * is what key actually maps to in our database. Every entry after that is what table Model/table
	 * that needs to be joined.
	 * If a string, then it acts as the first entry in the array (the value the key maps to in the database).
	 */
	public static $valid_fields	= array(
		'id'		=> 'riders.id',
		'name'		=> 'first_name, last_name',
		'location'	=> 'city, state',
		'country'	=> array(
			'countries.name as country',
			'country'
		),
		'username'	=> 'username',
		'gender'	=> 'gender',
		'has_image'	=> array(
			'IF(avatars.id IS NULL, 0, 1) as has_image',
			'avatar'
		),
	);
	
	public static $valid_wheres	= [
		'id'			=> 'riders.id',
		'country_id'	=> 'riders.country_id',
		'gender'		=> 'gender',
		'has_image'		=> [
			'IF(avatars.id IS NULL, 0, 1)',
			'avatar'
		]
	];
	
	public static $valid_sorts	= [
		'last_name'		=> 'riders.last_name',
		'first_name'	=> 'riders.first_name',
		'gender'		=> 'riders.gender',
		'has_image'		=> [
			'IF(avatars.id IS NULL, 0, 1)',
			'avatar'
		]
	];
	
	public function _before_save()
	{
		if (isset($this->password))
		{
			$this->salt		= md5(uniqid(rand(), true));
			$this->password	= sha1($this->salt . $this->password);
		}
	}
	
	public function _before_update()
	{
		if (isset($this->password))
		{
			$this->salt		= md5(uniqid(rand(), true));
			$this->password	= sha1($this->salt . $this->password);
		}
	}
	
	public function get_profile()
	{
		$this->select('riders.*, countries.name as country_name, avatars.id as avatar_id')
			->join('countries', 'riders.country_id = countries.id', 'left')
			->join('avatars', 'riders.id = avatars.rider_id', 'left')
			->get();
			
		return $this;
	}
	
	public function api_process_where($key, &$data)
	{	
		switch ($key)
		{
			case 'country_id':
			case 'id':
			case 'has_image':
				$data = intval($data);
				break;
			default:
				$data = $this->db->escape($data);
				break;
		}
	}
	
	public function api_build_join($joins, $j_type)
	{	
		$group_by_rider = false;

		if (array_key_exists('country', $joins))
		{
			$this->db->join('countries', 'countries.id = riders.country_id', $j_type, false);
		}
		
		if (array_key_exists('avatar', $joins))
		{
			$this->db->join('avatars', 'riders.id = avatars.rider_id', $j_type, false);
			$group_by_rider = true;
		}
		
		if ($group_by_rider)
		{
			$this->db->group_by('riders.id', false);
		}
	}
}
