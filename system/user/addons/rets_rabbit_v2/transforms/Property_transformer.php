<?php

namespace Anecka\RetsRabbit\Transforms;

use League\Fractal\TransformerAbstract;

class Property_transformer extends TransformerAbstract
{
    protected $availableIncludes = array('photos');

    protected $defaultIncludes = array('photos');

    public function transform($listing = array())
    {
        $data = $listing;
        $data['has_photos'] = false;
        $data['total_photos'] = 0;

        if(isset($data['photos'])) {
            $count = sizeof($data['photos']);
            if($count) {
                $data['has_photos'] = true;
                $data['total_photos'] = $count;
            }
        }

        return $data;
    }

    public function includePhotos($listing = array())
    {   $photos = array();

        if(isset($listing['photos']))
            $photos = $listing['photos'];

        return $this->collection($photos, new Photo_transformer);
    }
}
