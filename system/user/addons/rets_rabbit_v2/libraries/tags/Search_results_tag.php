<?php

require PATH_THIRD . "rets_rabbit_v2/libraries/tags/Base_tag.php";

class Search_results_tag extends Base_tag
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
}