<?php

namespace Anecka\RetsRabbit\Core;

class ApiResponse
{
    /**
     * Flag for if the response failed
     *
     * @var bool
     */
    private $failed = false;

    /**
     * Flag for if the response was successful
     *
     * @var bool
     */
    private $success = false;

    /**
     * The body of the API response
     *
     * @var array|null
     */
    private $body = [];

    /**
     * Set the body of the response.
     *
     * @param array $content
     */
    public function setContent($content = [])
    {
        $this->body = $content;

        return $this;
    }

    /**
     * Get the api response
     *
     * @return array|null
     */
    public function getResponse()
    {
        return $this->body;
    }

    /**
     * Set the success to true.
     *
     * @return $this
     */
    public function successful()
    {
        $this->success = true;

        return $this;
    }

    /**
     * Set the failed to true.
     *
     * @return $this
     */
    public function failed()
    {
        $this->failed = true;

        return $this;
    }

    /**
     * Check if succeeded
     *
     * @return bool
     */
    public function didSucceed()
    {
        return $this->success;
    }

    /**
     * Check if failed
     *
     * @return bool
     */
    public function didFail()
    {
        return $this->failed;
    }
}
