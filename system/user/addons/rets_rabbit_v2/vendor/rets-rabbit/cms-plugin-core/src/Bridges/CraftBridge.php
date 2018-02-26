<?php

namespace Anecka\RetsRabbit\Core\Bridges;

class CraftBridge implements iCmsBridge
{
    /**
     * Instance of the Craft app global object.
     *
     * @var Craft
     */
    private $app = null;

    /**
     * Rets Rabbit settings in Craft
     * @var array
     */
    private $settings;

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
        if(class_exists('\\Craft\\Craft')) {
            //v2
            $this->app = \Craft\Craft::app();
            $this->settings = $this->app->plugins->getPlugin('retsRabbit')->getSettings();
        } else if(class_exists('\\Craft')){
            //v3
            $this->app = \Craft::$app;
            $this->settings = \anecka\retsrabbit\RetsRabbit::$plugin->getSettings();
        }
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
     * @return string|null
     */
    public function getAccessToken()
    {
        if(is_null($this->tokenFetcher)) {
            throw new \Exception("You have not set the token fetcher yet.");
        }

        $callable = $this->tokenFetcher;
        $token = $callable();

        return $token;
    }
}
