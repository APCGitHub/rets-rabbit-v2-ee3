<?php

class Rets_rabbit_server extends CI_Model
{
    public $id = 0;
    public $site_id = null;
    public $short_code = null;
    public $server_id = null;
    public $is_default = false;
    public $name = null;

    /**
     * Constructor for Rets_rabbit_server
     *
     * @param array $attrs
     */
    public function __construct($attrs = array())
    {
        parent::__construct();

        foreach($attrs as $k => $v) {
            if(property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }
    }

    /**
     * Instantiate a new model instance
     *
     * @param  array  $attrs
     * @return Rets_rabbit_server
     */
    public static function create($attrs = array())
    {
        return new static($attrs);
    }

    /**
     * Fetch a single server by id
     *
     * @param  integer $id
     * @return void
     */
    public function getById($id = 0)
    {
        $query = ee()->db->get_where('rets_rabbit_v2_servers', array('id' => $id));

        if($query->num_rows() == 1) {
            $this->setResult($query->row());
        }
    }

    /**
     * Fetch a single server by site_id
     *
     * @param  integer $siteId
     * @return void
     */
    public function getBySiteId($siteId = 0)
    {
        $query = ee()->db->get_where('rets_rabbit_v2_servers', 
            array(
                'site_id' => $siteId
            )
        );
        $servers = array();

        if($query->num_rows() > 0) {
            foreach($query->result() as $row) {
                $s = static::create($row);
                $servers[] = $s;
            }
        }

        return $servers;
    }

    /**
     * Get a server by short code
     * @param  string $siteId   
     * @param  string $shortCode
     */
    public function getByShortCode($siteId = '', $shortCode = '')
    {
        $query = ee()->db->get_where('rets_rabbit_v2_servers', 
            array(
                'site_id' => $siteId, 'short_code' => $shortCode
            )
        );

        if($query->num_rows() >= 1) {
            $row = $query->row();

            return $row->server_id;
        }

        return '';
    }

    /**
     * Create a new servers
     *
     * @param  array $data
     * @return void
     */
    public function insert($data = array()) {
        $this->id = null;
        $this->prepSave($data);

        ee()->db->insert('rets_rabbit_v2_servers', $this);
        $this->id = ee()->db->insert_id();
    }

    /**
     * Update a server record
     * @param  array  $data
     * @return void
     */
    public function update($data = array(), $id) {
        $this->prepSave($data);
        $this->id = $id;
        ee()->db->update('rets_rabbit_v2_servers', $this, array('id' => $id));
    }

    /**
     * Update a server's short code
     * @param  int|string $id
     * @param  string $short_code
     * @return void
     */
    public function updateShortCode($id, $short_code = '')
    {
        $data = array(
            'short_code' => $short_code
        );

        ee()->db->update('rets_rabbit_v2_servers', $data, array('id' => $id));
    }

    /**
     * Mark the default server
     *
     * @param int|string $site_id
     * @param int|string $default
     */
    public function setDefault($site_id, $default)
    {
        $reset_default = array('is_default' => 0);
        $set_default = array('is_default' => 1);

        ee()->db->update('rets_rabbit_v2_servers', $reset_default, array('site_id' => $site_id));
        ee()->db->update('rets_rabbit_v2_servers', $set_default, array('id' => $default));
    }

    /**
     * Prepare the model for saving to the DB
     *
     * @param  array  $data
     * @return void
     */
    private function prepSave($data = array()) {
        foreach($data as $k => $v) {
            $this->{$k} = $v;
        }
    }

    /**
     * Set the model from the DB
     *
     * @param object $model
     * @return void
     */
    private function setResult($model)
    {
        $this->id = $row->id;
        $this->site_id = $row->site_id;
        $this->short_code = $row->short_code;
        $this->server_id = $row->server_id;
        $this->is_default = $row->is_default;
        $this->name = $row->name;
    }
}
