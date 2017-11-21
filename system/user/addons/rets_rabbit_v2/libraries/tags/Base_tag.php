<?php

abstract class Base_tag
{
	/**
     * Fetch the parameters as an associative array for the API
     *
     * @return array
     */
    public function toApiParams()
    {
        $data = array();

        foreach($this->params as $k => $v) {
            if(in_array($k, $this->apiParams)) {
                if(in_array($k, array_keys($this->map))) {
                    $k = $this->map[$k];
                }

                $data[$k] = $v;
            }
        }

        return $data;
    }

    /**
     * Return the param value requested
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        if(in_array($key, array_keys($this->params))) {
            return $this->params[$key];
        } else if(in_array($key, array_keys($this->attrs))) {
            return $this->attrs[$key];
        }

        return null;
    }
}