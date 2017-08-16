<?php

namespace Anecka\RetsRabbit\Transforms;

use League\Fractal\TransformerAbstract;

class Photo_transformer extends TransformerAbstract
{
    protected $availableIncludes = array();

    protected $defaultIncludes = array();

    public function transform($photo = array())
    {
        return $photo;
    }
}
