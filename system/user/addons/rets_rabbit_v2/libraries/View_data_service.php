<?php

class View_data_service
{
    /**
     * Data variables
     * @var array
     */
    private $variables = array();

    /**
     * View conditionals
     *
     * @var array
     */
    private $conditionals = array();

    /**
     * Strip tags from field values
     *
     * @var bool
     */
    private $stripTags = false;

    /**
     * Constructor
     */
    public function __construct()
    {}

    /**
     * Set the view variables
     *
     * @param array $data
     */
    public function setVariables($data = array())
    {
        $this->variables = $data;

        return $this;
    }

    /**
     * Set the strip tags flag to true
     *
     * @return $this
     */
    public function stripTags($strip = true)
    {
        $this->stripTags = $strip;

        return $this;
    }

    /**
     * Set the view conditionals
     *
     * @param array $conds
     */
    public function setConditionals($conds = array())
    {
        $this->conditionals = $conds;

        return $this;
    }

    /**
     * Process the template data and fill in variables
     *
     * @param  boolean $hasResults
     * @return mixed
     */
    public function process($hasResults = false)
    {
        $tagdata = ee()->TMPL->tagdata;

        if($this->stripTags)
            $this->variables = $this->stripVariableTags($this->variables);

        if(!is_null($this->conditionals) && !empty($this->conditionals)) {
            $tagdata = ee()->functions->prep_conditionals($tagdata, $this->conditionals);
        }

        if($hasResults)
            $output = ee()->TMPL->parse_variables($tagdata, $this->variables);
        else
            $output = ee()->TMPL->no_results();

        ee()->TMPL->tagdata = $output;

        return ee()->TMPL->tagdata;
    }

    /*
    |----------------------------------------
    |       Private Methods
    |----------------------------------------
     */

    /**
     * Strip tags from the variables
     *
     * @return void
     */
    private function stripVariableTags($variables = array())
    {
        foreach($variables as $k => $v) {
            if(is_array($v)) {
                $variables[$k] = $this->stripVariableTags($v);
            } else {
                $variables[$k] = strip_tags($v);
            }
        }

        return $variables;
    }
}
