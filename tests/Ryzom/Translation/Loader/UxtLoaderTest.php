<?php
namespace Ryzom\Translation\Loader;

/**
 */
class UxtLoaderTest extends \PHPUnit\Framework\TestCase {

	public function testLoadingSingle() {
		$loader = new UxtLoader();

		$expect = [
			'uxt' => [
				'uiname1' => ['name' => 'name1Value'],
			],
		];
		$data = $loader->load('uxt', join("\n", [
			"uiName1\t[name1Value]",
		]));

		$this->assertEquals($expect, $data);
	}

	public function testLoadingSingleWithComments() {
		$loader = new UxtLoader();

		$expect = [
			'uxt' => [
				'uiname1' => ['name' => 'name1Value'],
			],
		];
		$data = $loader->load('uxt', join("\n", [
			"// HASH_VALUE 01234",
			"// INDEX 123",
			"uiName1\t[name1Value]",
		]));

		$this->assertEquals($expect, $data);
	}

	public function testLoadingSingleWithMultilineComments() {
		$loader = new UxtLoader();

		$expect = [
			'uxt' => [
				'uiname1' => ['name' => 'name1Value'],
			],
		];
		$data = $loader->load('uxt', join("\n", [
			"/* HASH_VALUE 01234",
			"// INDEX 123",
			"multiline\t[comment]",
			"",
			"*/",
			"uiName1\t[name1Value]",
			"/* .... */",
		]));

		$this->assertEquals($expect, $data);
	}

	public function testLoadingMulti() {
		$loader = new UxtLoader();

		$expect = [
			'uxt' => [
				'uiname3' => ['name' => "name, multiline with tab, and with space"],
			],
		];
		$data = $loader->load('uxt', join("\n", [
			"// HASH_VALUE 01234",
			"// INDEX 123",
			"uiName3\t[name, \n\tmultiline with tab, \n and with space]",
		]));

		$this->assertEquals($expect, $data);
	}

	public function testLoadingWithNoEmptyLinesBetween() {
		$loader = new UxtLoader();

		$expect = [
			'uxt' => [
				'uiname1' => ['name' => 'name1Value'],
				'uiname2' => ['name' => 'name2Value'],
			],
		];
		$data = $loader->load('uxt', join("\n", [
			"uiName1\t[name1Value]",
			"uiName2\t[name2Value]",
		]));

		$this->assertEquals($expect, $data);
	}

	public function testLoadingDefault() {
		$loader = new UxtLoader();

		$expect = [
			'uxt' => [
				'uiname1' => ['name' => 'name1Value'],
				'uiname2' => ['name' => 'name2Value'],
				'uiname3' => ['name' => "name3, multiline with tab, and with space"],
				'uiname4' => ['name' => "1.Line\n2. Line"],
			],
		];
		$data = $loader->load('uxt', join("\n", [
			"// HASH_VALUE 123",
			"// INDEX 123",
			"uiName1\t[name1Value]",
			"",
			"// HASH_VALUE 456",
			"// INDEX 456",
			"uiName2\t[name2Value]",
			"",
			"// HASH_VALUE 789",
			"// INDEX 789",
			"uiName3\t[name3, \n\tmultiline with tab, \n and with space]",
			"",
			"// HASH_VALUE 012",
			"// INDEX 012",
			"uiName4\t[1.Line\\n2. Line]", // '\n' in string
		]));

		$this->assertEquals($expect, $data);
	}
}

