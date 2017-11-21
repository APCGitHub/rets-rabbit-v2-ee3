<?php

require PATH_THIRD . "rets_rabbit_v2/vendor/autoload.php";

use RetsRabbit\ApiService;
use RetsRabbit\Bridges\EEBridge;
use RetsRabbit\Resources\PropertiesResource;

class Properties_service
{
	/**
	 * The api service from the core RR library
	 * 
	 * @var ApiService
	 */
	private $apiService;

	/**
	 * The properties resource endpoint
	 * 
	 * @var PropertiesResource
	 */
	private $resource;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		ee()->load->library('Rets_rabbit_cache', null, 'Rr_cache');
        ee()->load->model('Rets_rabbit_config', 'Rr_config');
        ee()->load->model('Rets_rabbit_server', 'Rr_server');
        ee()->load->library('Token_service', null, 'Token');
        ee()->load->library('logger');

		$bridge = new EEBridge;

		//Set the token fetcher function so the core lib can grab tokens
		//from cache on the plugin's behalf
		$bridge->setTokenFetcher(function () {
			return ee()->Rr_cache->get('access_token', true);
		});

		$this->apiService = new ApiService($bridge);
        $this->siteId = ee()->config->item('site_id');

        ee()->Rr_config->getBySiteId($this->siteId);
        ee()->Token->setApiService($this->apiService);

		//Allow developer to override base endpoint
		if($customEndpoint = ee()->Rr_config->api_endpoint) {
            $this->apiService->overrideBaseApiEndpoint($customEndpoint);
        }

        if(!ee()->Token->isValid()) {
            ee()->Token->refresh();
        }

		//Instantiate the PropertiesResource
		$this->resource = new PropertiesResource($this->apiService);
	}

	/**
	 * @param  array
	 * @return array
	 */
	public function search($params = array())
	{
		$res = $this->resource->search($params);

		if($res->didFail()) {
			$contents = $res->getResponse();

			if(isset($contents['error']) && isset($contents['error']['code'])) {
				ee()->logger->developer('A permission error occurred.');
				
				$code = $contents['error']['code'];

				if($code == 'permission') {
					$success = ee()->Token->refresh();

					if(!is_null($success)) {
						$res = $this->resource->search($params);
					} else {
						ee()->logger->developer('Could not refresh the token during a search.');
					}
				}
			}
		}

		return $res;
	}

	/**
	 * @param  string
	 * @return array
	 */
	public function find($id = '', $params = array())
	{
		$res = $this->resource->single($id, $params);

		if($res->didFail()) {
			$contents = $res->getResponse();

			if(isset($contents['error']) && isset($contents['error']['code'])) {
				ee()->logger->developer('A permission error occurred.');
				
				$code = $contents['error']['code'];

				if($code == 'permission') {
					$success = ee()->Token->refresh();

					if(!is_null($success)) {
						$res = $this->resource->single($id, $params);
					} else {
						ee()->logger->developer('Could not refresh the token during property lookup.');
					}
				}
			}
		}

		return $res;
	}
}
