<?php

namespace Anecka\RetsRabbit\Core\Resources;

class ServersResource extends aResource
{
    /**
     * Fetch a single server details
     *
     * @param int $id
     * @param array $params
     * @throws \Exception
     */
    public function single($id, $params = array())
    {
        throw new \Exception("Not yet implemented.");
    }

    /**
     * Fetch all the servers
     *
     * @param  array  $params
     * @return ApiResponse
     */
    public function search($params = array())
    {
        $url = $this->api->buildApiUrl("/servers");
        $params = array();

        return $this->api->getRequest($url, $params, true);
    }

    /**
     * Fetch the server metadata.
     *
     * @return ApiResponse
     */
    public function metadata($params = array())
    {
        return $this->search();
    }
}
