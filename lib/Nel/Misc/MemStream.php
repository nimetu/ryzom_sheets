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
 * MemStream
 */
class MemStream {
	/** @var bool */
	protected $isReading = false;

	/** @var bool */
	protected $is64bit = false;

	/** @var string */
	protected $buffer = '';

	/** @var int */
	protected $pos = 0;

	/** @var int */
	protected $size = 0;

	/**
	 * @param string $buffer
	 */
	public function __construct($buffer = '') {
		$this->setBuffer($buffer);

		$this->set64bit(PHP_INT_SIZE === 8);
	}

	/**
	 * If 64bit support is disabled (32bit platform)
	 * then use bcmath() in serial_uint64() method
	 *
	 * @param bool $s
	 *
	 * @return void
	 */
	public function set64bit($s) {
		$this->is64bit = $s;
	}

	/**
	 * Set buffer and reset position counter to start
	 *
	 * @param string $buf
	 *
	 * @return void
	 */
	public function setBuffer($buf) {
		$this->pos = 0;
		$this->size = strlen($buf);
		$this->buffer = $buf;
		$this->isReading = !empty($buf);
	}

	/**
	 * Return full buffer
	 *
	 * @return mixed
	 */
	public function getBuffer() {
		return $this->buffer;
	}

	/**
	 * Return current buffer size
	 *
	 * @return mixed
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * Return current read position in buffer
	 *
	 * @return mixed
	 */
	public function getPos() {
		return $this->pos;
	}

	/**
	 * @return bool
	 */
	public function isReading() {
		return $this->isReading;
	}

	/**
	 * Set current buffer position
	 *
	 * @param int $pos
	 *
	 * @return bool
	 */
	public function seek($pos) {
		if ($pos < 0 || $pos > $this->getSize()) {
			return false;
		}
		$this->pos = $pos;
		return true;
	}

	/**
	 * Return hex dump for next N bytes from buffer
	 * Offset can be negative to rewind buffer for dump
	 *
	 * @param int $bytes
	 * @param int $offset
	 *
	 * @return string
	 */
	public function dump($bytes = 32, $offset = 0) {
		$buf = substr($this->buffer, $this->pos + $offset, $bytes);
		return self::hexDump($buf);
	}

	/**
	 * Return hex-ascii formatted hexdump
	 *
	 * @param string $buf
	 * @param bool $long
	 *
	 * @return string
	 */
	static public function hexDump($buf, $long = true) {
		$ret = '';

		if ($long !== true) {
			// one big space separated hex string
			$ret = array();
			for ($i = 0; $i < $l = strlen($buf); $i++) {
				$b = ord($buf[$i]);
				$ret[] = sprintf('%02x', $b);
			}
			$ret = join(' ', $ret);
		} else {
			// 16 byte wide hex-ascii side-by-side
			$length = strlen($buf);
			$blocks = ceil($length / 16);
			for ($i = 0; $i < $blocks; $i++) {
				$hex = '';
				$ascii = '';
				for ($j = 0; $j < 16; $j++) {
					$idx = 16 * $i + $j;
					if ($idx >= $length) {
						// end of string
						break;
					}
					$b = ord($buf[$idx]);
					$hex .= sprintf('%02x ', $b);
					if ($b < 32 || $b > 127) {
						$ascii .= '.';
					} else {
						$ascii .= chr($b);
					}
				}
				if ($hex != '') {
					$ret .= sprintf("%-51s [%-16s]\n", $hex, $ascii);
				}
			}
		}
		return $ret;
	}

	/**
	 * Return unpack/pack format size in bytes
	 *
	 * @param string $format
	 *
	 * @return int
	 * @throws \RuntimeException
	 */
	private function _format_size($format) {
		switch ($format[0]) {
		case 'a':
		case 'A':
		case 'h':
		case 'H':
			$size = intval(substr($format, 1)); // a20
			break;
		case 'c':
		case 'C':
			$size = 1;
			break;
		case 's':
		case 'S':
		case 'n':
		case 'v':
			$size = 2;
			break;
		case 'l':
		case 'L':
		case 'N':
		case 'V':
			$size = 4;
			break;
		case 'f':
			$size = 4;
			break;
		case 'd':
			$size = 8;
			break; // i think
		case 'x':
			$size = 1;
			break; // 0x00 ? NUL
		default: // i, I, X, @
			throw new \RuntimeException('Unknown format {'.$format.'}');
		}
		return $size;
	}

