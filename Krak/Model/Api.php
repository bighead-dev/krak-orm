<?php

namespace Krak\Model;

abstract class Api extends \Krak\Model
{
    const API_PREFIX = '';
    const API_EVENT_PRE_BUILD_FIELDS  = 'api-pre-build-fields';
    const API_EVENT_POST_BUILD_FIELDS = 'api-post-build-fields';
    const API_EVENT_POST_BUILD_JOINS  = 'api-post-build-joins';
    
    
    public static $api_fields = [];
    public static $api_wheres = [];
    public static $api_sorts  = [];
    public static $api_joins  = [];
    public static $api_rel_ep = [];
    
    public $api_cur_fields = []; /* current fields to be used for select */
    public $api_where_map  = []; /* current valid where data to be used for where clause */
    public $api_sort_map   = [];
    public $api_join_set   = []; /* set of join keys */

    protected function get_compiled_api_fields()
    {
        static $cur_fields = null;
        
        if ($cur_fields) {
            return $cur_fields;
        }
        
        $cur_fields = [
            'self'  => static::$api_fields,
        ];
        
        foreach (static::$api_rel_ep as $ep => $class) {
            $cur_fields[$ep] = $class::$api_fields;
        }
        
        return $cur_fields;
    }

    public function api_docs()
    {
        return [
            'valid_fields'  => array_keys(static::$api_fields),
            'valid_wheres'  => array_keys(static::$api_wheres),
            'valid_sorts'   => array_keys(static::$api_sorts),
            'related_eps'   => array_keys(static::$api_rel_ep),
        ];
    }

    public function api_build_params_from_input($input)
    {
        $this->trigger(self::API_EVENT_PRE_BUILD_FIELDS);
        
        $this->build_cur_fields_from_input($input);
        
        $this->trigger(self::API_EVENT_POST_BUILD_FIELDS);
        
        $this->build_where_map_from_input($input);
        $this->build_sort_map_from_input($input);
        $this->build_join_set();
        
        $this->trigger(self::API_EVENT_POST_BUILD_JOINS);
        
        return $this;
    }
    
    public function api_build_query()
    {
        $this->select($this->api_build_select(), false);
        
        $wh   = $this->api_build_where();
        $sort = $this->api_build_order_by();
        
        if ($wh) {
            $this->where($wh, null, false);
        }
        if ($sort) {
            $this->order_by($sort, null, false);
        }
    
        static::api_build_joins($this->db, $this->api_join_set);
        
        /* some of the related endpoints might need to make joins also */
        $this->build_rel_ep_joins();
        
        return $this;
    }

    public function api_build_query_from_input($input)
    {
        $this->api_build_params_from_input($input)
            ->api_build_query();
            
        return $this;
    }
    
    private function build_cur_fields_from_input($input)
    {
        $cur_fields = [];
        $api_fields = $this->get_compiled_api_fields();

        if (!isset($input['fields'])) {
            $this->api_cur_fields = $api_fields;
            return;
        }
        
        /* fields can be a string or an array, if it's a string
           then turn it into an array to simplify the code */
        if (is_string($input['fields']))
        {
            $input['fields'] = [
                'self'  => $input['fields'],
            ];
        }
                        
        /* fields is set, so let's validate them */
        foreach ($input['fields'] as $key => $val)
        {
            if ($key === 0) {
                $key = 'self';
            }
            
            if (array_key_exists($key, $api_fields))
            {
                $vals = (is_array($val)) ? $val : array_flip(explode(',', $val));
                /* intersect the fields */
                $cur_fields[$key] = array_intersect_key(
                    $api_fields[$key],
                    $vals
                );
                
                /* if none of the fields were valid, just unset the cur_field key */
                if (!$cur_fields[$key]) {
                    unset($cur_fields[$key]);
                }
            }
        }
        
        if (!$cur_fields) {
            $this->api_cur_fields = $api_fields;
        }
        
        $this->api_cur_fields = $cur_fields;
    }
    
