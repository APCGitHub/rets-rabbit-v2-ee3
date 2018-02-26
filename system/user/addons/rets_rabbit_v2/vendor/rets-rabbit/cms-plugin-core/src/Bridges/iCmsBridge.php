<?php

namespace Anecka\RetsRabbit\Core\Bridges;

interface iCmsBridge
{
    /**
     * Get a handle to the plugin's parent CMS.
     *
     * @return mixed
     */
    public function getCms();

    /**
     * Set the method which will fetch tokens from the cache.
     *
     * @param callable $method
     */
    public function setTokenFetcher($method);

    /**
     * Fetch a saved RR token from the CMS
     *
     * @return string|null
     */
    public function getAccessToken();
}
