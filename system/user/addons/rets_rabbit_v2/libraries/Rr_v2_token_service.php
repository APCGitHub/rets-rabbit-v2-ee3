<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rr_v2_token_service
{
    /**
     * Optional token
     *
     * @var string
     */
    private $token = null;

    /**
     * Api service handle
     *
     * @var mixed
     */
    private $api = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        ee()->load->model('rets_rabbit_v2_config', 'Rr_config');
        ee()->load->library('Rr_v2_cache', null, 'Rr_cache');
    }

    /**
     * Set the api service
     *
     * @param mixed
     */
    public function setApiService($api)
    {
        $this->api = $api;
    }

    /**
     * Set the validating token
     *
     * @param string $token
     * @return $this
     */
    public function setToken($token = '')
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Check if token is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        $token = ee()->Rr_cache->get('access_token', true);

        if(is_null($token) || empty($token))
            return false;

        return true;
    }

    /**
     * Try to refresh the token
     *
     * @return bool
     */
    public function refresh()
    {
        $res = $this->api->getAccessToken([
            'client_id' => ee()->Rr_config->client_id,
            'client_secret' => ee()->Rr_config->client_secret
        ]);

        if($res->didSucceed()) {
            $content = $res->getResponse();
            $token = $content['access_token'];
            $ttl = $content['expires_in'];

            $set = ee()->Rr_cache->set('access_token', $token, $ttl, true);

            if($set) {
                $this->token = $token;
            }

            return true;
        }

        return false;
    }
}
