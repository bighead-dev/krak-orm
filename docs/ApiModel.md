# ApiModel

Krak Api Model is a special class that makes it easy to define and create RESTful api query and data model.

## Main Idea

ApiModel makes it very easy to dynamically take parameters, filters, and sorts from input, and turn it into a result set from the database.

ApiModel manages 5 aspects of a specific resource: *fields* to be shown in the result set, *where* filters to be applied to the database query, *sort* parameters to also be applied to the database, *join tables* to be joined on the query, and *related endpoints*. Each of these components are stored in static properties in the ApiModel subclass.

## Inputs

Before we get to nitty gritty of the ApiModel, let's look at what input parameters look like and what kind of responses they would return.

````php
/* example 1 */
$input = [
    'fields'        => 'id,full_name,birth_month',
    'id'            => [1,2,3,5],
    'group'         => ['admin', 'user'],
    'birth_month'   => 'june',
    'sort'          => 'last_name,fisrt_name-desc',
];

/* these input parameters mean, show only the id, full_name, and birth_moth in the result
   set. Filter results where id in (1,2,3,4,5) and where group in ('admin', 'user') and where birth_month = 'june' and sort by last_name ascending then first_name descending. */
   
/* example 2 */
$input = [
    'fields'    => [
        'self'  => 'id,full_name',
        'house' => 'id,location,sq_feet',
        'cars'  => 'id,make,model,year',
    ]
];

/* these input parameters would create a result set like the following
[
    {
        "id"        : 1,
        "full_name" : "RJ Garcia",
        "house"     : {
            "id"    : "1",
            "location"  : "Chico, CA",
            "sq_feet"   : 3000.0,
        },
        "cars"  : [
            {
                "id"    : 1,
                "make"  : "GMC",
                "model" : "Sierra",
                "year"  : 2003
            },
            ...
        ]
    },
    ...
]
*/

/* example 3 */
$input = [];

/* an empty array will return all of the fields for all of the related endpoints and the current endpoint. */
````

An ApiModel essentially looks at the fields for the current endpoint, and builds the select portion of sql. Then it builds the where portion of the sql according to the api_wheres. Then it builds the sort portion of the query. If there is a related endpoint, it builds the necessary select string for that endpoint also and concatenates it to the current select sql. You can select fields from an endpoint and it's related endpoints, but you can only where and sort on the current endpoint. Wheres and sorts on related endpoints won't be evaluated.

### `api_fields`

````php
class User extends Krak\Model\Api
{
    /* the prefix to be applied to every column alias for the fields in this endpoint */
    const API_PREFIX = 'u_';
    
