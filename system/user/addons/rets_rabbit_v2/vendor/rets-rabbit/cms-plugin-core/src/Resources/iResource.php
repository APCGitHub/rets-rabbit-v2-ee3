<?php

namespace Anecka\RetsRabbit\Core\Resources;

interface iResource
{
    /**
     * Fetch a single resource by id
     * @param  int|string $id
     * @param  array  $params
     * @return ApiResponse
     */
    public function single($id, $params = array());

    /**
     * Search against the resource
     *
     * @param  array  $params
     * @return ApiResponse
     */
    public function search($params = array());

    /**
     * Fetch resource metadata
     *
     * @param array $params
     * @return ApiResponse
     */
    public function metadata($params = array());
}
