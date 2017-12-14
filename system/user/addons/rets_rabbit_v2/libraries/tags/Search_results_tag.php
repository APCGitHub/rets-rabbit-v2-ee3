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
    	'cache'         => false,
        'strip_tags'    => false,
        'cache_duration'=> 3600,
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
    protected $map = array();

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
    protected $apiParams = array();

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

            $v = ee()->TMPL->fetch_param($key);

            $params[$key] = $v;
        }

        $this->params = $params;
    }
}