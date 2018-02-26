<?php

namespace Tests\Feature;

use Anecka\RetsRabbit\Core\Query\QueryParser;
use Tests\TestCase;

class QueryParserTest extends TestCase
{
	/**
	 * @test
	 */
	public function testAlternativeSyntaxSingleField()
	{
		$params = [
			'rr:ListPrice-ge-' => 150000,
			'rr:ListPrice-le-' => 175000
		];
		$queryParser = new QueryParser(false);
		$q = $queryParser->format($params);
		
		$expected = "ListPrice ge 150000 and ListPrice le 175000";
		$actual = trim($q['$filter']);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function testAlternativeSyntaxSingleFieldMultiVal()
	{
		$params = [
			'rr:PublicRemarks-contains-' => [
				'fireplace', 'golf', 'pool'
			]
		];
		$queryParser = new QueryParser(false);
		$q = $queryParser->format($params);
		
		$expected = "(contains(PublicRemarks, 'fireplace') or contains(PublicRemarks, 'golf') or contains(PublicRemarks, 'pool'))";
		$actual = trim($q['$filter']);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function testAlternativeSyntaxMultiFieldSingleVal()
	{
		$params = [
			'rr:StateOrProvince/City/PostalCode-contains-' => 'Columbus'
		];
		$queryParser = (new QueryParser)->useAlternateSyntax();
		$q = $queryParser->format($params);

		$expected = "(contains(StateOrProvince, 'Columbus') or contains(City, 'Columbus') or contains(PostalCode, 'Columbus'))";
		$actual = trim($q['$filter']);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function testStandardSyntaxSingleField()
	{
		$params = [
			'rr:ListPrice(ge)' => 150000,
			'rr:ListPrice(le)' => 175000
		];
		$queryParser = new QueryParser;
		$q = $queryParser->format($params);
		
		$expected = "ListPrice ge 150000 and ListPrice le 175000";
		$actual = trim($q['$filter']);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function testStandardSyntaxSingleFieldMultiVal()
	{
		$params = [
			'rr:PublicRemarks(contains)' => [
				'fireplace', 'golf', 'pool'
			]
		];
		$queryParser = new QueryParser;
		$q = $queryParser->format($params);
		
		$expected = "(contains(PublicRemarks, 'fireplace') or contains(PublicRemarks, 'golf') or contains(PublicRemarks, 'pool'))";
		$actual = trim($q['$filter']);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function testStandardSyntaxMultiFieldSingleVal()
	{
		$params = [
			'rr:StateOrProvince|City|PostalCode(contains)' => 'Columbus'
		];
		$queryParser = new QueryParser;
		$q = $queryParser->format($params);

		$expected = "(contains(StateOrProvince, 'Columbus') or contains(City, 'Columbus') or contains(PostalCode, 'Columbus'))";
		$actual = trim($q['$filter']);

		$this->assertEquals($expected, $actual);
	}
}