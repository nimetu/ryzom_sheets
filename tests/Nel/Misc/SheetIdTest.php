<?php
//
// RyzomSheets - https://github.com/nimetu/ryzom_sheets
// Copyright (c) 2012 Meelis Mägi <nimetu@gmail.com>
//
// This file is part of RyzomSheets.
//
// RyzomSheets is free software; you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation; either version 3 of the License, or
// (at your option) any later version.
//
// RyzomSheets is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program; if not, write to the Free Software Foundation,
// Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
//

namespace Nel\Misc;

class SheetIdTest extends \PHPUnit\Framework\TestCase {

	/** @var MemStream */
	public $mem;

	/** @var array */
	public $sheets = array(
		1 => array('name' => 'a1', 'sheet' => 'a'),
		2 => array('name' => 'a2', 'sheet' => 'a'),
		3 => array('name' => 'b1', 'sheet' => 'b'),
	);

	public function setUp(): void {
		$buf = new MemStream();
		$nbItems = count($this->sheets);
		$buf->serial_uint32($nbItems);
		foreach($this->sheets as $k => $v) {
			$buf->serial_uint32($k);
			$t = $v['name'].'.'.$v['sheet'];
			$buf->serial_string($t);
		}
		$this->mem = new MemStream($buf->getBuffer());
	}

	/**
	 * @todo   Implement testSerial().
	 */
	public function testSerial() {
		$sheet = new SheetId();
		$sheet->serial($this->mem);

		$this->assertEquals(null, $sheet->getSheetId('does-not-exist'));
		$this->assertEquals(1, $sheet->getSheetId('a1.a'));
		$this->assertEquals(2, $sheet->getSheetId('a2.a'));
	}

	public function testGetSheetIdName() {
		$sheet = new SheetId();
		$this->assertEquals('#1', $sheet->getSheetIdName(1));

		$sheet->serial($this->mem);
		$this->assertEquals('a1.a', $sheet->getSheetIdName(1));
		$this->assertEquals('a1', $sheet->getSheetIdName(1, false));
	}

	/**
	 * @todo   Implement testGetSheets().
	 */
	public function testGetSheets() {
		$sheet = new SheetId();
		$sheet->serial($this->mem);

		$array = $sheet->getSheets();
		$this->assertArrayIsEqualToArrayIgnoringListOfKeys($this->sheets, $array, array());
	}
}
