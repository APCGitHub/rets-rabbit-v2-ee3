<?php

namespace Anecka\RetsRabbit\Core\MediaResource;

class MediaResource extends aResource;
{
    /**
     * Fetch a single media
     *
     * @param  string|int $id
     * @param  array  $params
     * @return ApiResponse
     */
    public function single($id, $params = array())
    {
        throw new \Exception("Not yet implemented.");
    }

    /**
     * Run a search query against the property endpoint
     *
     * @param  array  $params
     * @return ApiResponse
     */
    public function search($params = array())
    {
        throw new \Exception("Not yet implemented.");
    }

    /**
     * Fetch the property metadata.
     *
     * @return ApiResponse
     */
    public function metadata($params = array())
    {
        $url = $this->api->buildApiUrl('/media/$metadata');

        return $this->api->getRequest($url, $params, true);
    }
}