    private function build_fields_from_cur_fields()
    {
        /* the fields are stored as field => sql partial select, so to pass the fields into 
           the api partial build result method, we need to get the array keys of the cur_fields */
        $fields = [
            'self' => array_keys($this->api_cur_fields['self'])
        ];
        
        /* grab the fields from related endpoints */
        foreach (static::$api_rel_ep as $ep => $class)
        {
            if (!array_key_exists($ep, $this->api_cur_fields)) {
                continue;
            }
            
            $fields[$ep] = array_keys($this->api_cur_fields[$ep]);
        }
        
        return $fields;
    }
    
    public function api_build_result()
    {
        $result = [];
        
        $res = $this->get();

        $fields = $this->build_fields_from_cur_fields();
    
        /* loop over the result set */
        foreach ($this as $obj)
        {
            $cur_data = static::api_build_partial_result($fields['self'], $obj);
            
            foreach (static::$api_rel_ep as $ep => $class)
            {
                if (!array_key_exists($ep, $this->api_cur_fields)) {
                    continue;
                }
                
                $cur_data[$ep] = $class::api_build_partial_result($fields[$ep], $obj);
            }
            
            $result[] = $cur_data;
        }
        
        return $result;
    }
    
    public function api_build_indexed_result(&$ids)
    {
        $result = [];
        $ids    = [];
        
        $res = $this->get();
        
        $fields = $this->build_fields_from_cur_fields();

        foreach ($this as $obj)
        {
            $cur_data = static::api_build_partial_result($fields['self'], $obj);
            
            foreach (static::$api_rel_ep as $ep => $class)
            {
                if (!array_key_exists($ep, $this->api_cur_fields)) {
                    continue;
                }
                
                $cur_data[$ep] = $class::api_build_partial_result($fields[$ep], $obj);
            }
            
            $ids[] = $cur_data['id'];
            $result[$cur_data['id']] = $cur_data;
        }
        
        return $result;
    }

    private function build_sort_map_from_input($input)
    {
        if (!isset($input['sort'])) {
            return;
        }
        
        /* sorts are defined like: field1-asc,field2-desc,... */
        $fields = explode(',', strval($input['sort']));
        
        foreach ($fields as $val)
        {
            $field = $val;
            $dir   = 'asc';
            
            if (strpos($val, '-') !== false) {
                list($field, $dir) = explode('-', $val);
            }
            
            /* make sure the sort direction is valid */
            if ($dir != 'asc' && $dir != 'desc') {
                $dir = 'asc'; /* maybe print a helpful message saying the sort direction was invalid? */
            }
            
            /* the sort needs to be defined the the api_sorts */
            if (!array_key_exists($field, static::$api_sorts)) {
                continue;
            }
            
            $this->api_sort_map[$field] = [
                static::$api_sorts[$field],
                $dir
            ];
        }
    }
    
    private function build_where_map_from_input($input)
    {
        $wm = []; /* where map */
        
        foreach ($input as $key => $val)
        {
            if (!array_key_exists($key, static::$api_wheres)) {
                continue;
            }
                        
            $values = [
                static::$api_wheres[$key]
            ];
            
            /* push the values */
            if (!is_array($val)) {
                $values[] = $this->api_process_where_value($key, $val);
            }
            else
            {
                foreach ($val as $v) {
                    $values[] = $this->api_process_where_value($key, $v);
                }
            }
            
            $wm[$key] = $values;
        }
        
        $this->api_where_map = $wm;
    }
    
    private function append_to_set(&$set, $vals)
    {
        if (is_array($vals))
        {
            foreach ($vals as $val) {
                $set[$val] = null;
            }
        }
        else {
            $set[$vals] = null;
        }
    }
    
    private function build_rel_ep_joins()
    {
        foreach ($this->api_cur_fields as $ep => $cur_fields)
        {
            if ($ep == 'self') {
                continue; /* don't worry about ourselves */
            }
            
            $js = [];
            
            /* grab the related ep class name */
            $class = static::$api_rel_ep[$ep];
            
            /* loop over the related endpoints fields and see if any of the related endpoints
               fields require a join. If so, then add to the join set */
            foreach ($cur_fields as $key => $val)
            {
                if (array_key_exists($key, $class::$api_joins)) {
                    $this->append_to_set($js, $class::$api_joins[$key]);
                }
            }
            
            /* now the join set should be built with all of the necessary joins for the
               related ep, so let's actually build the joins */
            $class::api_build_joins($this->db, $js);
        }
    }
    
