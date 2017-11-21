<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
* Rets_rabbit_v2 Module Class
*
* @package     rets_rabbit_v2
* @author      Andrew Clinton contact@anecka.com
* @copyright   Copyright (c) 2017, Andrew Clinton
* @link        http://retsrabbit.com
* @license		Creative Commons, Attribution-NoDerivatives 4.0
* 				http://creativecommons.org/licenses/by-nd/4.0/legalcode
*/

require PATH_THIRD . "rets_rabbit_v2/vendor/autoload.php";
require PATH_THIRD . "rets_rabbit_v2/config.php";

use Anecka\RetsRabbit\Serializers\Rets_rabbit_array_serializer;
use Anecka\RetsRabbit\Transforms\Property_transformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;

class Rets_rabbit_v2
{
    /**
     * Site id for this instance
     *
     * @var int
     */
    private $siteId = 0;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * Constructor
     */
    public function __construct()
    {
        //Load libs and models
        ee()->load->library('Rets_rabbit_cache', null, 'Rr_cache');
        ee()->load->model('Rets_rabbit_config', 'Rr_config');
        ee()->load->model('Rets_rabbit_server', 'Rr_server');
        ee()->load->library('Properties_service', null, 'Rr_properties');

        $this->fractal = new Manager();
        $this->fractal->setSerializer(new Rets_rabbit_array_serializer);
    }

    /**
     * Run a RESO query against the API to fetch properties.
     * 
     * @return array
     */
    public function properties()
    {
        //Load libs
        ee()->load->library('tags/Properties_tag', null, 'Tag');
        ee()->load->library('View_data_service', null, 'View_service');

        //Parse template params
        ee()->Tag->parseParams();

        //Convert params to search terms
        $params = ee()->Tag->toApiParams();
        $cacheKey = hash('sha256', serialize($params));

        //Set the view data props
        $data = array();
        $cond = array(
            'has_results'   => true,
            'has_error'     => false
        );

        //See if we are caching
        if(ee()->Tag->cache) {
            $data = ee()->Rr_cache->get($cacheKey);
        }

        if(is_null($data) || !$data) {
            //Hit the API for data
            $res = ee()->Rr_properties->search($params);

            if(!$res->didSucceed()) {
                $cond['has_results'] = false;
                $cond['has_error'] = true;
            } else {
                $data = $res->getResponse()['value'];

                ee()->Rr_cache->set($cacheKey, $data, ee()->Tag->cache_duration);
            }
        }

        if(empty($data)) {
            $cond['has_results'] = false;
        }

        //Massage the data for view consumption
        $resources = new Collection($data, new Property_transformer);
        $viewData = $this->fractal->createData($resources)->toArray();

        return ee()->View_service
            ->setVariables($viewData)
            ->stripTags(ee()->Tag->strip_tags)
            ->setConditionals($cond)
            ->process($cond['has_results']);
    }

    /**
     * Fetch a single property by msl id.
     * 
     * @return array
     */
    public function property()
    {
        //Load libs
        ee()->load->library('tags/Property_tag', null, 'Tag');
        ee()->load->library('View_data_service', null, 'View_service');

        //Parse template params
        ee()->Tag->parseParams();

        //Convert params to search terms
        $params = ee()->Tag->toApiParams();
        $cacheKey = md5(ee()->Tag->mls_id) . serialize($params);
        $cacheKey = hash('sha256', $cacheKey);

        //Set the view data props
        $data = array();
        $cond = array(
            'has_results'   => true,
            'has_error'     => false
        );

        //Check if caching results
        if(ee()->Tag->cache) {
            $data = ee()->Rr_cache->get($cacheKey);
        }

        if(is_null($data) || !$data) {
            //Hit the API for data
            $res = ee()->Rr_properties->find(ee()->Tag->mls_id, $params);

            if(!$res->didSucceed()) {
                $cond['has_results'] = false;
                $cond['has_error'] = true;
            } else {
                $data = $res->getResponse()['value'];

                ee()->Rr_cache->set($cacheKey, $data, ee()->Tag->cache_duration);
            }
        }

        if(empty($data)) {
            $cond['has_results'] = false;
        }

        //Massage the data for view consumption
        $resources = new Item($data, new Property_transformer);
        $viewData = $this->fractal->createData($resources)->toArray();

        return ee()->View_service
            ->setVariables($viewData)
            ->stripTags(ee()->Tag->strip_tags)
            ->setConditionals($cond)
            ->process($cond['has_results']);
    }

    /**
     * Create a RR search form
     */
    public function search_form()
    {
        ee()->load->helper('form');

        $shortCode = ee()->TMPL->fetch_param("short_code", '');
        $resultsPath = ee()->TMPL->fetch_param('results_path', ee()->uri->uri_string());

        $hiddenFields = array(
            'ACT'            => ee()->functions->fetch_action_id(RETS_RABBIT_V2_NAME, 'run_search'),
            'results_path'    => $resultsPath,
            'short_code'      => $shortCode,
        );

        $formAttrs = array(
            'name'           => 'search_results',
            'id'             => ee()->TMPL->form_id,
            'class'          => ee()->TMPL->form_class,
        );

        $variable = array();
        $tagdata = ee()->TMPL->tagdata;

        $output = form_open('', $formAttrs, $hiddenFields);
        $output .= ee()->TMPL->parse_variables($tagdata, array($variable));
        $output .= "</form>";

        return $output;
    }

    /**
     * Run a search against the RR API.
     * 
     * @description This method acts more like a controller endpoint
     * since EE doesn't support actual controllers.
     */
    public function run_search()
    {
        ee()->load->library('Forms_service', null, 'Forms');
        ee()->load->model('Rets_rabbit_search');

        $params = array();

        if(isset($_POST)) {
            $resoParams = ee()->Forms->toReso($_POST);

            $data = array(
                'params' => $resoParams
            );

            if(isset($_POST['short_code'])) {
                $data['short_code'] = $_POST['short_code'];
            }

            ee()->Rets_rabbit_search->insert($data);
        }
    }

    /**
     * Get search results from the RR API.
     */
    public function search_results()
    {

    }
}
