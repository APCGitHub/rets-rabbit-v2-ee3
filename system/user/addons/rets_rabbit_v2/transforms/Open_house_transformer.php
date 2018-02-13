<?php

namespace Anecka\RetsRabbit\Transforms;

use League\Fractal\TransformerAbstract;

class Open_house_transformer extends TransformerAbstract
{
	public function transform($open_house = array())
	{
		return $open_house;
	}
}