<?php

namespace Anecka\RetsRabbit\Core\Query;


class QueryBuilder
{
	/**
	 * Array of where clauses.
	 * 
	 * @var Array
	 */
	public $wheres = array();

	/**
	 * Array of order by clauses.
	 * 
	 * @var Array
	 */
	public $orders = array();

    /**
     * Array of selects
     * 
     * @var array
     */
    public $selects = array();

    /**
     * @var integer
     */
    public $limit;

    /**
     * @var integer
     */
    public $offset;

	/**
	 * Data structure for the clause value bindings.
	 * 
	 * @var array
	 */
	protected $bindings = array(
        'where' => array(),
    );

	/**
	 * Available comparison operators.
	 * 
	 * @var array
	 */
    public $operators = array(
        'eq',
        'lt',
        'gt',
        'le',
        'ge',
        'ne',
        'contains',
        'endswith',
        'startswith',
        'between',
    );

    /**
     * Processes the query builder into an ODATA query string.
     * 
     * @var QueryProcessor
     */
    private $processor;

    /**
     * Constructor
     */
    public function __construct()
    {
    	$this->processor = new QueryProcessor($this);
    }

    /**
     * Building a where clause.
     * 
     * @param  string 	$field
     * @param  string 	$operator
     * @param  string 	$value
     * @param  string 	$modifier
     * @param  string 	$boolean
     * @return $this
     */
	public function where($field, $operator = null, $value = null, $modifier = null, $boolean = 'and')
	{
		//If field is a function then the caller is trying to create a nested
		//statement. Add to the where stack and immediately return.
		if(is_callable($field)){
			return $this->whereNested($field, $boolean);
		}

		//If the operator is not found in the valid list of operators then
		//we can assume the developer is using the equals shortcut.
		if(!in_array($operator, $this->operators)){
			list($value, $operator) = [$operator, 'eq'];
		}

		$type = 'Basic';

		$this->wheres[] = compact('type', 'field', 'operator', 'value', 'modifier', 'boolean');
       
        $this->addBinding($value, 'where');

		return $this;
	}

	/**
	 * Build an or where statement. 
	 * 
	 * @param  string 	$field
     * @param  string 	$operator
     * @param  string 	$value
     * @param  string 	$modifier
	 * @return $this
	 */
	public function orWhere($field, $operator = null, $value = null, $modifier = null)
	{
		$this->where($field, $operator, $value, $modifier, 'or');

		return $this;
	}

	/**
	 * Add a where between statement to the query. 
	 * 
	 * @param  string  $field   
	 * @param  array   $values  
	 * @param  string  $boolean 
	 * @param  boolean $not     
	 * @return $this
	 */
	public function whereBetween($field, array $values, $boolean = 'and', $not = false)
    {
        $type = 'Between';

        $this->wheres[] = compact('field', 'type', 'boolean', 'not');

        $this->addBinding($values, 'where');

        return $this;
    }

    /**
     * Add an order by clause to the query.
     * 
     * @param  string $field    
     * @param  string $direction
     * @return $this
     */
    public function orderBy($field, $direction = 'asc')
    {
        $this->orders[] = array(
            'field' => $field,
            'direction' => strtolower($direction) == 'desc' ? 'desc' : 'asc',
        );

        return $this;
    }

    /**
     * Skip {$skip} rows
     * 
     * @param  integer $skip
     * @return $this
     */
    public function skip($skip = 0)
    {
        if($skip >= 0) {
            $this->offset = $skip;
        }

        return $this;
    }

    /**
     * Limit the number of results returned
     * 
     * @param  integer $limit
     * @return $this
     */
    public function limit($limit = 0)
    {

        if($limit >= 0) {
            $this->limit = $limit;
        }

        return $this;
    }

    /**
     * Build selects
     * 
     * @param  array
     * @return $this
     */
    public function select($columns = array())
    {
        $this->selects = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    /**
     * Add a nested where statement to the query.
     * 
     * @param  function $callback 
     * @param  string 	$boolean  
     * @return $this
     */
	public function whereNested($callback, $boolean = 'and')
	{
		$query = new self();

        call_user_func($callback, $query);

        return $this->addNestedWhereQuery($query, $boolean);
	}

	/**
	 * Add another query builder as the nested where
	 * @param 	QueryBuilder 	$query  
	 * @param 	string 			$boolean
	 * @return  $this
	 */
	public function addNestedWhereQuery($query, $boolean = 'and')
    {
        if (count($query->wheres)) {
            $type = 'Nested';

            $this->wheres[] = compact('type', 'query', 'boolean');

            $this->addBinding($query->getBindings(), 'where');
        }

        return $this;
    }

    /**
     * Add a binding to the query.
     * 
     * @param 	any 	$value
     * @param 	string 	$type 
     * @return 	$this
     */
	public function addBinding($value, $type = 'where')
    {
        if (is_array($value)) {
        	//Remove other keys which don't belong if not matching {$type}
        	$keys = array_keys($this->bindings);
        	foreach($keys as $key){
        		if(array_key_exists($key, $value)){
        			if($key !== $type){
        				unset($value[$key]);
        			}
        		}
        	}

            $this->bindings[$type] = array_values(array_merge($this->bindings[$type], $value));
        } else {
            $this->bindings[$type][] = $value;
        }

        return $this;
    }

    /**
     * Get the current bindings.
     * 
     * @return array
     */
    public function getBindings()
    {
    	return $this->bindings;
    }

    /**
     * Build the ODATA query string from the builder.
     * 
     * @return string
     */
    public function get()
    {
    	$results = $this->processor->build();

    	return $results;
    }
}