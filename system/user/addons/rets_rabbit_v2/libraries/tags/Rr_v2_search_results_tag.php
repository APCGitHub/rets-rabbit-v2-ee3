<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Rr_v2_search_results_tag
{
	/**
     * The different params this tag can handle
     *
     * @var array
     */
    protected $attrs = array(
    	'search_id' 	=> null,
    	'select' 		=> null,
        'orderby'       => null,
        'per_page'      => 12,
    	'cache'         => 'n',
        'strip_tags'    => 'n',
        'cache_duration'=> 3600,
        'count'         => 'estimated'
    );

    /**
     * Final output of the filled in attrs
     *
     * @var array
     */
    protected $params = array();

    /**
     * Map template params to query params
     *
     * @var array
     */
    protected $map = array(
        'select'    => '$select',
        'orderby'   => '$orderby',
        'per_page'  => '$top',
    );

    /**
     * Casts for param types
     *
     * @var array
     */
    protected $casts = array();

    /**
     * Hide these from toArray which is used for building the query params
     *
     * @var array
     */
    protected $apiParams = array('select', 'orderby', 'per_page');

    /**
     * Rules for value setting
     *
     * @var array
     */
    protected $rules = array();

    /**
     * Parse the template params
     *
     * @return void
     */
    public function parseParams()
    {
        $params = array();

        foreach($this->attrs as $k => $_v) {
            $key = $k;

            if(is_integer($k)) {
                $key = $_v;
            }

            $v = trim(ee()->TMPL->fetch_param($key));

            if($key === 'count') {
                if($v !== 'exact') {
                    $v = 'estimated';
                }
            }

            $params[$key] = $v;
        }

        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getCountMethod()
    {
        $count = $this->count;

        if($count == 'exact') {
            return 'total_results';
        }

        return 'estimated_results';
    }

     /**
     * Fetch the parameters as an associative array for the API
     *
     * @return array
     */
    public function toApiParams()
    {
        $data = array();

        foreach($this->params as $k => $v) {
            if(in_array($k, $this->apiParams)) {
                if(in_array($k, array_keys($this->map))) {
                    $k = $this->map[$k];
                }

                $data[$k] = $v;
            }
        }

        return $data;
    }

    /**
     * Return the param value requested
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        if(in_array($key, array_keys($this->params))) {
            return $this->params[$key];
        } else if(in_array($key, array_keys($this->attrs))) {
            return $this->attrs[$key];
        }

        return null;
    }
}