<?php
namespace Ryzom\Translation\Loader;

/**
 */
class WordsLoaderTest extends \PHPUnit\Framework\TestCase {

	public function testSkipColumn() {
		$loader = new WordsLoader();

		$expect = [
			'title' => [
				'titleid' => [
					'name' => 'nameValue'
				]
			]
		];

		$data = $loader->load('title',
			"*HASH_VALUE\ttitle_id\tname\n".
			"hashValue\ttitleId\tnameValue\n");

		$this->assertEquals($expect, $data);
	}

	public function testDescriptionColumnRemap() {
		$loader = new WordsLoader();

		$expect = [
			'title' => [
				'titleid' => [
					'name' => 'nameValue',
					'description' => 'descValue'
				]
			]
		];

		$data = $loader->load('title',
			"*HASH_VALUE\ttitle_id\tname\tdescripción\n".
			"hashValue\ttitleId\tnameValue\tdescValue\n");

		$this->assertEquals($expect, $data);
	}
}

