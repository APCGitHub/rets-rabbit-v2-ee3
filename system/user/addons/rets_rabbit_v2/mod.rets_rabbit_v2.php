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
        $this->siteId = ee()->config->item('site_id');
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

        //See if short code supplied
        if(ee()->Tag->short_code) {
            $serverId = ee()->Rr_server->getByShortCode($this->siteId, ee()->Tag->short_code);

            if(!$serverId) {
                ee()->output->fatal_error("Could not find a server having short code: " . ee()->Tag->short_code, 404);
            }

            if(isset($params['$filter']) && strlen($params['$filter'])) {
                $params['$filter'] .= ' and server_id eq ' . $serverId;
            } else {
                $params['$filter'] = "server_id eq $serverId";
            }
        }

        //Generate cache key
        $cacheKey = hash('sha256', serialize($params));

        //Set the view data props
        $data = array();
        $cond = array(
            'has_results'   => 'TRUE',
            'has_error'     => 'FALSE'
        );

        //See if we are caching
        if(ee()->Tag->cache) {
            $data = ee()->Rr_cache->get($cacheKey);
        }

        if(is_null($data) || !$data) {
            //Hit the API for data
            $res = ee()->Rr_properties->search($params);

            if(!$res->didSucceed()) {
                $cond['has_results'] = 'FALSE';
                $cond['has_error'] = 'TRUE';
                $data = array();
            } else {
                $data = $res->getResponse()['value'];

                ee()->Rr_cache->set($cacheKey, $data, ee()->Tag->cache_duration);
            }
        }

        if(empty($data)) {
            $cond['has_results'] = 'FALSE';
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

        //See if short code supplied
        if(ee()->Tag->short_code) {
            $serverId = ee()->Rr_server->getByShortCode($this->siteId, ee()->Tag->short_code);

            if(!$serverId) {
                ee()->output->fatal_error("Could not find a server having short code: " . ee()->Tag->short_code, 404);
            }

            if(isset($params['$filter']) && strlen($params['$filter'])) {
                $params['$filter'] .= ' and server_id eq ' . $serverId;
            } else {
                $params['$filter'] = "server_id eq $serverId";
            }
        }
        
        $cacheKey = md5(ee()->Tag->mls_id) . serialize($params);
        $cacheKey = hash('sha256', $cacheKey);

        //Set the view data props
        $data = array();
        $cond = array(
            'has_results'   => 'TRUE',
            'has_error'     => 'FALSE'
        );

        //Check if caching results
        if(ee()->Tag->cache) {
            $data = ee()->Rr_cache->get($cacheKey);
        }

        if(is_null($data) || !$data) {
            //Hit the API for data
            $res = ee()->Rr_properties->find(ee()->Tag->mls_id, $params);

            if(!$res->didSucceed()) {
                $cond['has_results'] = 'FALSE';
                $cond['has_error'] = 'TRUE';
            } else {
                $data = $res->getResponse();

                ee()->Rr_cache->set($cacheKey, $data, ee()->Tag->cache_duration);
            }
        }

        if(empty($data)) {
            $cond['has_results'] = 'FALSE';
        }

        //Massage the data for view consumption
        $resources = new Item($data, new Property_transformer);
        $viewData = array($this->fractal->createData($resources)->toArray());

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
        $actionUrl = ee()->functions->fetch_action_id(RETS_RABBIT_V2_NAME, 'run_search');

        $hiddenFields = array(
            'ACT'           => $actionUrl,
            'results_path'  => $resultsPath,
        );

        if($shortCode) {
            $hiddenFields['short_code'] = $shortCode;
        }

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
        ee()->load->library('logger');
        ee()->load->library('Forms_service', null, 'Forms');
        ee()->load->model('Rets_rabbit_search');

        $params = array();

        if(isset($_POST)) {
            $resoParams = ee()->Forms->toReso($_POST);

            $data = array(
                'params' => $resoParams,
                'site_id' => $this->siteId
            );

            if(isset($_POST['short_code'])) {
                $data['short_code'] = $_POST['short_code'];
            }

            ee()->Rets_rabbit_search->insert($data);

            $resultsPath = $_POST['results_path'];
            preg_match_all("/:([^\)]*):/", $resultsPath, $matches);

            if(sizeof($matches) == 2) {
                $match = trim($matches[0][0]);

                if($match == ':search_id:') {
                    $resultsPath = str_replace($matches[0][0], ee()->Rets_rabbit_search->id, $resultsPath);
                } else {
                    ee()->output->fatal_error('You must use :search_id: in your results path as the target search id token. You supplied the following token: ' . $match, 500);
                }
            } else {
               $resultsPath .= ee()->Rets_rabbit_search->id;
            }
            
            ee()->functions->redirect($resultsPath);
        }
    }

    /**
     * Get search results from the RR API.
     */
    public function search_results()
    {
        ee()->load->library('pagination');
        ee()->load->model('Rets_rabbit_search');
        ee()->load->library('tags/Search_results_tag', null, 'Tag');
        ee()->load->library('View_data_service', null, 'View_service');

        //Parse template params
        ee()->Tag->parseParams();

        $searchId = ee()->Tag->search_id;
        $pagination = ee()->pagination->create();

        //Remove pagination from template
        ee()->TMPL->tagdata = $pagination->prepare(ee()->TMPL->tagdata);

        //Fetch the search query from the DB
        ee()->Rets_rabbit_search->get($searchId);

        if(!ee()->Rets_rabbit_search->id) {
            ee()->output->fatal_error('We could not find a search.', 404);
        }

        //Search Params
        $params = ee()->Rets_rabbit_search->params;
        $overrideParams = ee()->Tag->toApiParams();
        $params = array_merge($params, $overrideParams);

        if(ee()->Rets_rabbit_search->short_code) {
            $serverId = ee()->Rr_server->getByShortCode($this->siteId, ee()->Rets_rabbit_search->short_code);

            if(!$serverId) {
                ee()->output->fatal_error("Could not find a server having short code: " . ee()->Rets_rabbit_search->short_code, 404);
            }

            if(isset($params['$filter']) && strlen($params['$filter'])) {
                $params['$filter'] .= ' and server_id eq ' . $serverId;
            } else {
                $params['$filter'] = "server_id eq $serverId";
            }
        }

        //Count Params
        $countParams = array(
            '$select' => ee()->Tag->getCountMethod(),
            '$filter' => $params['$filter']
        );
        $countError = false;

        //Generate count hash key
        $countCacheKey = hash('sha256', serialize($countParams));

        //Set the view data props
        $data = array();
        $cond = array(
            'has_results'   => 'TRUE',
            'has_error'     => 'FALSE'
        );
        $total = ee()->Rr_cache->get($countCacheKey);

        //Fetch search count
        if(is_null($total) || !$total) {
            //Hit the API for data
            $res = ee()->Rr_properties->search($countParams);

            if(!$res->didSucceed()) {
                $countError = true;
            } else {
                $total = $res->getResponse()['@retsrabbit.total_results'];

                ee()->Rr_cache->set($countCacheKey, $total, 3600);
            }
        }

        //Render the view of the pagination failed for some reason
        if(is_null($total) || !$total) {
            $cond['has_results'] = 'FALSE';
            $cond['has_error'] = 'TRUE';

             //Massage the data for view consumption
            $resources = new Collection($data, new Property_transformer);
            $viewData = $this->fractal->createData($resources)->toArray();

            return ee()->View_service
                ->paginate($pagination)
                ->setVariables($viewData)
                ->stripTags(ee()->Tag->strip_tags)
                ->setConditionals($cond)
                ->process($cond['has_results']);
        }

        if($pagination->paginate === TRUE) {
            //Build the pagination
            $pagination->build($total, ee()->Tag->per_page);

            
            //Get the current page
            $currentPage = $pagination->current_page;

            //Set the offset based on the current page
            if($currentPage > 1) {
                $params['$skip'] = ($currentPage - 1) * $params['$top'];
            }
        }

        //Check if caching results
        if(ee()->Tag->cache) {
            $data = ee()->Rr_cache->get($searchCacheKey);
        }

        //Generate search hash key
        $searchCacheKey = hash('sha256', serialize($params));

        //Fetch search results
        if(is_null($data) || !$data) {
            //Hit the API for data
            $res = ee()->Rr_properties->search($params);

            if(!$res->didSucceed()) {
                $cond['has_results'] = 'FALSE';
                $cond['has_error'] = 'TRUE';
            } else {
                $data = $res->getResponse()['value'];

                ee()->Rr_cache->set($searchCacheKey, $data, ee()->Tag->cache_duration);
            }
        }

        if(empty($data)) {
            $cond['has_results'] = 'FALSE';
        }

        //Massage the data for view consumption
        $resources = new Collection($data, new Property_transformer);
        $viewData = $this->fractal->createData($resources)->toArray();

        return ee()->View_service
            ->paginate($pagination)
            ->setVariables($viewData)
            ->stripTags(ee()->Tag->strip_tags)
            ->setConditionals($cond)
            ->process($cond['has_results']);
    }
}
