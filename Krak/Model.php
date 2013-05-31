<?php
namespace Krak;

abstract class Model implements \IteratorAggregate, \ArrayAccess, \Countable
{
	// Iterator Contants
	const ITERATOR_SIMPLE_BUFFERED	= '\ArrayIterator';
	const ITERATOR_DEFAULT_BUFFERED	= '\Krak\Iterator\Buffered';

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
	private static $iter_class	= '';
	
	/* I N S T A N C E   P R O P E R T I E S */
	protected $fields = array();

	protected $model			= '';
	protected $table			= '';
	protected $parent_of		= array();
	protected $child_of			= array();
	protected $buddy_of			= array();
	protected $primary_key		= 'id';
	protected $created_field	= '';
	protected $updated_field	= '';
	
	protected $db;
	
	private $event_queues		= array();
	private $is_related			= FALSE;
	private $related_model		= NULL;
	private $related_name		= '';
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
			
			self::$iter_class = (isset(self::$config['iterator'])) ? self::$config['iterator'] : self::ITERATOR_DEFAULT_BUFFERED;
			$this->_load_extensions();
			
			self::$has_init = TRUE;
		}
		
		$ci->load->helper('inflector');
		
		// set up the default values
		$this->class_name = get_class($this);
		$lower_class_name = strtolower($this->class_name);
		
		if ($this->model == '')
		{
			$this->model = str_replace('\\', '_', $lower_class_name);
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
		$default = array();
		$index = '';
		
		// setup the parent_of relationships
		foreach ($this->parent_of as $key => $class)
		{
			if (is_array($class))
			{
				$default['class']		= (isset($class['class']))			? $class['class']		: $key;
				$default['this_column']	= (isset($class['this_column']))	? $class['this_column']	: $this->model . '_' . $this->primary_key;
				$index					= (isset($class['class']))			? $key					: strtolower(str_replace('\\', '_', $key));
			}
			else	// must be a string
			{
				$default['class']		= $class;
				$default['this_column']	= $this->model . '_' . $this->primary_key;
				$index = strtolower(str_replace('\\', '_', $class));
			}
			
			// unset the current value because we don't use it any more, free up memory
			unset($this->parent_of[$key]);
			$this->parent_of[$index] = $default;
		}
		
		// reset the vals
		$default = array();
		$index = '';
		
		// setup the child_of relationships
		foreach ($this->child_of as $key => $class)
		{
			if (is_array($class))
			{
				$default['class']			= (isset($class['class']))			? $class['class']			: $key;
				$default['parent_column']	= (isset($class['parent_column']))	? $class['parent_column']	: '';	// will be evaluated when the query is made
				$index						= (isset($class['class']))			? $key						: strtolower(str_replace('\\', '_', $key));
			}
			else	// must be a string
			{
				$default['class']			= $class;
				$default['parent_column']	= '';	// will be evaluated when the query is made
				$index = strtolower(str_replace('\\', '_', $class));
			}
			
			// unset the current value because we don't use it any more, free up memory
			unset($this->child_of[$key]);
			$this->child_of[$index] = $default;
		}
		
		// reset the vals
		$default = array();
		$index = '';
		
		// setup the buddy_of relationships
		foreach ($this->buddy_of as $key => $class)
		{
			if (is_array($class))
			{
				$default['class']			= (isset($class['class']))			? $class['class']			: $key;
				$default['this_column']		= (isset($class['this_column']))	? $class['this_column']		: $this->model . '_' . $this->primary_key;
				$default['buddy_column']	= (isset($class['buddy_column']))	? $class['buddy_column']	: '';	// will be evaluated when the query is made
				$default['join_table']		= (isset($class['join_table']))		? $class['join_table']		: '';	// will be evaluated when the query is made
				$index						= (isset($class['class']))			? $key						: strtolower(str_replace('\\', '_', $key));
			}
			else	// must be a string
			{
				$default['class']			= $class;
				$default['this_column']		= $this->model . '_' . $this->primary_key;
				$default['buddy_column']	= '';	// will be evaluated when the query is made
				$default['join_table']		= '';	// will be evaluated when the query is made
				$index						= strtolower(str_replace('\\', '_', $class));
			}
			
			// unset the current value because we don't use it any more, free up memory
			unset($this->buddy_of[$key]);
			$this->buddy_of[$index] = $default;
		}
		
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
            	$file = strtolower($file);
                
                if (!file_exists($file))
                {
                	show_error('Krak Error: loading extension ' . $extension . ': File not found.');
                }
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

	public static function model_autoloader($class, $ret = false)
	{
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

		$path = APPPATH . 'models/' . $file . $class . '.php';
		
		if (file_exists($path))
		{
			if ($ret == false)
			{
				require_once $path;
			}
			else
			{
				return $path;
			}
		}
		else
		{
			if ($ret == true)
			{
				return false;
			}
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
		else if (array_key_exists($name, $this->parent_of))
		{
			$child					= new $this->parent_of[$name]['class'];
			$child->is_related		= TRUE;
			$child->related_model	= &$this;
			$child->related_name	= $name;
		
			// let's do some validation
			if (!in_array($this->parent_of[$name]['this_column'], $child->fields))
			{
				print_r($this->parent_of[$name]['this_column']);
				print_r($child->fields);
				show_error('Krak Error: Child object doesn\'t contain foreign key column to this parent. Parent = ' . $this->class_name . ', Child = ' . $child->class_name);
				die();
			}
		
			$this->{$name} = $child;
			return $this->{$name};
		}
		else if (array_key_exists($name, $this->child_of))
		{
			$parent					= new $this->child_of[$name]['class'];
			$parent->is_related		= TRUE;
			$parent->related_model	= &$this;
			$parent->related_name	= $name;
		
			// we have to finish the child_of array relationships now that we have the parent object
			if ($this->child_of[$name]['parent_column'] == '')
			{
				$this->child_of[$name]['parent_column'] = $parent->model . '_' . $parent->primary_key;
			}
		
			// let's do some validation
			if (!in_array($this->child_of[$name]['parent_column'], $this->fields))
			{
				show_error('Krak Error: Child object doesn\'t contain foreign key column to this parent. Parent = ' . $parent->class_name . ', Child = ' . $this->class_name);
				die();
			}
		
			$this->{$name} = $parent;
			return $this->{$name};
		}
		else if (array_key_exists($name, $this->buddy_of))
		{
			$buddy					= new $this->buddy_of[$name]['class'];
			$buddy->is_related		= TRUE;
			$buddy->related_model	= &$this;
			$buddy->related_name	= $name;
			
			// we have to finish the buddy_of array
			if ($this->buddy_of[$name]['buddy_column'] == '')
			{
				$this->buddy_of[$name]['buddy_column'] = $buddy->model . '_' . $buddy->primary_key;
			}
			
			if ($this->buddy_of[$name]['join_table'] == '')
			{
				$this->buddy_of[$name]['join_table'] = ($this->table < $buddy->table) ? $this->table . '_' . $buddy->table : $buddy->table . '_' . $this->table;
			}
			
			// theres no way to do validation, so we'll just assume everything was setup properly with the join table and return buddy
			$this->{$name} = $buddy;
			return $this->{$name};
		}
		else if ($name == 'fields')
		{
			return $this->fields;
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
			return $this->related_get($limit, $offset);
		}
			
		if ($table === '')
		{
			$table = $this->table;
		}
			
		$this->last_res = $this->db->get($table, $limit, $offset);
		
		$this->iter			= new self::$iter_class($this->last_res->result('Krak\Result'), $this);
		$this->first_row	= $this->iter->current();
		$this->clear();
		
		return $this;
	}
	
	/**
	 * @param Krak The parent/buddy object to get from.
	 * @param int limit
	 * @param int offset
	 */
	public function related_get($limit = NULL, $offset = NULL)
	{	
		// if I'm a child of my related_model
		if (array_key_exists($this->related_name, $this->related_model->parent_of))
		{
			/*
			 * I'm for sure a child, so the fkey is in my table, and I don't
			 * need to do any worry about any errors because I've already
			 * done error checking.
			 */
			
			$this->db->where($this->related_model->parent_of[$this->related_name]['this_column'], $this->related_model->get_pkey());
		}
		else if (array_key_exists($this->related_name, $this->related_model->child_of))	// if I'm a parent
		{
			/*
			 * I'm for sure a parent, so the fkey is in my child table (related_model)
			 * No need for error checking because it was already handled in __get
			 */
			
			// e.g $this->db->where('id', $video->rider_id);
			$this->db->where($this->primary_key, $this->related_model->{$this->related_model->child_of[$this->related_name]['parent_column']});
		}
		else if (array_key_exists($this->related_name, $this->related_model->buddy_of))
		{
			$rel_data = $this->related_model->buddy_of[$this->related_name];
			
			// I'm for sure a buddy
			$j_clause = $this->table . '.' . $this->primary_key . ' = ' . $rel_data['join_table'] . '.' . $rel_data['buddy_column'];
			
			$this->db->select($this->table . '.*');
			$this->db->join($rel_data['join_table'], $j_clause, 'left');
			$this->db->where($rel_data['join_table'] . '.' . $rel_data['this_column'], $this->related_model->get_pkey());
		}
		
		// can't use $this->get because I'd run into an infinite loop
		$this->last_res = $this->db->get($this->table, $limit, $offset);
		
		$this->iter			= new self::$iter_class($this->last_res->result('Krak\Result'), $this);
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
		
		// Create a new instance of iterator
		$this->iter			= new self::$iter_class($this->last_res->result('Krak\Result'), $this);
		$this->first_row	= $this->iter->current();
		$this->clear();
		
		return $this;
	}
	
	public function save($buddy = NULL, $name = '')
	{	
		// check to see if we are really trying to save a relation
		if ($buddy != NULL)
		{
			/*
			 * We don't want to run any more querires than we have to
			 * if $this is parent, and is saving a bunch of children, then we need
			 * to run a query for every child to update.
			 * if $this is a child, and is saving a bunch of parents, then we only need
			 * to save $this once
			 */
			if (is_array($buddy))
			{
				$statuses = array();
				
				foreach ($buddy as $name => $obj)
				{
					if (is_string($name))
					{
						$status = $this->save_relation($obj, $data, $name);
					}
					else
					{
						$status = $this->save_relation($obj, $data);
					}
					
					// if I'm parent, then we need to update child, no matter
					if ($status == 0)
					{
						$obj->save();
					}
					
					$statuses[$status] = TRUE;
				}
				
				if (isset($statuses[1]))
				{
					$this->save();
				}
				
				if (isset($statuses[2]))
				{
					foreach ($data as $table => $save_data)
					{
						$this->insert_batch($save_data, $table);
					}
				}
			}
			else
			{
				$status = $this->save_relation($buddy, $data, $name);
				
				// update buddy
				if ($status == 0)
				{
					$buddy->save();
				}
				else if ($status == 1) // update this
				{
					$this->save();
				}
				else if ($status == 2) // update join table
				{
					foreach ($data as $table => $save_data)
					{
						$this->insert_batch($save_data, $table);
					}
				}
			}
			
			return;
		}
	
		/*
		 * Are we saving or updating?
		 * if we have already run a get statement and the primary key field exists then we are updating
		 * a user could also just run the update() method... but that can get pretty taxing ; )
		 * we check primary key because user may have run a query statement, we need primary key to update in krak
		 */
		if ($this->get_pkey() !== NULL)
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
		$krak_data = array();
		$this->build_krak_data($krak_data);
		
		$res = $this->db->insert($this->table, $krak_data);
		
		if ($res)
		{
			$this->{$this->primary_key} = $this->db->insert_id();
		}
		
		$this->iter			= NULL;
		$this->last_res		= NULL;
		$this->first_row	= NULL;
		
		$this->trigger(self::EVENT_AFTER_SAVE);
		return ($res) ? $this->db->insert_id() : FALSE;
	}

	public function save_relation(&$buddy, &$data = array(), $name = '')
	{
		if ($name == '')
		{
			$name = $buddy->model;
		}
		
		if (array_key_exists($name, $this->parent_of))
		{
			$buddy->{$this->parent_of[$name]['this_column']} = $this->get_pkey();
			
			return 0;
		}
		else if (array_key_exists($name, $this->child_of))
		{
			// parent_column may not have been set, so let's set now if it hasn't
			if ($this->child_of[$name]['parent_column'] == '')
			{
				$this->child_of[$name]['parent_column'] = $buddy->model . '_' . $buddy->primary_key;
			}
			
			$this->{$this->child_of[$name]['parent_column']} = $buddy->get_pkey();
			
			return 1;
		}
		else if (array_key_exists($name, $this->buddy_of))
		{
			// The buddy values may not have been set, so let's set them now
			if ($this->buddy_of[$name]['buddy_column'] == '')
			{
				$this->buddy_of[$name]['buddy_column'] = $buddy->model . '_' . $buddy->primary_key;
			}
			
			if ($this->buddy_of[$name]['join_table'] == '')
			{
				$this->buddy_of[$name]['join_table'] = ($this->table < $buddy->table) ? $this->table . '_' . $buddy->table : $buddy->table . '_' . $this->table;
			}
		
			$data[$this->buddy_of[$name]['join_table']][] = array(
				$this->buddy_of[$name]['this_column']	=> $this->get_pkey(),
				$this->buddy_of[$name]['buddy_column']	=> $buddy->get_pkey()
			);
			
			return 2;
		}
		
		return 3;
	}
	
	public function insert_batch($data, $table = '')
	{
		if ($table == '')
		{
			$table = $this->table;
		}
		
		return $this->db->insert_batch($table, $data);
	}
	
	public function update()
	{
		$this->trigger(self::EVENT_BEFORE_UPDATE);
		$res = FALSE;

		if ($this->updated_field !== '')
		{
			$this->{$this->updated_field} = date('Y-m-d H:i:s', time());
		}
		
		$pkey = $this->get_pkey();
		
		if ($pkey == NULL)
		{
			return FALSE;
		}

		// makes krak_data only hold values in the field array
		$krak_data = array();
		$this->build_krak_data($krak_data);
		
		unset($krak_data[$this->primary_key]);
		
		$res = $this->db->where($this->primary_key, $pkey)->update($this->table, $krak_data);
		
		$this->trigger(self::EVENT_AFTER_UPDATE);
		return $res;
	}
	
	public function update_set()
	{
		$this->trigger(self::EVENT_BEFORE_UPDATE);
		$res = FALSE;

		if ($this->updated_field !== '')
		{
			$this->{$this->updated_field} = date('Y-m-d H:i:s', time());
		}

		// makes krak_data only hold values in the field array
		$krak_data = array();
		$this->build_krak_data($krak_data);

		$res = $this->db->update($this->table, $krak_data);
		
		$this->clear();

		$this->trigger(self::EVENT_AFTER_UPDATE);
		return $res;
	}
	
	public function delete($buddy = NULL, $name = '')
	{
		// check to see if we are really trying to delete a relation
		if ($buddy != NULL)
		{
			/*
			 * We don't want to run any more querires than we have to
			 * if $this is parent, and is saving a bunch of children, then we need
			 * to run a query for every child to update.
			 * if $this is a child, and is saving a bunch of parents, then we only need
			 * to save $this once
			 */
			if (is_array($buddy))
			{
				$statuses = array();
				
				foreach ($buddy as $name => $obj)
				{
					if (is_string($name))
					{
						$status = $this->delete_relation($obj, $data, $name);
					}
					else
					{
						$status = $this->delete_relation($obj, $data);
					}
					
					// if I'm parent, then we need to update child, no matter
					if ($status == 0)
					{
						$obj->save();
					}
					
					$statuses[$status] = TRUE;
				}
				
				if (isset($statuses[1]))
				{
					$this->save();
				}
				
				if (isset($statuses[2]))
				{
					foreach ($data as $table => $save_data)
					{
						$this->insert_batch($save_data, $table);
					}
				}
			}
			else
			{
				$status = $this->delete_relation($buddy, $data, $name);
				
				// update buddy
				if ($status == 0)
				{
					$buddy->save();
				}
				else if ($status == 1) // update this
				{
					$this->save();
				}
				else if ($status == 2) // update join table
				{		
					foreach ($data as $table => $save_data)
					{
						$where_string = '';
						
						foreach ($save_data as $where_data)
						{
							$where_string .= '(';
							
							$where_string .= key($where_data) . ' = ' . $this->db->escape(current($where_data)) . ' AND ';
							next($where_data);
							$where_string .= key($where_data) . ' = ' . $this->db->escape(current($where_data));
										
							$where_string .= ') OR ';
						}
						
						$this->db->where(substr($where_string, 0, -4))->delete($table);
					}
				}
			}
			
			return;
		}
	
	
		$this->trigger(self::EVENT_BEFORE_DELETE);
		$res = FALSE;
		
		$pkey = $this->get_pkey();
		
		if ($pkey == NULL)
		{
			return FALSE;
		}
		
		$this->db->where($this->primary_key, $pkey);
		$res = $this->db->delete($this->table);
		// same as update, don't destroy iterator
		
		$this->trigger(self::EVENT_AFTER_DELETE);
		return $res;
	}
	
	public function delete_set()
	{	
		$this->trigger(self::EVENT_BEFORE_DELETE);
		$res = FALSE;

		$res = $this->db->delete($this->table);

		$this->clear();
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
			$this->iter			= NULL;
			$this->last_res		= NULL;
			$this->first_row	= NULL;
		}
			
		return $res;
	}
	
	public function delete_relation($buddy, &$data = array(), $name = '')
	{
		if (is_array($buddy))
		{
			foreach ($buddy as $key => $value)
			{
				if (is_string($key))
				{
					$this->delete_relation($buddy, $key, $data);
				}
				else
				{
					$this->delete_relation($buddy, '', $data);
				}
			}
			
			return;
		}
	
		if ($name == '')
		{
			$name = $buddy->model;
		}
		
		if (array_key_exists($name, $this->parent_of))
		{
			$buddy->{$this->parent_of[$name]['this_column']} = NULL;
			
			return 0;
		}
		else if (array_key_exists($name, $this->child_of))
		{
			// parent_column may not have been set, so let's set now if it hasn't
			if ($this->child_of[$name]['parent_column'] == '')
			{
				$this->child_of[$name]['parent_column'] = $buddy->model . '_' . $buddy->primary_key;
			}
			
			$this->{$this->child_of[$name]['parent_column']} = NULL;
			
			return 1;
		}
		else if (array_key_exists($name, $this->buddy_of))
		{
			// The buddy values may not have been set, so let's set them now
			if ($this->buddy_of[$name]['buddy_column'] == '')
			{
				$this->buddy_of[$name]['buddy_column'] = $buddy->model . '_' . $buddy->primary_key;
			}
			
			if ($this->buddy_of[$name]['join_table'] == '')
			{
				$this->buddy_of[$name]['join_table'] = ($this->table < $buddy->table) ? $this->table . '_' . $buddy->table : $buddy->table . '_' . $this->table;
			}
			
			$data[$this->buddy_of[$name]['join_table']][] = array(
				$this->buddy_of[$name]['this_column']	=> $this->get_pkey(),
				$this->buddy_of[$name]['buddy_column']	=> $buddy->get_pkey()
			);
			
			return 2;
		}
		
		return 3;
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
		{
			$this->event_queues[$et][] = $callback;
		}
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
	
	public function get_join_table($other_table)
	{
		return ($this->table < $other_table) ? $this->table . '_' . $other_table : $other_table . '_' . $this->table;
	}
	
	public function get_jt($buddy)
	{
		if ($buddy instanceof \Krak\Model)
		{
			$other_table = $buddy->table;
		}
		else
		{
			$other_table = $buddy;
		}
		
		return ($this->table < $other_table) ? $this->table . '_' . $other_table : $other_table . '_' . $this->table;
	}
	
	public function clear()
	{
		foreach ($this->fields as $field)
		{
			unset($this->{$field});
		}
	}
	
	public function set_iterator($iter)
	{
		self::$iter_class = 'Krak\Iterator\\' . $iter;
	}
	
	public function get_krak_bundle()
	{
		$b					= new Bundle();
		$b->table			= &$this->table;
		$b->model			= &$this->model;
		$b->class_name		= &$this->class_name;
		
		// indirect modification error if the next two props are assigned by ref
		$b->parent_of		= &$this->parent_of;
		$b->child_of		= &$this->child_of;
		$b->buddy_of		= &$this->buddy_of;
		
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
		foreach ($this->fields as $field)
		{
			if (property_exists($this, $field))
			{
				$krak_data[$field] = &$this->{$field};
			}
		}
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
		return $this->{$this->primary_key};
	}
	
	/* I T E R A T O R  A G G R E G A T E   M E T H O D S */
	
	public function getIterator()
	{
		if ($this->iter !== NULL)
		{
			return $this->iter;
		}
			
		return new \ArrayIterator();
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
		if ($this->last_res !== NULL)
		{
			return $this->last_res->num_rows();
		}
		
		return 0;
	}
}
