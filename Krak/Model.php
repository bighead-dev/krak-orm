<?php
namespace Krak;

defined('BASEPATH') || exit('No direct script access allowed');

require_once 'Result.php';
require_once 'Bundle.php';

abstract class Model implements IteratorAggregate, ArrayAccess, Countable
{
	// Event Constants
	const EVENT_BEFORE_SAVE		= '_before_save';
	const EVENT_AFTER_SAVE		= '_after_save';
	const EVENT_BEFORE_UPDATE	= '_before_update';
	const EVENT_AFTER_UPDATE	= '_after_update';
	const EVENT_BEFORE_DELETE	= '_before_delete';
	const EVENT_AFTER_DELETE	= '_after_delete';
	
	/*  S T A T I C   P R O P E R T I E S */
	
	public static $before_save_update = array(
		self::EVENT_BEFORE_SAVE,
		self::EVENT_BEFORE_UPDATE
	);
	
	public static $before_all = array(
		self::EVENT_BEFORE_SAVE,
		self::EVENT_BEFORE_UPDATE,
		self::EVENT_BEFORE_DELETE
	);
	
	public static $after_save_update = array(
		self::EVENT_AFTER_SAVE,
		self::EVENT_AFTER_UPDATE
	);
	
	public static $after_all = array(
		self::EVENT_AFTER_SAVE,
		self::EVENT_AFTER_UPDATE,
		self::EVENT_AFTER_DELETE
	);
	
	public static $before_after_all = array(
		self::EVENT_BEFORE_SAVE,
		self::EVENT_BEFORE_UPDATE,
		self::EVENT_BEFORE_DELETE,
		self::EVENT_AFTER_SAVE,
		self::EVENT_AFTER_UPDATE,
		self::EVENT_AFTER_DELETE
	);
	
	protected static $config = array(
		'extensions'	=> array()
	);
	
	protected static $extension_methods	= array();
	
	private static $has_init	= FALSE;
	//private static $aliases		= array();
	//private static $krak_fields	= array();

	/* I N S T A N C E   P R O P E R T I E S */
	public $fields = array();

	protected $table			= '';
	//protected $has_one			= array();
	//protected $has_many			= array();
	protected $parent_of		= array();
	protected $child_of			= array();
	protected $buddy_of			= array();
	protected $primary_key		= 'id';
	protected $created_field	= '';
	protected $updated_field	= '';
	
	protected $db;
	
	private $model				= '';
	private $event_queues		= array();
	private $is_related			= FALSE;
	private $buddy				= NULL;
	private $related_models		= array();
	private $class_name			= '';
	
	// iterator related variables
	private $iter			= NULL;
	private $first_row		= NULL;
	private $last_res		= NULL;
	
	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct($id = NULL)
	{
		$ci = &get_instance();
		$this->db = &$ci->db;
		
		if ( ! self::$has_init)
		{
			// Load configuration
			if ($ci->config->load('Krak', TRUE, TRUE))
			{
				self::$config = $ci->config->item('Krak');
			}
			
			$this->_load_extensions();
			
			self::$has_init = TRUE;
		}
		
		$ci->load->helper('inflector');
		
		// set up the default values
		$this->class_name = get_class($this);
		
		if ($this->model == '')
		{
			$this->model = str_replace('\\', '_', $this->class_name);
		}
		
		if ($this->table == '')
		{
			$this->table = plural($this->model);
		}
			
		// if user hasn't already supplied a fields array, then run the query
		if (count($this->fields) == 0)
		{
			$this->fields = $this->db->list_fields($this->table);
		}
		
		// Search for defined event functions to add in the event queue
		foreach (self::$before_after_all as $func)
		{
			if (method_exists($this, $func))
			{
				$this->add_event_listener(array($this, $func), $func, FALSE);
			}
		}
		
		// simple default values array

		// set parent_of
		foreach ($this->parent_of as $key => $class)
		{
			$default = array();
			$default['this_column']	= $this->model . '_id';
		
			if (is_array($class))
			{
				$default['class'] = $key; 
				$default = array_merge($default, $class);
			}
			else	// must be a string
			{
				$default['class'] = $class;
			}
			
			// unset the current value because we don't use it any more, free up memory
			unset($this->parent_of[$key]);
			$this->parent_of[str_replace('\\', '_', $default['class'])] = $default;
		}
		
		foreach ($this->child_of as $key => $class)
		{
			$default = array();
			$default['parent_column']	= $this->model . '_id';
		
			if (is_array($class))
			{
				$default['class'] = $key; 
				$default = array_merge($default, $class);
			}
			else	// must be a string
			{
				$default['class'] = $class;
			}
			
			// unset the current value because we don't use it any more, free up memory
			unset($this->parent_of[$key]);
			$this->parent_of[str_replace('\\', '_', $default['class'])] = $default;
		}
		
		$this->extend_related_objects($this->parent_of);
		$this->extend_related_objects($this->child_of);
		$this->extend_related_objects($this->buddy_of);
	
		if ($id !== NULL)
		{
			$this->db->where($this->primary_key, $id);
			$this->get();
		}
	}
	
