<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rr_v2_cache
{
    /**
     * Base path for all cache.
     *
     * @var string
     */
    private $basePath = '/rets_rabbit_v2/';

    /**
     * Rets_rabbit_cache constructor
     */
    public function __construct()
    {
        ee()->load->library('encrypt');
    }

    /**
     * Set a value in the cache.
     *
     * @param  string  $key
     * @param  boolean $isSecure
     * @return bool
     */
    public function get($key, $isSecure = false)
    {
        $path = $this->basePath;

        if($isSecure) {
            $path .= 'secure/';
        }

        $path .= $key;
        $value = ee()->cache->get($path);

        if($isSecure && $value) {
            $value = ee()->encrypt->decode($value);
        }

        return $value;
    }

    /**
     * Set a key value in storage.
     *
     * @param string  $key
     * @param mixed  $value
     * @param integer $ttl
     * @param boolean $secure
     * @return bool
     */
    public function set($key, $value, $ttl = 3600, $secure = false)
    {
        $path = $this->basePath;

        if($secure) {
            $path .= 'secure/';
            $value = ee()->encrypt->encode($value);
        }

        $path .= $key;

        return ee()->cache->save($path, $value, $ttl);
    }

    /**
     * Delete cache
     *
     * @param  string $key
     * @return bool
     */
    public function delete($key = '')
    {
        $path = '/rets_rabbit/' . $key;

        ee()->cache->delete($path);

        return ee()->cache->clean();
    }
}
