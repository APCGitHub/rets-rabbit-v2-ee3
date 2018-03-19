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
        ee()->load->library('Rr_v2_cache', null, 'Rr_cache');
        ee()->load->library('Rr_v2_property_service', null, 'Rr_properties');
        ee()->load->model('rets_rabbit_v2_config', 'Rr_config');
        ee()->load->model('rets_rabbit_v2_server', 'Rr_server');
        ee()->load->model('rets_rabbit_v2_search', 'Rr_search');

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
        ee()->load->library('tags/Rr_v2_properties_tag', null, 'Rr_properties_tag');
        ee()->load->library('Rr_v2_view_data_service', null, 'Rr_properties_view');

        //Parse template params
        ee()->Rr_properties_tag->parseParams();

        //Convert params to search terms
        $params = ee()->Rr_properties_tag->toApiParams();
        $transformer = new Property_transformer;

        //See if short code supplied
        if(ee()->Rr_properties_tag->short_code) {
            $serverId = ee()->Rr_server->getByShortCode($this->siteId, ee()->Rr_properties_tag->short_code);

            if(!$serverId) {
                ee()->output->fatal_error("Could not find a server having short code: " . ee()->Rr_properties_tag->short_code, 404);
            }

            if(isset($params['$filter']) && strlen($params['$filter'])) {
                $params['$filter'] .= ' and server_id eq ' . $serverId;
            } else {
                $params['$filter'] = "server_id eq $serverId";
            }
        } else if (ee()->Rr_properties_tag->all != 'y' && ee()->Rr_properties_tag->all != 'yes') {
            // If not fetching all then only get for the default server(s)
            $defaultServers = ee()->Rr_server->getDefaultsForSiteId($this->siteId);

            if(count($defaultServers)) {
                $sq = "(";
                $ors = array();

                foreach($defaultServers as $s) {
                    $ors[] = "server_id eq {$s->server_id}";
                }

                $sq .= implode(' or ', $ors);
                $sq .= ")";
                
                if(isset($params['$filter']) && strlen($params['$filter'])) {
                    $params['$filter'] .= " and $sq";
                } else {
                    $params['$filter'] = $sq;
                }
            }
        }

        $_params = array();

        //Remove params with empty values
        foreach($params as $k => $v) {
            if(!empty($v) && !is_null($v)) {
                $_params[$k] = $v;
            }
        }

        $params = $_params;

        //Generate cache key
        $cacheKey = hash('sha256', serialize($params));

        //Set the view data props
        $data = array();
        $cond = array(
            'has_results'   => 'TRUE',
            'has_error'     => 'FALSE'
        );

        //See if we are caching
        if(ee()->Rr_properties_tag->cache == 'y' || ee()->Rr_properties_tag->cache == 'yes') {
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
                $d = $res->getResponse();

                if(isset($d['@retsrabbit.total_results'])) {
                    $t = $d['@retsrabbit.total_results'];
                    $transformer->totalRecords = $t;
                }

                $data = $res->getResponse()['value'];

                ee()->Rr_cache->set($cacheKey, $data, ee()->Rr_properties_tag->cache_duration);
            }
        } else {
            $transformer->totalRecords = count($data);
        }

        if(empty($data)) {
            $cond['has_results'] = 'FALSE';
        }

        //Massage the data for view consumption
        $resources = new Collection($data, $transformer);
        $viewData = $this->fractal->createData($resources)->toArray();

        return ee()->Rr_properties_view
            ->setVariables($viewData)
            ->stripTags(ee()->Rr_properties_tag->strip_tags)
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
        ee()->load->library('tags/Rr_v2_property_tag', null, 'Rr_property_tag');
        ee()->load->library('Rr_v2_view_data_service', null, 'Rr_property_view');

        //Parse template params
        ee()->Rr_property_tag->parseParams();

        //Convert params to search terms
        $params = ee()->Rr_property_tag->toApiParams();

        //See if short code supplied
        if(ee()->Rr_property_tag->short_code) {
            $serverId = ee()->Rr_server->getByShortCode($this->siteId, ee()->Rr_property_tag->short_code);

            if(!$serverId) {
                ee()->output->fatal_error("Could not find a server having short code: " . ee()->Rr_property_tag->short_code, 404);
            }

            if(isset($params['$filter']) && strlen($params['$filter'])) {
                $params['$filter'] .= ' and server_id eq ' . $serverId;
            } else {
                $params['$filter'] = "server_id eq $serverId";
            }
        }
        
        $cacheKey = md5(ee()->Rr_property_tag->mls_id) . serialize($params);
        $cacheKey = hash('sha256', $cacheKey);

        //Set the view data props
        $data = array();
        $cond = array(
            'has_results'   => 'TRUE',
            'has_error'     => 'FALSE'
        );

        //Check if caching results
        if(ee()->Rr_property_tag->cache == 'y' || ee()->Rr_property_tag->cache == 'yes') {
            $data = ee()->Rr_cache->get($cacheKey);
        }

        if(is_null($data) || !$data) {
            //Hit the API for data
            $res = ee()->Rr_properties->find(ee()->Rr_property_tag->mls_id, $params);

            if(!$res->didSucceed()) {
                $cond['has_results'] = 'FALSE';
                $cond['has_error'] = 'TRUE';
            } else {
                $data = $res->getResponse();

                ee()->Rr_cache->set($cacheKey, $data, ee()->Rr_property_tag->cache_duration);
            }
        }

        if(empty($data)) {
            $cond['has_results'] = 'FALSE';
        }

        //Massage the data for view consumption
        $resources = new Item($data, new Property_transformer);
        $viewData = array($this->fractal->createData($resources)->toArray());

        return ee()->Rr_property_view
            ->setVariables($viewData)
            ->stripTags(ee()->Rr_property_tag->strip_tags)
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
        $searchAll = ee()->TMPL->fetch_param('all', '');
        $actionUrl = ee()->functions->fetch_action_id(RETS_RABBIT_V2_NAME, 'run_search');

        $hiddenFields = array(
            'ACT'           => $actionUrl,
            'results_path'  => $resultsPath,
        );

        if($shortCode) {
            $hiddenFields['short_code'] = $shortCode;
        }

        if($searchAll) {
            $hiddenFields['all'] = $searchAll;
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
        ee()->load->library('Rr_v2_form_service', null, 'Rr_forms');

        $params = array();

        if(isset($_POST)) {
            $resoParams = ee()->Rr_forms->toReso($_POST);

            $data = array(
                'params' => $resoParams,
                'site_id' => $this->siteId
            );

            // Check if doing a server scope by short code first
            if(isset($_POST['short_code']) && $_POST['short_code']) {
                $data['short_code'] = $_POST['short_code'];
            } elseif(isset($_POST['all']) && $_POST['all']) {
                $data['all'] = $_POST['all'];
            }

            ee()->Rr_search->insert($data);

            $resultsPath = $_POST['results_path'];
            preg_match_all("/:([^\)]*):/", $resultsPath, $matches);

            if(sizeof($matches) == 2) {
                $match = trim($matches[0][0]);

                if($match == ':search_id:') {
                    $resultsPath = str_replace($matches[0][0], ee()->Rr_search->id, $resultsPath);
                } else {
                    ee()->output->fatal_error('You must use :search_id: in your results path as the target search id token. You supplied the following token: ' . $match, 500);
                }
            } else {
                if(substr($resultsPath, -1) !== '/') {
                    $resultsPath .= '/';
                }

                $resultsPath .= ee()->Rr_search->id;
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
        ee()->load->library('tags/Rr_v2_search_results_tag', null, 'Rr_search_results_tag');
        ee()->load->library('Rr_v2_view_data_service', null, 'Rr_search_results_view');

        $transformer = new Property_transformer;

        //Parse template params
        ee()->Rr_search_results_tag->parseParams();

        $searchId = ee()->Rr_search_results_tag->search_id;
        $pagination = ee()->pagination->create();

        //Remove pagination from template
        ee()->TMPL->tagdata = $pagination->prepare(ee()->TMPL->tagdata);

        //Fetch the search query from the DB
        ee()->Rr_search->get($searchId);

        if(!ee()->Rr_search->id) {
            ee()->output->fatal_error('We could not find a search.', 404);
        }

        //Search Params
        $params = ee()->Rr_search->params;
        $overrideParams = ee()->Rr_search_results_tag->toApiParams();
        $mergedParams = array_merge($params, $overrideParams);
        $params = array();

        //Remove params with empty values
        foreach($mergedParams as $k => $v) {
            if(!empty($v) && !is_null($v)) {
                $params[$k] = $v;
            }
        }


        if(ee()->Rr_search->short_code) {
            $serverId = ee()->Rr_server->getByShortCode($this->siteId, ee()->Rr_search->short_code);

            if(!$serverId) {
                ee()->output->fatal_error("Could not find a server having short code: " . ee()->Rr_search->short_code, 404);
            }

            if(isset($params['$filter']) && strlen($params['$filter'])) {
                $params['$filter'] .= ' and server_id eq ' . $serverId;
            } else {
                $params['$filter'] = "server_id eq $serverId";
            }
        }

        //Count Params
        $countParams = array(
            '$select' => ee()->Rr_search_results_tag->getCountMethod(),
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

        //Render the view if the pagination failed for some reason
        if(is_null($total) || !$total) {
            $cond['has_results'] = 'FALSE';
            $cond['has_error'] = 'TRUE';

             //Massage the data for view consumption
            $resources = new Collection($data, $transformer);
            $viewData = $this->fractal->createData($resources)->toArray();

            return ee()->Rr_search_results_view
                ->paginate($pagination)
                ->setVariables($viewData)
                ->stripTags(ee()->Rr_search_results_tag->strip_tags)
                ->setConditionals($cond)
                ->process($cond['has_results']);
        }

        //Set the total records count for the transformer
        $transformer->totalRecords = $total;
        
        // Build paginator
        if($pagination->paginate === TRUE) {
            //Build the pagination
            $pagination->build($total, ee()->Rr_search_results_tag->per_page);

            
            //Get the current page
            $currentPage = $pagination->current_page;

            //Set the offset based on the current page
            if($currentPage > 1) {
                $params['$skip'] = ($currentPage - 1) * $params['$top'];
            }
        }

        //Check if caching results
        if(ee()->Rr_search_results_tag->cache == 'y' || ee()->Rr_search_results_tag->cache == 'yes') {
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

                ee()->Rr_cache->set($searchCacheKey, $data, ee()->Rr_search_results_tag->cache_duration);
            }
        }

        if(empty($data)) {
            $cond['has_results'] = 'FALSE';
        }

        //Massage the data for view consumption
        $resources = new Collection($data, $transformer);
        $viewData = $this->fractal->createData($resources)->toArray();

        return ee()->Rr_search_results_view
            ->paginate($pagination)
            ->setVariables($viewData)
            ->stripTags(ee()->Rr_search_results_tag->strip_tags)
            ->setConditionals($cond)
            ->process($cond['has_results']);
    }
}