	/**
	 * _read
	 *
	 * @param mixed $val - referenced, where to put value
	 * @param string $format - unpack format
	 * @param int $nb
	 *
	 * @return void
	 *
	 * @throws \RuntimeException
	 */
	private function _read(&$val, $format, $nb = null) {
		$len = $nb === null ? 1 : $nb;
		if (!in_array($format[0], array('a', 'A', 'h', 'H'))) {
			$format = $format.$len;
		}
		$size = $this->_format_size($format);
		$newPos = $this->pos + $size * $len;
		if ($newPos > $this->size) {
			$bytes = $newPos - $this->size;
			throw new \RuntimeException("Buffer overflow by $bytes bytes");
		}
		$tmp = unpack('@'.$this->pos.'/'.$format, $this->buffer);
		if ($nb === null) {
			$val = $tmp[1];
		} else {
			$val = array_values($tmp);
		}
		$this->pos += $size * $len;
	}

	/**
	 * _write
	 *
	 * @param mixed $val
	 * @param string $format
	 *
	 * @return void
	 */
	private function _write($val, $format) {
		if (!is_array($val)) {
			$val = array($val);
		}
		foreach ($val as $v) {
			$this->buffer .= pack($format, $v);
		}
		$this->size = strlen($this->buffer);
		$this->pos = $this->size;
	}

	/**
	 * _serial
	 *
	 * @param mixed $val - input or output for value
	 * @param string $format - format
	 * @param int $nb
	 *
	 * @return void
	 */
	private function _serial(&$val, $format, $nb = null) {
		if ($this->isReading) {
			$this->_read($val, $format, $nb);
		} else {
			$this->_write($val, $format);
		}
	}

	/**
	 * Read/write unsigned 8bit integer
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_byte(&$val, $nb = null) {
		$this->_serial($val, 'C', $nb);
	}

	/**
	 * Read/write signed 8bit integer
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_sint8(&$val, $nb = null) {
		$this->_serial($val, 'c', $nb);
	}

	/**
	 * Read/write 16bit integer
	 * big-endian, network order
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_short_n(&$val, $nb = null) {
		$this->_serial($val, 'n', $nb);
	}

	/**
	 * Read/write 16bit integer
	 * little-endian, intel's order
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_short(&$val, $nb = null) {
		$this->_serial($val, 'v', $nb);
	}

	/**
	 * Read/write unsigned 64bit integer
	 * little-endian, intel's order
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_uint64(&$val, $nb = null) {
		if ($this->isReading) {
			if ($nb === null) {
				$this->serial_uint32($low);
				$this->serial_uint32($high);

				if ($this->is64bit && is_int($val)) {
					$val = ($high << 32) + $low;
				} else {
					$high = bcmul($high, '4294967296');
					$val = bcadd($high, $low);
				}
			} else {
				$val = array();
				for ($i = 0; $i < $nb; $i++) {
					$this->serial_uint64($val[$i]);
				}
			}
		} else {
			if (is_array($val)) {
				foreach ($val as $s) {
					$this->serial_uint64($s);
				}
			} else {
				if ($this->is64bit && is_int($val)) {
					$high = ($val >> 32) & 0xFFFFFFFF;
					$low = $val & 0xFFFFFFFF;
				} else {
					$high = bcdiv($val, '4294967296');
					$low = bcmod($val, '4294967296');
				}
				$this->serial_uint32($low);
				$this->serial_uint32($high);
			}
		}
	}

	/**
	 * Read/write 32bit integer
	 * big-endian, network order
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_uint32_n(&$val, $nb = null) {
		$this->_serial($val, 'N', $nb);
	}

	/**
	 * Convert signed 32bit int to unsigned 32bit int
	 *
	 * @param mixed $val
	 *
	 * @return int
	 */
	public function sint32_to_uint32($val){
		if((float)$val < 0){
			$val = sprintf('%u', $val);
		}
		return $val;
	}

	/**
	 * Convert unsigned 32bit int to signed 32bit int
	 *
	 * @param mixed $val
	 *
	 * @return int
	 */
	public function uint32_to_sint32($val){
		if ((float)$val > 2147483647) {
			$val = bcsub($val, '4294967296');
		}
		return $val;
	}

	/**
	 * Read/write 32bit integer
	 * little-endian - intel's order
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_uint32(&$val, $nb = null) {
		if ($this->isReading()) {
			$this->_serial($val, 'V', $nb);
			// correct sign
			if(is_array($val)){
				array_map(array($this, 'sint32_to_uint32'), $val);
			}else{
				$val = $this->sint32_to_uint32($val);
			}
		}else{
			// correct sign
			if(is_array($val)){
				array_map(array($this, 'uint32_to_sint32'), $val);
			}else{
				$val = $this->uint32_to_sint32($val);
			}
			$this->_serial($val, 'V', $nb);
		}
	}

	/**
	 * Read/write 32bit signed int
	 * machine order
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_sint32(&$val, $nb = null) {
		$this->_serial($val, 'l', $nb);
	}

	/**
	 * Read/write float
	 * machine dependent size and representation
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	function serial_float(&$val, $nb = null) {
		$this->_serial($val, 'f', $nb);
	}

	/**
	 * Read/write string buffer
	 *
	 * @param mixed $val
	 * @param int $size
	 *
	 * @return void
	 */
	public function serial_buffer(&$val, $size = null) {
		if ($this->isReading) {
			$val = substr($this->buffer, $this->pos, $size);
		} else {
			$this->buffer .= $val;
			$size = strlen($val);
			$this->size += $size;
		}
		/** @psalm-suppress PossiblyNullOperand */
		$this->pos += $size;
	}

