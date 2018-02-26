<?php

namespace Anecka\RetsRabbit\Core\Resources;

class OpenHousesResource extends aResource
{
    /**
     * Fetch a single property
     *
     * @param  string|int $id
     * @param  array  $params
     * @return ApiResponse
     */
    public function singe($id, $params = array())
    {
        $url = $this->api->buildApiUrl("/open-house('$id')");

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
        throw new \Exception("This method has not been implemented yet.");
    }

    /**
     * Fetch the property metadata.
     *
     * @return ApiResponse
     */
    public function metadata($params = array())
    {
        $url = $this->api->buildApiUrl('/open-house/$metadata');

        return $this->api->getRequest($url, $params, true);
    }
}
