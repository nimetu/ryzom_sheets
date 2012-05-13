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

class MemStreamTest extends \PHPUnit_Framework_TestCase {

	public function testBuffer() {
		$buf = '1234567890abcdef';
		$mem = new MemStream;
		$this->assertEmpty($mem->getBuffer());
		$this->assertEquals(0, $mem->getSize());

		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(strlen($buf), $mem->getSize());
	}

	public function testPos() {
		$buf = '1234567890abcdef';
		$mem = new MemStream;
		$this->assertEquals(0, $mem->getPos());
		$this->assertFalse($mem->seek(10));

		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());
		$this->assertNull($mem->seek(10));
		$this->assertEquals(10, $mem->getPos());
	}

	public function test_format_size() {
		$mem = new MemStream();
		$this->assertEquals(1, $mem->_format_size('a1'));
		$this->assertEquals(20, $mem->_format_size('a20'));
		$this->assertEquals(1, $mem->_format_size('c'));
		$this->assertEquals(1, $mem->_format_size('C'));
		$this->assertEquals(2, $mem->_format_size('s'));
		$this->assertEquals(2, $mem->_format_size('S'));
		$this->assertEquals(2, $mem->_format_size('n'));
		$this->assertEquals(2, $mem->_format_size('v'));
		$this->assertEquals(4, $mem->_format_size('l'));
		$this->assertEquals(4, $mem->_format_size('L'));
		$this->assertEquals(4, $mem->_format_size('N'));
		$this->assertEquals(4, $mem->_format_size('V'));
		$this->assertEquals(8, $mem->_format_size('d'));
		$this->assertEquals(1, $mem->_format_size('x'));
		//throw new \Exception('Unknown format {'.$format.'}');
	}

	public function testSerial_byte() {
		$buf = "\x01\x02\x03\x04";

		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_byte($result);
		$this->assertEquals(1, $result);
		$this->assertEquals(1, $mem->getPos());
	}

	public function testSerial_short() {
		$buf = "\x02\x01\x04\x03";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_short($short_n);
		$this->assertEquals(0x0102, $short_n);
		$this->assertEquals(2, $mem->getPos());

		$mem->serial_short($short_n);
		$this->assertEquals(0x0304, $short_n);
		$this->assertEquals(4, $mem->getPos());
	}

	public function testSerial_short_n() {
		$buf = "\x01\x02\x03\x04";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_short_n($short_n);
		$this->assertEquals(0x0102, $short_n);
		$this->assertEquals(2, $mem->getPos());

		$mem->serial_short_n($short_n);
		$this->assertEquals(0x0304, $short_n);
		$this->assertEquals(4, $mem->getPos());
	}

	public function testSerial_uint32() {
		$buf = "\x01\x02\x03\x04\x05\x06\x07\x08";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_uint32($val);
		$this->assertEquals(0x04030201, $val);
		$this->assertEquals(4, $mem->getPos());

		$mem->serial_uint32($val);
		$this->assertEquals(0x08070605, $val);
		$this->assertEquals(8, $mem->getPos());
	}

	public function testSerial_uint32_n() {
		$buf = "\x01\x02\x03\x04\x05\x06\x07\x08";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_uint32_n($val);
		$this->assertEquals(0x01020304, $val);
		$this->assertEquals(4, $mem->getPos());

		$mem->serial_uint32_n($val);
		$this->assertEquals(0x05060708, $val);
		$this->assertEquals(8, $mem->getPos());
	}

	public function testSerial_sint32() {
		$buf = "\xFF\xFF\xFF\xFF";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_sint32($val);
		$this->assertEquals(-1, $val);
		$this->assertEquals(4, $mem->getPos());
	}

	public function testSerial_float() {
		$buf = "\xc3\xf5\x48\x40";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_float($val);
		$this->assertEquals(3.14, $val, '', 0.001);
		$this->assertEquals(4, $mem->getPos());
	}

	public function testSerial_uint64() {
		$buf = "\x08\x07\x06\x05\x04\x03\x02\x01";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_uint64($val);

		// test for string value
		$this->assertEquals("72623859790382856", $val);
		$this->assertEquals(8, $mem->getPos());
	}

	public function testSerial_uint64_bcmath() {
		$buf = "\x08\x07\x06\x05\x04\x03\x02\x01";
		$mem = new MemStream();
		$mem->set64Bit(false);
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_uint64($val);

		// test for string value
		$this->assertEquals("72623859790382856", $val);
		$this->assertEquals(8, $mem->getPos());
	}

	public function testSerial_buffer() {
		$buf = "buffer string";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_buffer($val, 6);
		$this->assertEquals('buffer', $val);
		$this->assertEquals(6, $mem->getPos());
	}

	public function testSerial_string() {
		$buf = "\x0A\x00\x00\x00int string";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_string($val);
		$this->assertEquals('int string', $val);
		$this->assertEquals(14, $mem->getPos());
	}

	public function testSerial_byte_string() {
		$buf = "\x04Test123";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_byte_string($val);
		$this->assertEquals('Test', $val);
		$this->assertEquals(5, $mem->getPos());
	}

	public function testSerial_short_string() {
		$buf = "\x04\x00Test123";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_short_string($val);
		$this->assertEquals('Test', $val);
		$this->assertEquals(6, $mem->getPos());
	}

	public function testSerial_int_string() {
		$buf = "\x04\x00\x00\x00Test123";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_int_string($val);
		$this->assertEquals('Test', $val);
		$this->assertEquals(8, $mem->getPos());
	}

}
