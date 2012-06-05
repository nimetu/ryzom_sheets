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

/**
 * MemStreamTest
 */
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

	public function testWriteBuffer() {
		$buf = '1234567890abcdef';
		$len = strlen($buf);

		$mem = new MemStream;
		$this->assertEmpty($mem->getBuffer());
		$this->assertEquals(0, $mem->getSize());

		$mem->serial_buffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals($len, $mem->getSize());
		$this->assertEquals($len, $mem->getPos());
	}

	public function testPos() {
		$buf = '1234567890abcdef';
		$mem = new MemStream;
		$this->assertEquals(0, $mem->getPos());
		$this->assertFalse($mem->seek(10));

		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$this->assertTrue($mem->seek(10));
		$this->assertEquals(10, $mem->getPos());

		$pos = $mem->getPos();
		$this->assertFalse($mem->seek(-1));
		$this->assertEquals($pos, $mem->getPos());

		$pos = $mem->getPos();
		$size = $mem->getSize();
		$this->assertFalse($mem->seek($size + 1));
		$this->assertEquals($pos, $mem->getPos());
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
		$this->setExpectedException('\RuntimeException', 'Unknown format {?}');
		$mem->_format_size('?');
		//throw new \Exception('Unknown format {'.$format.'}');
	}

	public function testBufferOverflow() {
		$buf = "\x01\x02\x03\x04";

		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$this->setExpectedException('\RuntimeException', 'Buffer overflow by 6 bytes');
		$mem->serial_byte($result, 10);
	}

	public function testSerial_byte() {
		$buf = "\x01\x02\x03\x04";

		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_byte($val, 3);
		$this->assertCount(3, $val);
		$this->assertEquals(array(1, 2, 3), $val);
		$this->assertEquals(3, $mem->getPos());
	}

	public function testWriteByte() {
		$mem = new MemStream();
		$this->assertEquals('', $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$b = array(0x12, 0x34, 0x56);
		$mem->serial_byte($b);
		$this->assertEquals("\x12\x34\x56", $mem->getBuffer());
		$this->assertEquals(3, $mem->getPos());
	}

	public function testSerial_short() {
		$buf = "\x02\x01\x04\x03";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_short($val, 2);
		$this->assertCount(2, $val);
		$this->assertEquals(array(0x0102, 0x0304), $val);
		$this->assertEquals(4, $mem->getPos());
	}

	public function testWriteShort() {
		$mem = new MemStream();
		$this->assertEquals('', $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$b = array(0x1234, 0x5678);
		$result = "\x34\x12\x78\x56";
		$mem->serial_short($b);
		$this->assertEquals(4, $mem->getPos());
		$this->assertEquals($result, $mem->getBuffer());

		$result .= "\x12\x34\x56\x78";
		$mem->serial_short_n($b);
		$this->assertEquals(8, $mem->getPos());
		$this->assertEquals($result, $mem->getBuffer());
	}

	public function testSerial_short_n() {
		$buf = "\x01\x02\x03\x04";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_short_n($val, 2);
		$this->assertCount(2, $val);
		$this->assertEquals(array(0x0102, 0x0304), $val);
		$this->assertEquals(4, $mem->getPos());
	}

	public function testSerial_uint32() {
		$buf = "\x01\x02\x03\x04\x05\x06\x07\x08";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_uint32($val, 2);
		$this->assertCount(2, $val);
		$this->assertEquals(array(0x04030201, 0x08070605), $val);
		$this->assertEquals(8, $mem->getPos());
	}

	public function testSerial_uint32_n() {
		$buf = "\x01\x02\x03\x04\x05\x06\x07\x08";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_uint32_n($val, 2);
		$this->assertCount(2, $val);
		$this->assertEquals(array(0x01020304, 0x05060708), $val);
		$this->assertEquals(8, $mem->getPos());
	}

	public function testWriteInt() {
		$mem = new MemStream();
		$this->assertEquals(0, $mem->getPos());
		$this->assertEquals('', $mem->getBuffer());

		$b = array(0x12345678, 0x12345678);
		$result = "\x78\x56\x34\x12\x78\x56\x34\x12";
		$mem->serial_uint32($b);
		$this->assertEquals($result, $mem->getBuffer());
		$this->assertEquals(8, $mem->getPos());

		$result .= "\x12\x34\x56\x78\x12\x34\x56\x78";
		$mem->serial_uint32_n($b);
		$this->assertEquals($result, $mem->getBuffer());
		$this->assertEquals(16, $mem->getPos());
	}

	public function testSerial_sint32() {
		$buf = "\xFF\xFF\xFF\xFF";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_sint32($val, 1);
		$this->assertCount(1, $val);
		$this->assertEquals(array(-1), $val);
		$this->assertEquals(4, $mem->getPos());
	}

	public function testSerial_float() {
		$buf = "\xc3\xf5\x48\x40";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_float($val, 1);
		$this->assertCount(1, $val);
		$this->assertEquals(3.14, $val[0], '', 0.001);
		$this->assertEquals(4, $mem->getPos());
	}

	public function testWriteFloat() {
		$mem = new MemStream();
		$this->assertEquals('', $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$b = 3.14;
		$mem->serial_float($b);
		$this->assertEquals("\xc3\xf5\x48\x40", $mem->getBuffer());
		$this->assertEquals(4, $mem->getPos());
	}

	public function testSerial_uint64() {
		$buf = "\x08\x07\x06\x05\x04\x03\x02\x01";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_uint64($val, 1);

		// test for string value
		$this->assertCount(1, $val);
		$this->assertEquals("72623859790382856", $val[0]);
		$this->assertEquals(8, $mem->getPos());
	}

	public function testSerial_uint64_bcmath() {
		$buf = "\x08\x07\x06\x05\x04\x03\x02\x01";
		$mem = new MemStream();
		$mem->set64Bit(false);
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_uint64($val, 1);

		// test for string value
		$this->assertCount(1, $val);
		$this->assertEquals("72623859790382856", $val[0]);
		$this->assertEquals(8, $mem->getPos());
	}

	public function testWriteInt64() {
		$mem = new MemStream();
		$this->assertEquals('', $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$result = "\x56\x34\x12\x00\x00\x00\x00\x00";
		$result .= "\xbc\x9a\x78\x00\x00\x00\x00\x00";
		$b = array(0x00123456, 0x00789abc);
		$mem->serial_uint64($b);
		$this->assertEquals($result, $mem->getBuffer());
		$this->assertEquals(16, $mem->getPos());
	}

	public function testWriteInt64_bcmath() {
		$mem = new MemStream();
		$mem->set64bit(false);
		$this->assertEquals('', $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$result = "\x56\x34\x12\x00\x00\x00\x00\x00";
		$result .= "\xbc\x9a\x78\x00\x00\x00\x00\x00";
		$b = array(0x00123456, 0x00789abc);
		$mem->serial_uint64($b);
		$this->assertEquals($result, $mem->getBuffer());
		$this->assertEquals(16, $mem->getPos());
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
		$buf .= "\x0B\x00\x00\x00int string2";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_string($val, 2);
		$this->assertCount(2, $val);
		$this->assertEquals(array('int string', 'int string2'), $val);
		$this->assertEquals(29, $mem->getPos());
	}

	public function testSerial_byte_string() {
		$buf = "\x04Test";
		$buf .= "\x07Test123";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_byte_string($val, 2);
		$this->assertCount(2, $val);
		$this->assertEquals(array('Test', 'Test123'), $val);
		$this->assertEquals(13, $mem->getPos());
	}

	public function testSerial_short_string() {
		$buf = "\x04\x00Test";
		$buf .= "\x07\x00Test123";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_short_string($val, 2);
		$this->assertCount(2, $val);
		$this->assertEquals(array('Test', 'Test123'), $val);
		$this->assertEquals(15, $mem->getPos());
	}

	public function testSerial_int_string() {
		$buf = "\x04\x00\x00\x00Test";
		$buf .= "\x07\x00\x00\x00Test123";
		$mem = new MemStream();
		$mem->setBuffer($buf);
		$this->assertEquals($buf, $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$mem->serial_int_string($val);
		$this->assertEquals('Test', $val);
		$this->assertEquals(8, $mem->getPos());
	}

	public function testWriteString() {
		$buffer = '';
		$mem = new MemStream();
		$this->assertEquals('', $mem->getBuffer());
		$this->assertEquals(0, $mem->getPos());

		$s = array('test1', 'test2');
		$buffer .= "\x05\x00\x00\x00".$s[0];
		$buffer .= "\x05\x00\x00\x00".$s[1];
		$mem->serial_string($s);
		$this->assertEquals($buffer, $mem->getBuffer());
		$this->assertEquals(strlen($buffer), $mem->getPos());

		$s = array('test short1', 'test short2');
		$buffer .= "\x0B\x00".$s[0];
		$buffer .= "\x0B\x00".$s[1];
		$mem->serial_short_string($s);
		$this->assertEquals($buffer, $mem->getBuffer());
		$this->assertEquals(strlen($buffer), $mem->getPos());

		$s = array('test byte1', 'test byte2');
		$buffer .= "\x0A".$s[0];
		$buffer .= "\x0A".$s[1];
		$mem->serial_byte_string($s);
		$this->assertEquals($buffer, $mem->getBuffer());
		$this->assertEquals(strlen($buffer), $mem->getPos());
	}

	public function testDump() {
		$buffer = "\x31\x01\x32\x1F\x33\x80\x90\xA0\xB0\xC0\x41\x42\x43\x44\x45\x46";
		$buffer .= "\x31\x32";

		$result = "31 01 32 1f 33 80 90 a0                             [1.2.3...        ]\n";
		$mem = new MemStream($buffer);
		$hexDump = $mem->dump(8);
		$this->assertEquals($result, $hexDump);

		$result = "32 1f 33 80 90 a0 b0 c0                             [2.3.....        ]\n";
		$hexDump = $mem->dump(8, 2);
		$this->assertEquals($result, $hexDump);
	}

	public function testHexDump() {
		$buffer = "\x31\x01\x32\x1F\x33\x80\x90\xA0\xB0\xC0\x41\x42\x43\x44\x45\x46";
		$buffer .= "\x31\x32";
		$result = "31 01 32 1f 33 80 90 a0 b0 c0 41 42 43 44 45 46     [1.2.3.....ABCDEF]\n";
		$result .= "31 32                                               [12              ]\n";

		$hexDump = MemStream::hexDump($buffer);
		$this->assertEquals($result, $hexDump);

		$result = "31 01 32 1f 33 80 90 a0 b0 c0 41 42 43 44 45 46 31 32";
		$hexDump = MemStream::hexDump($buffer, false);
		$this->assertEquals($result, $hexDump);
	}
}
