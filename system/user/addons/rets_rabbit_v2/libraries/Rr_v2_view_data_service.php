<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rr_v2_view_data_service
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
     * @var  bool
     */
    private $paginator = null;

    /**
     * @var null
     */
    private $totalRecords = null;

    /**
     * @var null
     */
    private $perPage = null;

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
    public function stripTags($strip = 'y')
    {
        $this->stripTags = ($strip == 'y' || $strip == 'yes');

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
     * Set the total items and the per page.
     * 
     * @param  int $total  
     * @param  int $perPage
     * @return $this
     */
    public function paginate(&$paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Process the template data and fill in variables
     *
     * @param  boolean $hasResults
     * @return mixed
     */
    public function process($hasResults = 'FALSE')
    {
        $tagdata = ee()->TMPL->tagdata;

        if($this->stripTags)
            $this->variables = $this->stripVariableTags($this->variables);

        if(!is_null($this->conditionals) && !empty($this->conditionals)) {
            $tagdata = ee()->functions->prep_conditionals($tagdata, $this->conditionals);
        }

        if($hasResults === 'TRUE')
            $output = ee()->TMPL->parse_variables($tagdata, $this->variables);
        else
            $output = ee()->TMPL->no_results();

        //Set output
        ee()->TMPL->tagdata = $output;

        //Render pagination if markup requires it
        if($this->paginator && $this->paginator->paginate === TRUE) {
            //Set final output with pagination
            ee()->TMPL->tagdata = $this->paginator->render(ee()->TMPL->tagdata);
        }

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
