<?php

namespace Anecka\RetsRabbit\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Anecka\RetsRabbit\Core\Bridges\iCmsBridge;

class ApiService
{
    /**
     * Handle to the current CMS
     *
     * @var mixed
     */
    private $cmsBridge = null;

    /**
     * The default base API point.
     *
     * @var string
     */
    private $baseApiEndpoint = 'https://werx.retsrabbit.com';

    /**
     * Default api version endpoint.
     *
     * @var string
     */
    private $apiVersionEndpoint = 'api/v2';

    /**
     * Api token fetching endpoint.
     *
     * @var string
     */
    private $apiOauthEndpoint = 'api/oauth/access_token';

    /**
     * Built API endpoint.
     *
     * @var string
     */
    private $apiEndpoint = '';

    /**
     * Build API token endpoint.
     *
     * @var string
     */
    private $tokenEndpoint = '';

    /**
     * Guzzle client
     * @var Client
     */
    private $client;

    /**
     * Constructor
     *
     * @param iCmsBridge $bridge
     */
    public function __construct(iCmsBridge $bridge)
    {
        $this->cmsBridge = $bridge;

        //Trim off trailing slash
        if(substr($this->baseApiEndpoint, -1) === '/') {
            $this->baseApiEndpoint = substr($this->baseApiEndpoint, 0, strlen($this->baseApiEndpoint) - 2);
        }

        //Set up new guzzle client for this instance;
        $this->client = new Client();
    }

    /**
     * Fetch a new access token from the RR API.
     *
     * @return ApiResponse;
     */
    public function getAccessToken($params = array())
    {
        if(!isset($params['client_id'])) {
            throw new \Exception("You must pass in a client ID");
        }

        if(!isset($params['client_secret'])) {
            throw new \Exception("You must pass in a client secret");
        }

        $this->checkIfEndpointsSet();

        $res = $this->postRequest($this->tokenEndpoint, [
            'client_id'         => $params['client_id'],
            'client_secret'     => $params['client_secret'],
            'grant_type'        => 'client_credentials'
        ]);

        $response = new ApiResponse;

        if(isset($res['access_token'])) {
            $response->successful()->setContent($res);

            return $response;
        }

        return $response->failed()->setContent($res);
    }

    /**
     * Note: This shouldn't be used except for testing purposes.
     *
     * If the client wants to override the base api endpoint that will be done
     * via the config settings only.
     *
     * @param  string $apiEndpoint
     * @return $this
     */
    public function overrideBaseApiEndpoint($apiEndpoint = '')
    {
        if(!empty($apiEndpoint)) {
            if(substr($apiEndpoint, -1) === '/') {
                $apiEndpoint = substr($apiEndpoint, 0, strlen($apiEndpoint) - 2);
            }
            
            $this->baseApiEndpoint = $apiEndpoint;
        }

        return $this;
    }

    /**
     * Build an api url from the input segment
     * @param  string $segment
     * @return string
     */
    public function buildApiUrl($segment = '')
    {
        $this->checkIfEndpointsSet();
        $url = $this->apiEndpoint;

        if(!empty($segment)) {
            if(substr($segment, 0, 1) === '/') {
                $url .= $segment;
            } else {
                $url = $url . '/' . $segment;
            }
        }

        return $url;
    }

    /**
	 * Make a post request to {@code $endpoint} with form params
	 * {@code form}.
	 *
	 * @param  string $endpoint
	 * @param  array  $form
	 * @return array
	 */
	public function postRequest($endpoint, array $form = array(), $needsToken = false)
	{
		$body = array();

		try {
			$res = $this->client->post($endpoint, array(
				'headers' => $this->headers($needsToken),
				'form_params' => $form,
			));

			$body = json_decode($res->getBody(), true);
		} catch (BadResponseException $e) {
			$res = $e->getResponse();
			$body = $res->getBody()->getContents();

			$body = json_decode($body, true);
		}

		return $body;
	}

	/**
	 * Make a get request to {@code $endpoint} with query params {@code $params}.
	 *
	 * @param  string $endpoint
	 * @param  array  $params
	 * @return ApiResponse
	 */
	public function getRequest($endpoint, array $params = array(), $needsToken = true)
	{
		$response = new ApiResponse;

		try {
			$res = $this->client->get($endpoint, array(
				'headers' => $this->headers($needsToken),
				'query' => $params
			));

			$body = json_decode($res->getBody(), true);

            //If 300 or above then fail the response
            if($res->getStatusCode() > 299) {
                $response->failed()->setContent($body);    
            } else {
                $response->successful()->setContent($body);
            }
		} catch (BadResponseException $e) {
			$res = $e->getResponse();
            $body = $res->getBody()->getContents();

            $body = json_decode($body, true);
            $response->failed()->setContent($body);
		}

		return $response;
	}

    /*
     ---------------------------------------------------------
     | Private Methods for doing all the helpful things
     ---------------------------------------------------------
     */

    /**
	 * Apply the json content type.
	 *
	 * @param $authenticate bool
	 * @return array
	 */
	private function headers($authenticate = false)
	{
		$headers = array(
			'Accept' => 'application/json',
		);

        //Grab token
        if($authenticate) {
            $token = $this->cmsBridge->getAccessToken();

            $headers['Authorization'] = "Bearer $token";
        }

		return $headers;
	}

    /**
     * Check if the api endpoints need setting
     *
     * @return void
     */
    private function checkIfEndpointsSet()
    {
        if(empty($this->apiEndpoint)) {
            $this->apiEndpoint = $this->baseApiEndpoint . '/' . $this->apiVersionEndpoint;
        }

        if(empty($this->tokenEndpoint)) {
            $this->tokenEndpoint = $this->baseApiEndpoint . '/' . $this->apiOauthEndpoint;
        }
    }
}