    /* define the fields */
    public static $api_fields = [
        /* key => val, key is the string that actually shows up in the result set,
           the val is just a portion of sql select code */
        'id'            => 'users.id',
        'full_name'     => ['users.first_name', 'users.last_name'],
        'birth_month'   => 'MONTH(users.birth_date) as u_birth_month',
        'country'       => 'countries.name as u_country'
    ];
````

The `api_fields` are used to map fields in the api result set to an actual sql query. The values for the keys can either be a string of sql, or an array of strings for the sql code.

If the input fields where: `id,full_name,birth_month,country`, then the resulting select sql would be like so `users.id as u_id, users.first_name as u_first_name, users.last_name as u_last_name, MONTH(users.birth_date) as u_birth_month, countries.name as u_country`

The ApiModel will alias all fields with the `API_PREFIX` except when there already is an alias in the query like in the example of birth_month and country.

### `api_wheres`

````php
class User extends Krak\Model\Api
{    
    /* define the where filters */
    public static $api_wheres = [
        /* key => val, key is the actual key used in the input parameters, and val is the sql
           portion of the where sql */
        'id'            => 'users.id',
        'country_id'    => 'users.country_id',
        'country_name'  => 'countries.name',
        'birth_month'   => 'MONTH(users.birth_date)',
        'group'         => 'users.group_id',
    ];
````

When the input parameters specify where filters, ApiModel makes sure that given filter is in the keys of api_wheres array. If it is then it's a valid where, and it will generate the where sql with the matched sql portion.

### `api_process_where_value`

````php
    /*
     * As the API model is processing the input data to find out what
     * what wheres to make on the result set, it calls this function for
     * every where value to allow sanitizing and extra processing
     */
    public function api_process_where_value($key, $val)
    {
        /* key is a key from api_wheres array, val is the string value passed
           in from the input */
        switch ($key)
        {
            case 'id':
            case 'country_id':
                return (int) $val; /* typecast string to int as this will sanitize it */
            case 'group':
                /* valid groups are things like: admin, author, user. In this example,
                   let's assume that Km\Group::slug2id($slug) takes a slug like 'admin' or 'user'
                   and returns the appropriate id via simple lookup array */
                return \Km\Group::slug2id($val);
            default:
                return $this->db->escape($val); /* just normally sanitize strings */
        }
    }
````

...

### `api_sorts`

````php
class User extends Krak\Model\Api
{
    /* define the sort parameters */
    public static $api_sorts = [
        'last_name'     => 'users.last_name',
        'first_name'    => 'users.first_name',
        'birth_date'    => 'users.birth_date',
    ];
}
````

sorts act very similar to wheres. In the sort string of the input parameters i.e `last_name-asc,first_name-desc`, it checks each sort field with the `api_sorts` field, if it's a valid sort, then it uses related sql in the sort portion of the sql query.


### `api_rel_ep`

````php
class User extends Krak\Model\Api
{
    /* define the related endpoints, users have one house and many cars */
    public static $api_rel_ep = [
        'house' => '\Km\House',
        'cars'  => '\Km\Car',
    ];
}
````

Related endpoints are used to share code for building results. ...

### `api_joins`

````php
class User extends Krak\Model\Api
{
    /* define which keys require special joins */
    public static $api_joins = [
        'house'         => 'house',
        'country'       => 'country',
        'country_name'  => 'country',
    ];
}
````

As the ApiModel builds the fields, wheres, and sorts, it compares each key in `api_fields`, `api_wheres`, and `api_sorts` against the keys in `api_joins`, if any of those keys are in the keys of `api_joins`, then it adds the value at key in `api_joins` to a join set, which will be used later in `api_build_joins`.

The purpose of this is that sometimes in the `api_fields`, you need to select a field from a different table, and need to perform a join, and same with wheres, sorts, and related endpoints. `api_joins` is used to build a join set that is used to only join the needed tables for the specific query.

...

### `api_build_joins`

````php
    /* build all of the joins for the sql query */
    public static function api_build_joins($db, $join_set)
    {
        /* $db is an instance to a CI db class
           $join_set is a set of join keys that were added in this query. The keys
           in the join set match the values in $api_joins array. */
        
        if (array_key_exists('house', $join_set)) {
            $db->join(); // join the house table
        }
        if (araray_key_exists('country', $join_set)) {
            $db->join(); // join the country
        }
    }
````

...


## Full Example

````php
<?php

namespace Km;

use Krak;

class User extends Krak\Model\Api
{
    const API_PREFIX = 'u_';
    
    /* define the fields */
    public static $api_fields = [
        'id'            => 'users.id',
        'full_name'     => ['users.first_name', 'users.last_name'],
        'birth_month'   => 'MONTH(users.birth_date) as u_birth_month',
        'country'       => 'countries.name as u_country'
    ];
    
    /* define the where filters */
    public static $api_wheres = [
        'id'            => 'users.id',
        'country_id'    => 'users.country_id',
        'country_name'  => 'countries.name',
        'birth_month'   => 'MONTH(users.birth_date)',
        'group'         => 'users.group_id',
    ];
    
    /* define the sort parameters */
    public static $api_sorts = [
        'last_name'     => 'users.last_name',
        'first_name'    => 'users.first_name',
        'birth_date'    => 'users.birth_date',
    ];
    
    /* define the related endpoints, users have one house and many cars */
    public static $api_rel_ep = [
        'house' => '\Km\House',
        'cars'  => '\Km\Car',
    ];
    
    /* define which keys require special joins */
    public static $api_joins = [
        'house'         => 'house',
        'country'       => 'country',
        'country_name'  => 'country',
    ];
    
    /*
     * As the API model is processing the input data to find out what
     * what wheres to make on the result set, it calls this function for
     * every where value to allow sanitizing and extra processing
     */
    public function api_process_where_value($key, $val)
    {
        /* key is a key from api_wheres array, val is the string value passed
           in from the input */
        switch ($key)
        {
            case 'id':
            case 'country_id':
                return (int) $val; /* typecast string to int as this will sanitize it */
            case 'group':
                /* valid groups are things like: admin, author, user. In this example,
                   let's assume that Km\Group::slug2id($slug) takes a slug like 'admin' or 'user'
                   and returns the appropriate id via simple lookup array */
                return \Km\Group::slug2id($val);
            default:
                return $this->db->escape($val); /* just normally sanitize strings */
        }
    }
    
    /* build all of the joins for the sql query */
    public static function api_build_joins($db, $join_set)
    {
        /* $db is an instance to a CI db class
           $join_set is a set of join keys that were added in this query. The keys
           in the join set match the values in $api_joins array. */
        
        if (array_key_exists('house', $join_set)) {
            $db->join(); // join the house table
        }
        if (araray_key_exists('country', $join_set)) {
            $db->join(); // join the country
        }
    }
}
````
