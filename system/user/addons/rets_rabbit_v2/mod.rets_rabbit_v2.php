<?php

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

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use RetsRabbit\ApiService;
use RetsRabbit\Bridges\EEBridge;
use RetsRabbit\Resources\PropertiesResource;

class Rets_rabbit_v2
{
    /**
     * Api Service for RR API
     *
     * @var ApiService
     */
    private $apiService = null;

    /**
     * Site id for this instance
     *
     * @var int
     */
    private $siteId = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        //Load libs and models
        ee()->load->library('Rets_rabbit_cache', null, 'Rr_cache');
        ee()->load->model('Rets_rabbit_config', 'Rr_config');
        ee()->load->model('Rets_rabbit_server', 'Rr_server');
        ee()->load->library('Token_service', null, 'Token');

        $bridge = new EEBridge;
        $bridge->setTokenFetcher(function () {
            return ee()->Rr_cache->get('access_token', true);
        });

        $this->apiService = new ApiService($bridge);
        $this->siteId = ee()->config->item('site_id');

        ee()->Rr_config->getBySiteId($this->siteId);
        ee()->Token->setApiService($this->apiService);

        if($customEndpoint = ee()->Rr_config->api_endpoint) {
            $this->apiService->overrideBaseApiEndpoint($customEndpoint);
        }

        if(!ee()->Token->isValid()) {
            ee()->Token->refresh();
        }
    }

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
            $resource = new PropertiesResource($this->apiService);
            $res = $resource->search($params);

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
        $fractal = new Manager();
        $fractal->setSerializer(new ArraySerializer);
        $resources = new Collection($data, new Anecka\RetsRabbit\Transforms\Property_transformer);
        $viewData = $fractal->createData($resources)->toArray()['data'];

        return ee()->View_service
            ->setVariables($viewData)
            ->stripTags(ee()->Tag->strip_tags)
            ->setConditionals($cond)
            ->process($cond['has_results']);
    }

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
            $resource = new PropertiesResource($this->apiService);
            $res = $resource->single(ee()->Tag->mls_id, $params);

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
        $fractal = new Manager();
        $fractal->setSerializer(new ArraySerializer);
        $resources = new Item($data, new Anecka\RetsRabbit\Transforms\Property_transformer);
        $viewData = $fractal->createData($resources)->toArray()['data'];

        return ee()->View_service
            ->setVariables($viewData)
            ->stripTags(ee()->Tag->strip_tags)
            ->setConditionals($cond)
            ->process($cond['has_results']);
    }
}
