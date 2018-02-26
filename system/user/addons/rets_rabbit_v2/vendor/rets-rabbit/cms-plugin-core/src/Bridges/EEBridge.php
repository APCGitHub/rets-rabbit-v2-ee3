<?php

namespace Anecka\RetsRabbit\Core\Bridges;

class EEBridge implements iCmsBridge
{
    /**
     * Instance of the ee global object.
     *
     * @var mixed
     */
    private $app = null;

    /**
     * Method handle for fetching a token from the CMS
     *
     * @var callable
     */
    private $tokenFetcher = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->app = get_instance();
    }

    /**
     * Get a handle to the plugin's parent CMS.
     *
     * @return mixed
     */
    public function getCms()
    {
        return $this->app;
    }

    /**
     * Set the method which will fetch tokens from the cache.
     *
     * @param callable $method
     */
    public function setTokenFetcher($method)
    {
        $this->tokenFetcher = $method;
    }

    /**
     * Fetch a saved RR token from the CMS
     *
     * @param callable
     * @return string|null
     */
    public function getAccessToken()
    {
        if(is_null($this->tokenFetcher)) {
            throw new \Exception("You have not set the token fetcher yet.");
        }
        
        $fetcher = $this->tokenFetcher;
        $token = $fetcher();

        return $token;
    }
}
