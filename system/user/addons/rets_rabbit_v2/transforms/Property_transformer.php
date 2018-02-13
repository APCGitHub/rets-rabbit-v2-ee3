<?php

namespace Anecka\RetsRabbit\Transforms;

use League\Fractal\TransformerAbstract;

class Property_transformer extends TransformerAbstract
{
    /**
     * @var array
     */
    protected $availableIncludes = array('photos', 'open_houses');

    /**
     * @var array
     */
    protected $defaultIncludes = array('photos', 'open_houses');

    /**
     * @param array $listing
     * @return void
     */
    public function transform($listing = array())
    {
        $data = $listing;
        $data['has_photos'] = false;
        $data['total_photos'] = 0;

        // Set photo booleans
        if(isset($data['listing']) && isset($data['listing']['photos'])) {
            $count = sizeof($data['listing']['photos']);

            if($count) {
                $data['has_photos'] = true;
                $data['total_photos'] = $count;
            }
        }

        // Pull out nested listing data object
        if(isset($data['listing'])) {
            unset($data['listing']);
        }

        return $data;
    }

    /**
     * @param array $listing
     * @return void
     */
    public function includePhotos($listing = array())
    {
        if(isset($listing['listing']) && isset($listing['listing']['photos'])) {
            $photos = array();

            foreach($listing['listing']['photos'] as $index => $p) {
                $blob = $p;
                $blob['photo_count'] = $index;
                $photos[] = $blob;
            }

            return $this->collection($photos, new Photo_transformer);
        }

        return $this->null();
    }

    /**
     * @param array $listing
     * @return void
     */
    public function includeOpenHouses($listing = array())
    {
        if(isset($listing['listing']) && isset($listing['listing']['open_houses'])) {
            $openHouses = array();

            foreach($listing['listing']['open_houses'] as $oh) {
                $openHouses[] = $oh;
            }

            return $this->collection($oh, new Open_house_transformer());
        }

        return $this->null();
    }
}
