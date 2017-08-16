<?php

class Rets_rabbit_config extends CI_Model
{
    public $id;
    public $site_id;
    public $client_id = '';
    public $client_secret = '';
    public $api_endpoint = '';
    public $log_searches = false;

    /**
     * Config constructor
     */
    public function __construct()
    {
        parent::__construct();

        ee()->load->library('encrypt');
    }

    /**
     * Fetch a single config by id
     *
     * @param  integer $id
     * @return void
     */
    public function getById($id = 0)
    {
        $query = ee()->db->get_where('rets_rabbit_v2_configs', array('id' => $id));

        if($query->num_rows() == 1) {
            $this->setResult($query->row());
        }
    }

    /**
     * Fetch a single config by site_id
     *
     * @param  integer $siteId
     * @return void
     */
    public function getBySiteId($siteId = 0)
    {
        $query = ee()->db->get_where('rets_rabbit_v2_configs', array('site_id' => $siteId));

        if($query->num_rows() == 1) {
            $this->setResult($query->row());
        }
    }

    /**
     * Create a new config record.
     *
     * @param  array $data
     * @return void
     */
    public function insert($data) {
        $this->prepSave($data);

        ee()->db->insert('rets_rabbit_v2_configs', $this);
        $this->id = ee()->db->insert_id();
    }

    /**
     * Update a config record.
     *
     * @param  array $data
     * @param  int|string $id
     * @return void
     */
    public function update($data, $id) {
        $this->prepSave($data);
        $this->id = $id;

        ee()->db->update('rets_rabbit_v2_configs', $this, array('id' => $id));
    }

    /**
     * Prepare the model for saving to the DB
     *
     * @param  array  $data
     * @return void
     */
    private function prepSave($data = array()) {
        $this->site_id = $data['site_id'];
        $this->client_id = ee()->encrypt->encode($data['client_id']);
        $this->client_secret = ee()->encrypt->encode($data['client_secret']);
        $api_endpoint = $data['api_endpoint'];
        if($api_endpoint != "" && substr($api_endpoint, -1) === '/')
            $api_endpoint = substr($api_endpoint, 0, strlen($api_endpoint) - 2);

        $this->api_endpoint = $api_endpoint;
        $this->log_searches = $data['log_searches'];
    }

    /**
     * Set the model from the DB
     *
     * @param object $model
     * @return void
     */
    private function setResult($model)
    {
        $this->id = $model->id;
        $this->site_id = $model->site_id;
        $this->client_id = ee()->encrypt->decode($model->client_id);
        $this->client_secret = ee()->encrypt->decode($model->client_secret);
        if(!empty($model->api_endpoint)) {
            $this->api_endpoint = $model->api_endpoint;
        }
        $this->log_searches = $model->log_searches;
    }
}
