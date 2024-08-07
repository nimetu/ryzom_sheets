<?php

namespace Ryzom\Common;

use Nel\Misc\MemStream;

class SPropVisualCTest extends \PHPUnit\Framework\TestCase {

	protected $value = '1200958908699209';
	protected $buffer = "\x49\x92\x24\x09\x44\x44\x04\x00";

	public function testSerial() {
		$vpc = new SPropVisualC();
		$this->assertEquals(0, $vpc->getValue());
		$vpc->MorphTarget1 = 1;
		$vpc->MorphTarget2 = 1;
		$vpc->MorphTarget3 = 1;
		$vpc->MorphTarget4 = 1;
		$vpc->MorphTarget5 = 1;
		$vpc->MorphTarget6 = 1;
		$vpc->MorphTarget7 = 1;
		$vpc->MorphTarget8 = 1;
		$vpc->EyesColor = 1;
		$vpc->Tattoo = 1;
		$vpc->CharacterHeight = 1;
		$vpc->TorsoWidth = 1;
		$vpc->ArmsWidth = 1;
		$vpc->LegsWidth = 1;
		$vpc->BreastSize = 1;

		$this->assertEquals($this->value, $vpc->getValue());

		$mock = $this->createMock('\Nel\Misc\MemStream');
		$mock->expects($this->once())
			->method('serial_uint64')
			->with($this->value);

		$vpc->serial($mock);
	}

	public function testSerialRead() {
		$vpc = new SPropVisualC();
		$this->assertEquals(0, $vpc->getValue());

		$mem = new MemStream();
		$mem->setBuffer($this->buffer);

		$vpc->serial($mem);
		$this->assertEquals($this->value, $vpc->getValue());
	}
}
