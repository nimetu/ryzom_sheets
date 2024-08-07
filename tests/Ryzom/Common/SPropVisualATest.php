<?php

namespace Ryzom\Common;

use Nel\Misc\MemStream;

class SPropVisualATest extends \PHPUnit\Framework\TestCase {
	protected $value = '2310911766417117699';
	protected $buffer = "\x03\x12\x90\x00\x02\x02\x12\x20";

	public function testSerialWrite() {
		$vpa = new SPropVisualA();
		$vpa->Sex = 1;
		$vpa->JacketModel = 1;
		$vpa->JacketColor = 1;
		$vpa->TrouserModel = 1;
		$vpa->TrouserColor = 1;
		$vpa->WeaponRightHand = 1;
		$vpa->WeaponLeftHand = 1;
		$vpa->ArmModel = 1;
		$vpa->ArmColor = 1;
		$vpa->HatModel = 1;
		$vpa->HatColor = 1;

		$this->assertEquals($this->value, $vpa->getValue());

		$mock = $this->createMock('\Nel\Misc\MemStream');
		$mock->expects($this->once())
			->method('serial_uint64')
			->with($this->value);

		$vpa->serial($mock);
	}

	public function testSerialRead() {
		$vpa = new SPropVisualA();
		$this->assertEquals(0, $vpa->getValue());

		$mem = new MemStream();
		$mem->setBuffer($this->buffer);

		$vpa->serial($mem);
		$this->assertEquals($this->value, $vpa->getValue());
	}

	public function testFull64bitValue() {
		$expected = '12069653598422708230';
		$vpa = new SPropVisualA();
		$this->assertEquals(0, $vpa->getValue());

		$vpa->Sex = 0;
		$vpa->JacketModel = 3;
		$vpa->JacketModel = 3;
		$vpa->JacketColor = 0;
		$vpa->TrouserModel = 3;
		$vpa->TrouserColor = 0;
		$vpa->WeaponRightHand = 0;
		$vpa->WeaponLeftHand = 0;
		$vpa->ArmModel = 3;
		$vpa->ArmColor = 0;
		$vpa->HatModel = 120;
		$vpa->HatColor = 5;

		$this->assertEquals($expected, (string)$vpa->getValue());
	}
}
