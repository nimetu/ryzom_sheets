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

class BnpFileTest extends \PHPUnit\Framework\TestCase {

	/** @var BnpFile */
	private $bnp;

	public function setUp(): void {
		$this->bnp = new BnpFile(__DIR__.'/_files/test.bnp');
	}

	public function testHasFile() {
		$this->assertTrue($this->bnp->hasFile('a.txt'));
		$this->assertTrue($this->bnp->hasFile('b.txt'));
		$this->assertFalse($this->bnp->hasFile('c.txt'));
	}

	public function testGetFileNames() {
		$files = array('a.txt', 'b.txt');
		$this->assertArrayIsEqualToArrayIgnoringListOfKeys($files, $this->bnp->getFileNames(), []);
	}

	public function testReadSelect() {
		$this->assertTrue($this->bnp->select('a.txt'));
	}

	public function testReadSelectInvalid() {
		$this->assertFalse($this->bnp->select('c.txt'));
	}

	public function testReadNoSelect() {
		$this->expectException('\RuntimeException');
		$this->expectExceptionMessage('No file is selected');
		$buf = $this->bnp->read();
		// $this->assertEquals()
		// TODO: should throw exception
	}

	public function testReadDefault() {
		$this->bnp->select('b.txt');
		$buf = $this->bnp->read();
		$this->assertEquals("file b\n", $buf);
	}

	public function testReadNeg() {
		$this->bnp->select('b.txt');
		$buf = $this->bnp->read(-1);
		$this->assertEquals("file b\n", $buf);
	}

	public function testReadZero() {
		$this->bnp->select('b.txt');
		$buf = $this->bnp->read(0);
		$this->assertEquals("file b\n", $buf);
	}

	public function testReadOne() {
		$this->bnp->select('b.txt');

		$expected = array('f', 'i', 'l', 'e', ' ', 'b', "\n");
		foreach($expected as $char) {
			$buf = $this->bnp->read(1);
			$this->assertEquals($char, $buf);
		}
	}

	public function testReadFileA() {
		$buf = $this->bnp->readFile('a.txt');
		$this->assertEquals("file a\n", $buf);
	}

	public function testReadFileB() {
		$buf = $this->bnp->readFile('b.txt');
		$this->assertEquals("file b\n", $buf);
	}

	public function testIterator() {
		$expected = array('a.txt' => "file a\n", 'b.txt' => "file b\n");
		foreach($this->bnp as $filename => $data) {
			$this->assertEquals($expected[$filename], $data);
			unset($expected[$filename]);
		}
		$this->assertEmpty($expected, 'Iterator did not return all files');

		//$this->assertEquals('a.txt', next($this->bnp));
	}
}
