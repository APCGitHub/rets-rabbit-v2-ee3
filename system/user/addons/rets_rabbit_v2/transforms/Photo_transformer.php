<?php

namespace Anecka\RetsRabbit\Transforms;

use League\Fractal\TransformerAbstract;

class Photo_transformer extends TransformerAbstract
{
	public function transform($photo = array())
	{
		return $photo;
	}
}