	public function __destruct()
	{
		$this->free();
	}
	
	private function _load_extensions()
    {
        $path = APPPATH . 'krak/';

        foreach (self::$config['extensions'] as $extension)
        {
            // Path/file and class name
            $file = $path.$extension.'.php';
            $class = "Krak\\$extension";

            if (!file_exists($file))
            {
                show_error('Krak Error: loading extension ' . $extension . ': File not found.');
            }

            require_once $file;

            if (!class_exists($class))
            {
                show_error("Krak Error: Unable to find a class for extension $extension. Looked for class {$class}");
            }

            $obj = new $class();

			// look for event functions to put in the event queue
			foreach (self::$before_after_all as $func)
			{
				if (method_exists($obj, $func))
					$this->add_event_listener(array($obj, $func), $func, FALSE);
			}

            // Check which methods can be called on this class, and store in array (method_name => class_name)
            foreach (get_class_methods($class) as $method)
            {
            	// if two plugins use the same function name, then the latest plugin's function will override the first.
                if ($method[0] != '_' && is_callable(array($obj, $method)))
                {
                    self::$extension_methods[$method] = $obj;
                }
            }
        }
    }

	public function free()
	{
		if ($this->last_res !== NULL)
		{
			$this->last_res->free_result();
		}
	}

	public static function model_autoloader($class)
	{		
		$class = ltrim(substr($class, 4), '\\');
		$file  = '';
		$namespace = '';
		
		if ($last_ns_pos = strrpos($class, '\\'))
		{
			$namespace = substr($class, 0, $last_ns_pos);
			$class = substr($class, $last_ns_pos + 1);
			$file  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		// don't convert _'s to directory separators, let's let users have underscores in
		// their model names

		$path = APPPATH . 'models/' . strtolower($file . $class) . '.php';
		
		if (file_exists($path))
		{
			require_once $path;
		}
	}
	
	public function __get($name)
	{
		// priority levels
		// 1. krak_obj, user has to actually set $model->propety = 'value'
		// for the krak object property to exist. So if they set it and then ask
		// for it, then they better get what they set it to.
		//
		// 2. If user ran a get statment, then check there
		//
		// 4. User must be trying access a related model, so instantiate the property
		// then actually add to $this so that next time user asks for $this->related_model
		// they won't go through __get
		//
		// 5. 'all' is Dm specific items that are deprecated in Krak
		// so just support them here, but we'll eventually drop these
	
		if ($this->first_row !== NULL && property_exists($this->first_row, $name))
		{
			return $this->first_row->{$name};
		}
		else if (array_key_exists($name, $this->related_models))
		{
			return $this->related_models[$name];
		}
		else if (array_key_exists($name, self::$aliases))
		{
			if ($this->has_alias($name, TRUE))
			{
				$this->related_models[$name]	= 		$bud	= new $this->has_one[$name]['class'];
				$this->related_models[$name]->is_related		= TRUE;
				$this->related_models[$name]->buddy				= $this;			
			
				/* 
				 * we need to init the buddy_column and join_table values
				 * for the has_one array if the user didn't already specify
				 * one because know we have the object to use
				 */
				if ($this->has_one[$name]['buddy_column'] == '')
				{
					$this->has_one[$name]['buddy_column'] = $bud->model . '_' . $bud->primary_key;
				}
			
				if ($this->has_one[$name]['join_table'] == '')
				{
					$this->has_one[$name]['join_table'] = ($bud->table < $this->table) ? $bud->table . '_' . $this->table : $this->table . '_' . $bud->table;
				}
			
				// let's do some validation
				
				// if the bud has a reference to this, then this is parent
				if (in_array($this->has_one[$name]['this_column'], $bud::$fields))
				{
					$this->has_one[$name]['is_parent'] = TRUE;
				}
				else if (in_array($this->has_one[$name]['buddy_column'], self::$fields))
				{
					$this->has_one[$name]['is_parent'] = FALSE;
				}
				else
				{
					// whoops!
					$tmp_class_name = get_class($this);
					$tmp_bud_class_name = get_class($bud);
					$tmp_field_printr = print_r(self::$fields, TRUE);
					$tmp_bud_field_printr = print_r(self::$fields, TRUE);
					$error = <<<error_str
# Krak Error

Unable to locate In-Table-Foreign-Key for `{$tmp_class_name}` in
has_one relationship with `{$tmp_bud_class_name}`

\$this Class: {$tmp_class_name}
Buddy Class: {$tmp_bud_class_name}
{$tmp_class_name}::\$fields => {$tmp_field_printr}

{$tmp_bud_class_name}::\$fields => {$tmp_bud_field_printr}

\$this->has_one => 
error_str;
					show_error($error . print_r($this->has_one[$name], TRUE));
					die();
				}
			
				return $this->related_models[$name];
			}
			else if ($this->has_alias($name, FALSE))	// has_alias for has_many array
			{
				$this->related_models[$name] =	 		$bud	= new $this->has_many[$name]['class'];
				$this->related_models[$name]->is_related		= TRUE;
				$this->related_models[$name]->buddy				= $this;
			
				if ($this->has_many[$name]['buddy_column'] == '')
				{
					$this->has_many[$name]['buddy_column'] = $bud->model . '_' . $bud->primary_key;
				}
			
				if ($this->has_many[$name]['join_table'] == '')
				{
					$this->has_many[$name]['join_table'] = ($bud->table < $this->table) ? $bud->table . '_' . $this->table : $this->table . '_' . $bud->table;
				}
			
				// no way to validate
			
				return $this->related_models[$name];
			}
		}
		else if ($name == 'fields')
		{
			return self::$fields
		}
		else if ($name == 'all')
		{
			return $this->getIterator();
		}
		
		return NULL;
	}
	
	/**
	 * 
	 * @param unknown_type $method
	 * @param unknown_type $args
	 * @return Krak|mixed
	 */
	public function __call($method, $args)
	{
		// Check if we have an extension defined for this method (this allows us to seemlessly call $model->some_extension())
        if (isset(self::$extension_methods[$method]))
        {
        	$obj = self::$extension_methods[$method];
        	
            // First argument should be this instance of Krak, the remaining arguments are passed verbatim
            array_unshift($args, $this);

            $ret_val = call_user_func_array(array($obj, $method), $args);
            
            if ($ret_val === $obj)
            	return $this;
            else
            	return $ret_val;
        }
        
        $ret_val = call_user_func_array(array($this->db, $method), $args);
	
		// if db returned itself for method chaining then return $this also
		// else just return the result
		if ($ret_val === $this->db)
			return $this;
		else
			return $ret_val;
	}
	
	/**
	 * @description Overload the db->get() method and default with this models default table.
	 * @param	int	$limit
	 * @param	int	$offset
	 * @param	string	$table
	 */
	public function get($limit = NULL, $offset = NULL, $table = '')
	{
		if ($this->is_related == TRUE)
		{
			return $this->related_get($this->buddy, $limit, $offset);
		}
			
		if ($table === '')
		{
			$table = $this->table;
		}
			
		$this->last_res = $this->db->get($table, $limit, $offset);
		
		$this->iter			= new ArrayIterator($this->last_res->result());
		$this->first_row	= $this->iter->current();
		$this->clear();
		
		return $this;
	}
	
	/**
	 * @param Krak The parent/buddy object to get from.
	 * @param int limit
	 * @param int offset
	 */
	public function related_get(Krak &$buddy, $limit = NULL, $offset = NULL)
	{
		$rel_data = array();
		
		// my bud has one of me
		if (array_key_exists($this->class_name, $buddy->has_one))
		{
			$rel_data = $buddy->has_one[$this->class_name];
			
			/*
			 * We've already validated the buddy -> one -> this
			 * relationship, so we just need to check if 
			 * This relationship may be 
			 * buddy -> one -> this & this -> one -> buddy
			 * OR
			 * buddy -> one -> this & this -> many -> buddy
			 *
			 * if one-to-one then the ITFK can be in either
			 * table.
			 * if one-to-many then the ITFK can only be in 
			 * the many (in this case it would be in this)
			 * So let's see if this has the key first, then
			 * we'll check buddy. If they both don't have
			 * it then there's a big issue.
			 */
			 
			 if ($rel_data['is_parent'] === TRUE)
			 {
			 	/*
			 	 * this has an ITFK of buddy. So buddy's this_column
			 	 * is inside of the this table
			 	 */
			 	$this->db->where($rel_data['this_column'], $buddy->get_pkey());
			 }
			 else
			 {
			 	// buddy has an itfk to this.
			 	$this->db->where($this->primary_key, $buddy->{$rel_data['buddy_column']});
			 } 
		}
		else if (array_key_exists($this->class_name, $buddy->has_many))
		{
			if (array_key_exists($buddy->class_name, $this->has_one))
			{
				$this->db->where($buddy->has_many[$this->class_name]['this_column'], $buddy->get_pkey());
			}
			else if (array_key_exists($buddy->class_name, $this->has_many))
			{
				$rel_data = $buddy->has_many[$this->class_name];
				$j_clause = $this->table . '.' . $this->primary_key . ' = ' . $rel_data['join_table'] . '.' . $rel_data['buddy_column'];

				$this->db->select($this->table . '.*');
				$this->db->join($rel_data['join_table'], $j_clause, 'left');
				$this->db->where($rel_data['join_table'] . '.' . $rel_data['this_column'], $buddy->get_pkey());
			}
			else
			{
				show_error("# Krak error\n relationship not properly specified");
			}
		}
		
		// can't use $this->get because I'd run into an infinite loop
		$this->last_res = $this->db->get($this->table, $limit, $offset);
		
		$this->iter			= new ArrayIterator($this->last_res->result());
		$this->first_row	= $this->iter->current();
		$this->clear();
		
		return $this;
	}
	
	public function get_where($where = NULL, $limit = NULL, $offset = NULL, $table = '')
	{
		if ($table === '')
		{
			$table = $this->table;
		}
		
		$this->db->where($where);
		return $this->get($limit, $offset, $table);
	}
	
	public function query($sql, $binds = array())
	{
		$this->last_res = $this->db->query($sql, $binds);
		
		$this->iter			= new ArrayIterator($this->last_res->result());
		$this->first_row	= $this->iter->current();
		$this->clear();
		
		return $this;
	}
	
	public function save(Krak &$rel_obj = NULL)
	{
		if ($rel_obj !== NULL)
		{
			return $this->save_relation($rel_obj);
		}
		
		/*
		 * Are we saving or updating?
		 * if we have already run a get statement and the primary key field exists then we are updating
		 * a user could also just run the update() method... but that can get pretty taxing ; )
		 * we check primary key because user may have run a query statement, we need primary key to update in qb
		 */
		if ($this->get_pkey_save() !== NULL)
		{
			return $this->update();
		}
			
		$this->trigger(self::EVENT_BEFORE_SAVE);
		$res = FALSE;
		
		if ($this->created_field !== '')
		{
			$this->{$this->created_field} = date('Y-m-d H:i:s', time());
		}
			
		if ($this->updated_field !== '')
		{
			$this->{$this->updated_field} = date('Y-m-d H:i:s', time());
		}
		
		// makes krak_obj only hold values in the field array
		$krak_data = array()
		$this->build_krak_data($krak_data);
		
		$res = $this->db->insert($this->table, $krak_data);
		
		if ($res)
		{
			$this->krak_obj->{$this->primary_key} = $this->db->insert_id();
		}
		
		$this->iter			= NULL;
		$this->last_res		= NULL;
		$this->first_row	= NULL;
		$this->clear();
		
		$this->trigger(self::EVENT_AFTER_SAVE);
		return ($res) ? $this->db->insert_id() : FALSE;
	}

	public function save_relation(Krak &$buddy)
	{
		$res = FALSE;
		
		$rel_data = array();
		
		// my bud has one of me
		if (array_key_exists($buddy->class_name, $this->has_one))
		{
			$rel_data = $this->has_one[$this->class_name];
			
			 
			 if ($rel_data['is_parent'] === TRUE)
			 {
			 	/*
			 	 * this has an ITFK of buddy. So buddy's this_column
			 	 * is inside of the this table
			 	 */
			 	$this->db->where($rel_data['this_column'], $buddy->get_pkey());
			 }
			 else
			 {
			 	// buddy has an itfk to this.
			 	$this->db->where($this->primary_key, $buddy->{$rel_data['buddy_column']});
			 } 
		}
		else if (array_key_exists($this->class_name, $buddy->has_many))
		{
			if (array_key_exists($buddy->class_name, $this->has_one))
			{
				$this->db->where($buddy->has_many[$this->class_name]['this_column'], $buddy->get_pkey());
			}
			else if (array_key_exists($buddy->class_name, $this->has_many))
			{
				$rel_data = $buddy->has_many[$this->class_name];
				$j_clause = $this->table . '.' . $this->primary_key . ' = ' . $rel_data['join_table'] . '.' . $rel_data['buddy_column'];

				$this->db->select($this->table . '.*');
				$this->db->join($rel_data['join_table'], $j_clause, 'left');
				$this->db->where($rel_data['join_table'] . '.' . $rel_data['this_column'], $buddy->get_pkey());
			}
			else
			{
				show_error("# Krak error\n relationship not properly specified");
			}
		}
		
		// determine the relationships
		
		// $rel has one of this
		// e.g. user has one country
		// table -> users
		// buddy_column -> country_id
		// update users
		// set country_id = {country_id_val}
		// where {primary_key} = {user_id_val}
		
		if (array_key_exists($this->model, $rel_obj->has_one))
		{
			$a = array($rel_obj->has_one[$this->model]['buddy_column'] => $this->get_pkey());
			$res = $this->db->update($rel_obj->table, $a, array($rel_obj->primary_key => $rel_obj->get_pkey()));
			
			if ($res === FALSE)
				return FALSE;
		}
	
		// this has one of $rel
		
		if (array_key_exists($rel_obj->model, $this->has_one))
		{
			$a = array($this->has_one[$rel_obj->model]['buddy_column'] => $rel_obj->get_pkey());
			$res = $this->db->update($this->table, $a, array($this->primary_key => $this->get_pkey()));
		
			if ($res === FALSE)
				return FALSE;
		}
		
		// many to many now, we don't need to worry about a many-to-one or one-to-many because
		// the obj that has_many doesn't have any join fields
		if (array_key_exists($rel_obj->model, $this->has_many) && array_key_exists($this->model, $rel_obj->has_many))
		{
			$rel_data = $this->has_many[$rel_obj->model];
			$full_table = $rel_data['join_table'];
			
			if ($full_table == '')
			{
				$full_table = ($rel_obj->table < $this->table) ? $rel_obj->table . '_' . $this->table : $this->table . '_' . $rel_obj->table;
				$rel_data['join_table'] = $full_table;
			}
			
			$a = array($rel_data['this_colum'] => $this->get_pkey(), $rel_data['buddy_column'] => $rel_obj->get_pkey());
			$res = $this->db->insert($full_table, $a);
		}
		
		return $res;
	}
	
	public function update()
	{
		$this->trigger(self::EVENT_BEFORE_UPDATE);
		$res = FALSE;

		if ($this->updated_field !== '')
			$this->krak_obj->{$this->updated_field} = date('Y-m-d H:i:s', time());
			
		$pkey = $this->get_pkey();
		
		if ($pkey !== NULL)	// be careful, if you don't have an existing object or other where's then this will update the entire table!!!
		{
			$this->db->where($this->primary_key, $this->get_pkey());
		}

		// makes krak_obj only hold values in the field array
		$this->validate_krak_obj();

		$res = $this->db->update($this->table, $this->krak_obj);
		$this->krak_obj = new stdClass();	// for sure needed because we don't user to worry about unsetting values they don't want updated
		// don't unset the iter because we may be updating objects from a get (in a loop)
		
		$this->trigger(self::EVENT_AFTER_UPDATE);
		return $res;
	}
	
	public function delete(Krak &$rel_obj = NULL)
	{
		if ($rel_obj !== NULL)
			return $this->delete_related($rel_obj);
		
		$this->trigger(self::EVENT_BEFORE_DELETE);
		$res = FALSE;
		
		$pkey = $this->get_pkey();
		
		if ($pkey !== NULL)	// don't worry, CI will make sure there is a where clause before running a delete
		{
			$this->db->where($this->primary_key, $pkey);
		}
		
		$res = $this->db->delete($this->table);
		$this->krak_obj = new stdClass();
		// same as update, don't destroy iterator
		
		$this->trigger(self::EVENT_AFTER_DELETE);
		return $res;
	}
	
	/**
	 *
	 * @deprecated
	 *
	 */
	public function delete_all()
	{
		$where_in = array();
		$res = FALSE;
		
		for ($i = $this->iter; $i->valid(); $i->next())
		{
			$pkey = $i->current()->{$this->primary_key};
			
			if ($pkey !== NULL)
				$where_in[] = $pkey;
		}
		
		if (count($where_in) > 0)
		{
			$this->db->where_in($this->primary_key, $where_in);
			$res = $this->db->delete($this->table);
			$this->clear();
		}
		
		return $res;
	}
	
	public function delete_related(Krak &$other_obj)
	{
		$res = FALSE;
		
		// pretty much the same code as save_related except we set to NULL
		// determine the relationships
		
		// $rel has one of this
		// e.g. user has one country
		// table -> users
		// buddy_column -> country_id
		// update users
		// set country_id = {country_id_val}
		// where {primary_key} = {user_id_val}
		
		if (array_key_exists($this->model, $rel_obj->has_one))
		{
			$a = array($rel_obj->has_one[$this->model]['buddy_column'] => NULL);
			$res = $this->db->update($rel_obj->table, $a, array($rel_obj->primary_key => $rel_obj->get_pkey()));
			
			if ($res === FALSE)
				return FALSE;
		}
	
		// this has one of $rel
		
		if (array_key_exists($rel_obj->model, $this->has_one))
		{
			$a = array($this->has_one[$rel_obj->model]['buddy_column'] => NULL);
			$res = $this->db->update($this->table, $a, array($this->primary_key => $this->get_pkey()));
		
			if ($res === FALSE)
			{
				return FALSE;
			}
		}
		
		// many to many now, we don't need to worry about a many-to-one or one-to-many because
		// the obj that has_many doesn't have any join fields
		if (array_key_exists($rel_obj->model, $this->has_many) && array_key_exists($this->model, $rel_obj->has_many))
		{
			$rel_data = $this->has_many[$rel_obj->model];
			$full_table = $rel_data['join_table'];
			
			if ($full_table == '')
			{
				$full_table = ($rel_obj->table < $this->table) ? $rel_obj->table . '_' . $this->table : $this->table . '_' . $rel_obj->table;
				$rel_data['join_table'] = $full_table;
			}
			
			$a = array($rel_data['this_colum'] => $this->get_pkey(), $rel_data['buddy_column'] => $rel_obj->get_pkey());
			$res = $this->db->delete($full_table, $a);
		}
		
		return $res;
	}
	
	public function add_event_listener($callback, $event_types = '', $use_this = TRUE)
	{
		if ($event_types === '')
		{
			$event_types = self::$before_save_update;
		}
		
		if ($use_this)
		{
			$callback = array($this, $callback);
		}
		
		if ( ! is_array($event_types))
		{
			$event_types = array($event_types);
		}
			
		foreach ($event_types as $et)
			$this->event_queues[$et][] = $callback;
	}
	
	public function remove_event_listener($callback, $event_types = '', $use_this = TRUE)
	{
		if ($event_types === '')
		{
			$event_types = self::$before_save_update;
		}
		
		if ($use_this)
		{
			$callback = array($this, $callback);
		}
		
		if ( ! is_array($event_types))
		{
			$event_types = array($event_types);
		}
			
		foreach ($event_types as $et)
		{
			$q = &$this->event_queues[$et];
		
			if (is_array($q))
			{
				for ($i = 0; $i < count($q) && $q[$i] !== $callback; $i++){}
			 
				if ($i < count($q))
				{
					array_splice($q, $i, 1);
				}
			}
		}
	}
	
	public function clear()
	{
		foreach (self::$fields as $field)
		{
			unset($this->{$field});
		}
	}
	
	public function get_krak_bundle()
	{
		$b					= new Bundle();
		$b->table			= &$this->table;
		$b->model			= &$this->model;
		$b->has_one			= &$this->has_one;
		$b->has_many		= &$this->has_many;
		$b->primary_key		= &$this->primary_key;
		$b->created_field	= &$this->created_field;
		$b->updated_field	= &$this->updated_field;
		return $b;
	}
	
	public function num_rows()
	{
		if ($this->last_res !== NULL)
		{
			return $this->last_res->num_rows();
		}
		
		return 0;
	}
	
	public function result()
	{
		if ($this->last_res !== NULL)
		{
			return $this->last_res->result();
		}
			
		return array();
	}
	
	/**
	 * Creates an array that actually gets inserted and updated.
	 * Only keys that are apart $this->fields should be sent to
	 * the database
	 */
	private function build_krak_data(&$krak_data)
	{
		$valid_krak = array();
		foreach (self::$fields as $field)
		{
			if (property_exists($this, $field))
			{
				$valid_krak[$field] = &$this->{$field};
			}
		}
		
		$krak_data = $valid_krak;
	}
	
	private function trigger($event)
	{
		if (isset($this->event_queues[$event]))
		{
			$q = $this->event_queues[$event];
			
			if (is_array($q))
			{
				for ($c = count($q); $c > 0; $c--)
				{
					call_user_func(array_shift($q));
				}
			}
		}
	}
	
	private function get_pkey()
	{
		if (property_exists($this->krak_obj, $this->primary_key))
		{
			return $this->krak_obj->{$this->primary_key};
		}
		else if ($this->iter !== NULL)
		{
			return $this->iter->current()->{$this->primary_key};
		}
		
		return NULL;
	}
	
	private function get_pkey_save()
	{
		if (property_exists($this->krak_obj, $this->primary_key))
		{
			return $this->krak_obj->{$this->primary_key};
		}
		else if ($this->iter !== NULL)
		{
			return $this->iter->current()->{$this->primary_key};
		}
		
		return NULL;
	}
	
	private function extend_related_objects(&$related_objects)
	{	
		// simple default values array
		$default = array();
		
		foreach ($related_objects as $key => $class)
		{
			$default['this_column']		= $this->model . '_id';
				
			// both of these values get populated once we have the buddy object
			$default['buddy_column']	= '';
			$default['join_table']		= '';
		
			if (is_array($class))
			{
				$default['class'] = $key; 
				$default = array_merge($default, $class);
			}
			else	// must be a string
			{
				$default['class'] = $class;
			}
			
			// unset the current value because we don't use it any more, free up memory
			unset($related_objects[$key]);
			$related_objects[$default['class']] = $default;
		}
	}
	
	private function has_alias($name, $has_one = TRUE)
	{
		if ($has_one)
		{
			foreach (self::$aliases[$name] as $class)
			{
				if (array_key_exists($class, $this->has_one))
				{
					return TRUE;
				}
			}
		}
		else
		{
			foreach (self::$aliases[$name] as $class)
			{
				if (array_key_exists($class, $this->has_many))
				{
					return TRUE;
				}
			}
		}
	
		return FALSE;
	}
	
	/* I T E R A T O R  A G G R E G A T E   M E T H O D S */
	
	public function getIterator()
	{
		if ($this->iter !== NULL)
		{
			return $this->iter;
		}
			
		return new ArrayIterator();
	}
	
	/* Array Access Methods */
	
	public function offsetExists($index)
	{
		if ($this->iter !== NULL)
		{
			return $this->iter->offsetExists($index);
		}
		
		return FALSE;
	}
	
	public function offsetGet($index)
	{
		if ($this->iter !== NULL)
		{
			return $this->iter->offsetGet($index);
		}
		
		return NULL;
	}
	
	public function offsetSet($index, $value)
	{
		if ($this->iter !== NULL)
		{
			$this->iter->offsetSet($index, $value);
		}
	}
	
	public function offsetUnset($index)
	{
		if ($this->iter !== NULL)
		{
			return $this->iter->offsetUnset($index);
		}
	}
	
	/* Countable methods */
	
	public function count()
	{
		if ($this->iter !== NULL)
		{
			return $this->last_res->num_rows;
		}
			
		return 0;
	}
}

/*** register the loader functions ***/
spl_autoload_register('Krak::model_autoloader');