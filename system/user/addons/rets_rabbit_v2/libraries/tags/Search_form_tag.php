<?php

require PATH_THIRD . "rets_rabbit_v2/libraries/tags/Base_tag.php";

class Search_form_tag extends Base_tag
{
	/**
     * The different params this tag can handle
     *
     * @var array
     */
    protected $attrs = array(
        'short_code'    => null,
        'all'           => 'n',
    	'results_path'  => null,
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