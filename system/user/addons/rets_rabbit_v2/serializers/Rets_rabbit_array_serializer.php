<?php

namespace Anecka\RetsRabbit\Serializers;


use League\Fractal\Serializer\ArraySerializer;

class Rets_rabbit_array_serializer extends ArraySerializer
{
	public function collection($resourceKey, array $data)
	{
		return $data;
	}
}