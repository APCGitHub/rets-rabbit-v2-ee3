<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . "rets_rabbit_v2/vendor/autoload.php";

use Anecka\RetsRabbit\Core\ApiService;
use Anecka\RetsRabbit\Core\Bridges\EEBridge;
use Anecka\RetsRabbit\Core\Resources\PropertiesResource;

class Rr_v2_property_service
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
		ee()->load->library('Rr_v2_cache', null, 'Rr_cache');
        ee()->load->model('rets_rabbit_v2_config', 'Rr_config');
        ee()->load->model('Rets_rabbit_v2_server', 'Rr_server');
        ee()->load->library('Rr_v2_token_service', null, 'Rr_token');
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
        ee()->Rr_token->setApiService($this->apiService);

		//Allow developer to override base endpoint
		if($customEndpoint = ee()->Rr_config->api_endpoint) {
            $this->apiService->overrideBaseApiEndpoint($customEndpoint);
        }

        if(!ee()->Rr_token->isValid()) {
            ee()->Rr_token->refresh();
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
					$success = ee()->Rr_token->refresh();

					if(!is_null($success)) {
						$res = $this->resource->search($params);
					} else {
						ee()->logger->developer('Could not refresh the token during a search.');
					}
				}
			} else {
				ee()->logger->developer('An unknown error occurred trying to fetch properties.');
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
					$success = ee()->Rr_token->refresh();

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
