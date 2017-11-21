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

        if(isset($data['listing']) && isset($data['listing']['photos'])) {
            $count = sizeof($data['listing']['photos']);
            if($count) {
                $data['has_photos'] = true;
                $data['total_photos'] = $count;
            }
        }

        return $data;
    }

    public function includePhotos($listing = array())
    {
        if(isset($listing['listing']) && isset($listing['listing']['photos'])) {
            return $this->collection($listing['listing']['photos'], new Photo_transformer);
        }

        return $this->null();
    }
}
