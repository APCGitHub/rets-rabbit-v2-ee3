<?php

namespace Anecka\RetsRabbit\Core\Resources;

class PropertiesResource extends aResource
{
    /**
     * Fetch a single property
     *
     * @param  string|int $id
     * @param  array  $params
     * @return ApiResponse
     */
    public function single($id, $params = array())
    {
        $url = $this->api->buildApiUrl("/property('$id')");

        return $this->api->getRequest($url, $params, true);
    }

    /**
     * Run a search query against the property endpoint
     *
     * @param  array  $params
     * @return ApiResponse
     */
    public function search($params = array())
    {
        $url = $this->api->buildApiUrl("/property");

        return $this->api->getRequest($url, $params, true);
    }

    /**
     * Fetch the property metadata.
     *
     * @return ApiResponse
     */
    public function metadata($params = array())
    {
        $url = $this->api->buildApiUrl('/property/$metadata');

        return $this->api->getRequest($url, $params, true);
    }

    /**
     * Fetch a single property's media (photos)
     *
     * @param  string|int $id
     * @param  array  $params
     * @return ApiResponse
     */
    public function media($id, $params = array())
    {
        $url = $this->api->buildApiUrl("/property($id)/media");

        return $this->api->getRequest($url, $params, true);
    }

    /**
     * Fetch a single property's media (photos)
     *
     * @param  string|int $id
     * @param  array  $params
     * @return ApiResponse
     */
    public function openHouses($id, $params = array())
    {
        $url = $this->api->buildApiUrl("/property($id)/open-house");

        return $this->api->getRequest($url, $params, true);
    }
}
