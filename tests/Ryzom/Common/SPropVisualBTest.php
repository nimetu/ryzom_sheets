<?php

namespace Ryzom\Common;

use Nel\Misc\MemStream;

class SPropVisualBTest extends \PHPUnit\Framework\TestCase {

	protected $value = '18829438681089';
	protected $buffer = "\x01\x00\x01\x12\x20\x11\x00\x00";

	public function testSerial() {
		$vpb = new SPropVisualB();
		$this->assertEquals(0, $vpb->getValue());
		$vpb->Name = 1;
		$vpb->HandsModel = 1;
		$vpb->HandsColor = 1;
		$vpb->FeetModel = 1;
		$vpb->FeetColor = 1;
		$vpb->RTrail = 1;
		$vpb->LTrail = 1;

		$this->assertEquals($this->value, $vpb->getValue());

		$mock = $this->createMock('\Nel\Misc\MemStream');
		$mock->expects($this->once())
			->method('serial_uint64')
			->with($this->value);

		$vpb->serial($mock);
	}

	public function testSerialRead() {
		$vpb = new SPropVisualB();
		$this->assertEquals(0, $vpb->getValue());

		$mem = new MemStream();
		$mem->setBuffer($this->buffer);

		$vpb->serial($mem);
		$this->assertEquals($this->value, $vpb->getValue());
	}
}