	/**
	 * @see serial_int_string
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_string(&$val, $nb = null) {
		$this->serial_int_string($val, $nb);
	}

	/**
	 * Serial ucstring (16bit unicode)
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_ucstring(&$val, $nb = null) {
		if ($this->isReading) {
			if ($nb === null) {
				$this->serial_uint32($size);
				$this->serial_buffer($val, $size * 2);
			} else {
				$val = array();
				for ($i = 0; $i < $nb; $i++) {
					$this->serial_ucstring($val[$i]);
				}
			}
		} else {
			if (is_array($val)) {
				foreach ($val as $s) {
					$this->serial_ucstring($s);
				}
			} else {
				$size = strlen($val);
				$this->serial_uint32($size);
				$this->serial_buffer($val, $size * 2);
			}
		}
	}

	/**
	 * Read/write string with <int32> length counter
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_int_string(&$val, $nb = null) {
		if ($this->isReading) {
			if ($nb === null) {
				$this->serial_uint32($size);
				$this->serial_buffer($val, $size);
			} else {
				$val = array();
				for ($i = 0; $i < $nb; $i++) {
					$this->serial_int_string($val[$i]);
				}
			}
		} else {
			if (is_array($val)) {
				foreach ($val as $s) {
					$this->serial_int_string($s);
				}
			} else {
				$size = strlen($val);
				$this->serial_uint32($size);
				$this->serial_buffer($val, $size);
			}
		}
	}

	/**
	 * Read/write string with <16bit> length counter
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_short_string(&$val, $nb = null) {
		if ($this->isReading) {
			if ($nb === null) {
				$this->serial_short($size);
				$this->serial_buffer($val, $size);
			} else {
				$val = array();
				for ($i = 0; $i < $nb; $i++) {
					$this->serial_short_string($val[$i]);
				}
			}
		} else {
			if (is_array($val)) {
				foreach ($val as $s) {
					$this->serial_short_string($s);
				}
			} else {
				$size = strlen($val);
				$this->serial_short($size);
				$this->serial_buffer($val, $size);
			}
		}
	}

	/**
	 * Read/write string with <8bit> length counter
	 *
	 * @param mixed $val
	 * @param int $nb
	 *
	 * @return void
	 */
	public function serial_byte_string(&$val, $nb = null) {
		if ($this->isReading) {
			if ($nb === null) {
				$this->serial_byte($size);
				$this->serial_buffer($val, $size);
			} else {
				$val = array();
				for ($i = 0; $i < $nb; $i++) {
					$this->serial_byte_string($val[$i]);
				}
			}
		} else {
			if (is_array($val)) {
				foreach ($val as $s) {
					$this->serial_byte_string($s);
				}
			} else {
				$size = strlen($val);
				$this->serial_byte($size);
				$this->serial_buffer($val, $size);
			}
		}
	}

	/**
	 * Version serializing
	 *
	 * @param int $val
	 *
	 * @return void
	 */
	public function serialVersion(&$val) {
		if ($this->isReading()) {
			$this->serial_byte($b);
			if ($b == 0xFF) {
				$this->serial_uint32($v);
			} else {
				$val = $b;
			}
		} else {
			if ($val >= 0xFF) {
				$b = 0xFF;
				$this->serial_byte($b);
				$this->serial_uint32($val);
			} else {
				$this->serial_byte($val);
			}
		}
	}

	/**
	 * Serialize and verify next few bytes.
	 *
	 * Throw exception if they do not match.
	 *
	 * @param string $val
	 *
	 * @return void
	 *
	 * @throws \UnexpectedValueException
	 */
	public function serialCheck($val) {
		if ($this->isReading()) {
			$this->serial_buffer($buf, strlen($val));
			if ($val !== $buf) {
				/** @psalm-suppress ForbiddenCode */
				var_dump($val, $buf);
				throw new \UnexpectedValueException("Unexpected data from stream, expected ({$val})");
			}
		} else {
			$this->serial_buffer($val, strlen($val));
		}
	}

}
