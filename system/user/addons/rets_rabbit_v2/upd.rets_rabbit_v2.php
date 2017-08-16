<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
* Rets_rabbit Class
*
* @package     rets_rabbit_v2
* @author      Andrew Clinton contact@anecka.com
* @copyright   Copyright (c) 2017, Andrew Clinton
* @link        http://retsrabbit.com
* @license		Creative Commons, Attribution-NoDerivatives 4.0
* 				http://creativecommons.org/licenses/by-nd/4.0/legalcode
*/

require PATH_THIRD . "rets_rabbit_v2/config.php";

class Rets_rabbit_v2_upd
{
    public $version = RETS_RABBIT_V2_VERSION;

    public function __construct()
    {
        ee()->load->dbforge();
    }

    public function install()
    {
        $data = array(
            'module_name' => RETS_RABBIT_V2_NAME,
            'module_version' => $this->version,
            'has_cp_backend' => 'y',
            'has_publish_fields' => 'n'
        );

        ee()->db->insert('modules', $data);

        $this->installActions();
        $this->createTables();

        return TRUE;
    }

    public function uninstall()
    {
        ee()->db->select('module_id');

        $query = ee()->db->get_where('modules', array('module_name' => RETS_RABBIT_V2_NAME));

        ee()->db->where('module_id', $query->row('module_id'));
        ee()->db->delete('modules');

        ee()->db->where('class', RETS_RABBIT_V2_NAME);
        ee()->db->delete('actions');

        ee()->dbforge->drop_table('rets_rabbit_v2_configs');
        ee()->dbforge->drop_table('rets_rabbit_v2_searches');
        ee()->dbforge->drop_table('rets_rabbit_v2_servers');

        return TRUE;
    }

    public function update($current = '')
    {
        if ($current == '' OR $current == $this->version) {
            return FALSE;
        }

        return TRUE;
    }

    /*
    |-----------------------------------
    |   Private Helper Methods
    |-----------------------------------
     */

    /**
     * Install plugin actions
     *
     * @return void
     */
    private function installActions()
    {
        $actions = array(
            array(
                'class'  => RETS_RABBIT_V2_NAME,
                'method' => 'run_search'
            ),
        );

        ee()->db->insert_batch('actions', $actions);
    }

    /**
     * Create all of the tables
     *
     * @return void
     */
    private function createTables()
    {
        $this->createConfigsTable();
        $this->createServersTable();
        $this->createSearchesTable();
    }

    /**
     * Create the configs tables
     *
     * @return void
     */
    private function createConfigsTable()
    {
        $fields = array(
            'id'                => array('type' => 'int', 'constraint' => '10', 'unsigned' => true, 'auto_increment' => true),
            'site_id'           => array('type' => 'int', 'constraint' => '10', 'unsigned' => true),
            'client_id'         => array('type' => 'varchar', 'constraint' => '250'),
            'client_secret'     => array('type' => 'varchar', 'constraint' => '250'),
            'api_endpoint'      => array('type' => 'varchar', 'constraint' => '1000', 'null' => true),
            'log_searches'      => array('type' => 'tinyint', 'constraint' => '10', 'unsigned' => true),
        );

        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('id', true);

        ee()->dbforge->create_table('rets_rabbit_v2_configs');
    }

    /**
     * Create the servers table
     * ]
     * @return void
     */
    private function createServersTable()
    {
        $fields = array(
            'id'            => array('type' => 'int', 'constraint' => '10', 'unsigned' => true, 'auto_increment' => true),
            'site_id'       => array('type' => 'int', 'constraint' => '10', 'unsigned' => true),
            'short_code'    => array('type' => 'varchar', 'constraint' => '250', 'null' => true),
            'server_id'     => array('type' => 'int', 'constraint' => '10', 'unsigned' => true, 'default' => 0),
            'is_default'    => array('type' => 'int', 'constraint' => '10', 'unsigned' => true, 'default' => 0),
            'name'          => array('type' => 'varchar', 'constraint' => '250', 'null' => true),
        );

        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('id', true);

        ee()->dbforge->create_table('rets_rabbit_v2_servers');
    }

    /**
     * Create the searches tables
     *
     * @return void
     */
    private function createSearchesTable() {
        $fields = array(
            'id'		     => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'site_id'        => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE),
            'result_page'    => array('type' => 'varchar', 'constraint' => '2000'),
            'searched_at'	 => array('type' => 'datetime', 'null' => true),
            'short_code'     => array('type' => 'varchar', 'constraint' => '250', 'null' => TRUE),
        );

        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('id', TRUE);

        ee()->dbforge->create_table('rets_rabbit_v2_searches');
    }
}
