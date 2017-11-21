<?php

class Rets_rabbit_search extends CI_Model
{
    public $id, 
    public $site_id;
    public $params;
    public $searched_at;
    public $short_code;

    public function __construct()
    {
    	parent::__construct();
    }

    /**
     * Fetch a search by id
     * 
     * @param  int $id
     * @return array|null
     */
    public function get($id) {
        $query = ee()->db->get_where('rets_rabbit_v2_searches', array('id' => $id));
        
        if($query->num_rows() == 1) 
        	$this->setResult($query->row());

        return null;
    }

    /**
     * Fetch searches by site_id
     * 
     * @param  int $site_id
     * @return array|null
     */
    public function getBySiteId($site_id) {
        $query = ee()->db->get_where('rets_rabbit_v2_searches', array('site_id' => $site_id));

        if($query->num_rows() == 1) 
        	$this->setResult($query->row());

        return null;
    }

    public function insert($data) {
        $this->prepSave($data);

        ee()->db->insert('rets_rabbit_v2_searches', $this);

        $this->id = ee()->db->insert_id();
    }

    public function update($data, $id) {
        $this->prepSave($data);
        $this->id = $id;

        ee()->db->update('rets_rabbit_v2_searches', $this, array('id' => $id));
    }

    public function clearOldSearches() {
        ee()->db->delete('rets_rabbit_v2_searches', array('search_date' => "< DATE_SUB(NOW(), 7 DAYS)"));
    }

    private function prepSave($data) {
        $this->site_id = $data['site_id'];
        $this->params = json_encode($data['params']);
        $this->searched_at = ee()->localize->now;
        $this->short_code = $data['short_code'];
    }

    private function setResult($row) {
        $this->id = $row->id;
        $this->site_id = $row->site_id;
        $this->params = json_decode($row->params, true);
        $this->searched_at = $row->search_date;
        $this->short_code = $row->short_code;
    }
}
