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
	protected $is64bit;

	protected $buffer;
	protected $pos;
	protected $size;

	/**
	 * @param string $buffer
	 */
	function __construct($buffer = '') {
		$this->setBuffer($buffer);

		$this->set64bit(PHP_INT_SIZE == 8);
	}

	/**
	 * If 64bit support is disabled (32bit platform)
	 * then use bcmath() in serial_uint64() method
	 *
	 * @param bool $s
	 */
	function set64bit($s) {
		$this->is64bit = (bool)$s;
	}

	/**
	 * Set buffer and reset position counter to start
	 *
	 * @param $buf
	 */
	function setBuffer($buf) {
		$this->pos = 0;
		$this->size = strlen($buf);
		$this->buffer = $buf;
	}

	/**
	 * Return full buffer
	 *
	 * @return mixed
	 */
	function getBuffer() {
		return $this->buffer;
	}

	/**
	 * Return current buffer size
	 *
	 * @return mixed
	 */
	function getSize() {
		return $this->size;
	}

	/**
	 * Return current read position in buffer
	 *
	 * @return mixed
	 */
	function getPos() {
		return $this->pos;
	}

	/**
	 * Set current buffer position
	 *
	 * @param int $pos
	 *
	 * @return bool
	 */
	function seek($pos) {
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
	function dump($bytes = 32, $offset = 0) {
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
	static function hexDump($buf, $long = true) {
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
	function _format_size($format) {
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
	 * _serial
	 *
	 * @param mixed $val - input or output for value
	 * @param string $format - format
	 * @param int $nb
	 */
	private function _serial(&$val, $format, $nb = null) {
		$this->_read($val, $format, $nb);
	}

	/**
	 * Read 8bit integer
	 */
	function serial_byte(&$val, $nb = null) {
		$this->_serial($val, 'C', $nb);
	}

	/**
	 * Read 16bit integer
	 * big-endian, network order
	 */
	function serial_short_n(&$val, $nb = null) {
		$this->_serial($val, 'n', $nb);
	}

	/**
	 * Read 16bit integer
	 * little-endian, intel's order
	 */
	function serial_short(&$val, $nb = null) {
		$this->_serial($val, 'v', $nb);
	}

	/**
	 * Read unsigned 64bit integer
	 * little-endian, intel's order
	 */
	function serial_uint64(&$val, $nb = null) {
		if ($nb === null) {
			$this->serial_uint32($low);
			$this->serial_uint32($high);

			if ($this->is64bit) {
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
	}

	/**
	 * Read 32bit integer
	 * big-endian, network order
	 */
	function serial_uint32_n(&$val, $nb = null) {
		$this->_serial($val, 'N', $nb);
	}

	/**
	 * Read 32bit integer
	 * little-endian, intel's order
	 */
	function serial_uint32(&$val, $nb = null) {
		$this->_serial($val, 'V', $nb);
	}

	/**
	 * Read signed 32bit integer
	 */
	function serial_sint32(&$val, $nb = null) {
		$this->_serial($val, 'l', $nb);
	}

	/**
	 * Read 32bit float
	 */
	function serial_float(&$val, $nb = null) {
		$this->_serial($val, 'f', $nb);
	}

	/**
	 * Read string buffer
	 */
	function serial_buffer(&$val, $size) {
		$val = substr($this->buffer, $this->pos, $size);
		$this->pos += $size;
	}

	/**
	 * @see serial_int_string
	 */
	function serial_string(&$val, $nb = null) {
		$this->serial_int_string($val, $nb);
	}

	/**
	 * Read string with <32bit> length counter
	 */
	function serial_int_string(&$val, $nb = null) {
		if ($nb === null) {
			$this->serial_uint32($size);
			$this->serial_buffer($val, $size);
		} else {
			$val = array();
			for ($i = 0; $i < $nb; $i++) {
				$this->serial_int_string($val[$i]);
			}
		}
	}

	/**
	 * Read string with <16bit> length counter
	 */
	function serial_short_string(&$val, $nb = null) {
		if ($nb === null) {
			$this->serial_short($size);
			$this->serial_buffer($val, $size);
		} else {
			$val = array();
			for ($i = 0; $i < $nb; $i++) {
				$this->serial_short_string($val[$i]);
			}
		}
	}

	/**
	 * Read string with <8bit> length counter
	 */
	function serial_byte_string(&$val, $nb = null) {
		if ($nb === null) {
			$this->serial_byte($size);
			$this->serial_buffer($val, $size);
		} else {
			$val = array();
			for ($i = 0; $i < $nb; $i++) {
				$this->serial_byte_string($val[$i]);
			}
		}
	}
}
