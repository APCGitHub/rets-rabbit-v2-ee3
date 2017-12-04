<?php

require PATH_THIRD . "rets_rabbit_v2/vendor/autoload.php";

use RetsRabbit\Query\QueryParser;

class Forms_service
{
	/**
	 * Convert form params to RESO standard format
	 * 
	 * @param  $params array
	 * @return array
	 */
	public function toReso($params = array())
	{
		$reso = (new QueryParser)->useAlternateSyntax()->format($params);
		$reso = array_filter($reso, function ($value) {
			return !empty($value);
		});

		ee()->logger->developer("RETS QUERY: ".var_export($reso, true));

		return $reso;
	}
}