    private function build_join_set()
    {
        $js = []; /* join set */
        
        /*
         * we build the join set by comparing the where_map, the sort_map, related endpoints,
         * and the cur_fields against the api_joins definition.
         * If any of them have key that also exists in the api_joins map, then
         * we need to add it to the join set to create the necessary joins
         * for the query
         */
         
        /* search the related endpoints */
        foreach ($this->api_cur_fields as $key => $val)
        {
            if (array_key_exists($key, static::$api_joins)) {
                $this->append_to_set($js, static::$api_joins[$key]);
            }
        }
        
        /* search the current fields for self */
        foreach ($this->api_cur_fields['self'] as $key => $val)
        {
            if (array_key_exists($key, static::$api_joins)) {
                $this->append_to_set($js, static::$api_joins[$key]);
            }
        }
        
        /* search the where map */
        foreach ($this->api_where_map as $key => $val)
        {
            if (array_key_exists($key, static::$api_joins)) {
                $this->append_to_set($js, static::$api_joins[$key]);
            }
        }
        
        /* search the sort map */
        foreach ($this->api_sort_map as $key => $val)
        {
            if (array_key_exists($key, static::$api_joins)) {
                $this->append_to_set($js, static::$api_joins[$key]);
            }
        }
        
        /* go through the related endpoints, and do any extra joins specific to that
           related endpoint */
        /* TODO - do the recursive join set building */
        
        $this->api_join_set = $js;
    }

    public function api_build_select()
    {
        $select = '';
    
        /* build the select statement */
        foreach ($this->api_cur_fields as $ep => $fields)
        {
            /* get the table prefix from the class */
            if ($ep == 'self') {
                $prefix = static::API_PREFIX;
            }
            else {
                $class = static::$api_rel_ep[$ep];
                $prefix = $class::API_PREFIX;
            }
            
            /* go through the fields now */
            foreach ($fields as $field)
            {
                if (is_string($field))
                {
                    /* we might have already provided an alias */
                    if (strpos($field, ' as ') === false)
                    {
                        $field_name = substr($field, strpos($field, '.') + 1);
                        $select .= $field . " as {$prefix}{$field_name}, ";
                    }
                    else {
                        $select .= $field.', ';
                    }
                }
                else
                {
                    foreach ($field as $f)
                    {
                        /* we might have already provided an alias */
                        if (strpos($f, ' as ') === false)
                        {
                            $field_name = substr($f, strpos($f, '.') + 1);
                            $select .= $f . " as {$prefix}{$field_name}, ";
                        }
                        else {
                            $select .= $f.', ';
                        }
                    }
                }
            }
        }
        
        return substr($select, 0, -2);  
    }
    
    public function api_build_where()
    {
        $where = '';
        
        foreach ($this->api_where_map as $key => $val)
        {
            $where .= "{$val[0]}";
            if (count($val) == 2) {
                $where .= " = {$val[1]}";
            }
            else {
                $where .= ' in (' . implode(', ', array_slice($val, 1)) . ')';
            }
            
            $where .= ' AND ';
        }
        
        return substr($where, 0, -5);
    }
    
    public function api_build_order_by()
    {
        $order = '';
        
        foreach ($this->api_sort_map as $key => $val) {
            $order .= "{$val[0]} {$val[1]}, ";
        }
        
        return substr($order, 0, -2);
    }
    
    /* these need to be defined by the inheriting class */
    abstract public function api_process_where_value($key, $val);
    public static function api_build_joins($db, $js) {
        throw new \Krak\Exception('api_build_joins needs to be implemented');
    }
    public static function api_build_partial_result($fields, $obj) {
        throw new \Krak\Exception('api_build_partial_result needs to be implemented');
    }
}
