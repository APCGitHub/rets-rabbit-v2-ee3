<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . "rets_rabbit_v2/vendor/autoload.php";

use Anecka\RetsRabbit\Core\Query\QueryParser;

class Rr_v2_form_service
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