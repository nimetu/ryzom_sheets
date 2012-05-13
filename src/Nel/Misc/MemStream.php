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

class MemStream {
	protected $is64bit;

	protected $buffer;
	protected $pos;
	protected $size;

	function __construct($buffer = '') {
		$this->setBuffer($buffer);

		$this->set64bit(is_int('9223372036854775807'));
	}

	function set64bit($s) {
		$this->is64bit = (bool)$s;
	}

	function setBuffer($buf) {
		$this->pos = 0;
		$this->size = strlen($buf);
		$this->buffer = $buf;
	}

	function getBuffer() {
		return $this->buffer;
	}

	function getSize() {
		return $this->size;
	}

	function getPos() {
		return $this->pos;
	}

	function seek($pos) {
		if ($pos < 0 || $pos > $this->getSize()) {
			return false;
		}
		$this->pos = $pos;
	}

	function debug($fmt) {
		//$args = array_slice(func_get_args(), 1);
		//vprintf($fmt, $args);
	}

	function dump($bytes = 32, $offset = 0) {
		$buf = substr($this->buffer, $this->pos + $offset, $bytes);
		return "\n".$this->hex_dump($buf);
	}

	function hex_dump($buf, $long = true) {
		$ret = '';
		$hex = '';
		$ascii = '';

		if ($long !== true) {
			// one big space separated hex string
			if ($long !== false) {
				$buf = substr($buf, 0, $long);
			}
			$ret = array();
			for ($i = 0; $i < $l = strlen($buf); $i++) {
				$b = ord($buf[$i]);
				$ret[] = sprintf('%02x', $b);
			}
			$ret = join(' ', $ret);
		} else {
			// 16 byte wide hex-ascii side-by-side

			if (strlen($buf) > 16) {
				$ret .= $this->hex_dump(substr($buf, 0, 16), $long);
				$ret .= $this->hex_dump(substr($buf, 16), $long);
			} else {
				for ($i = 0, $l = strlen($buf); $i < $l; $i++) {
					$b = ord($buf{$i});
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
	 */
	function _format_size($format) {
		switch ($format{0}) {
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
			throw new \Exception('Unknown format {'.$format.'}');
		}
		return $size;
	}

	/**
	 * _read
	 *
	 * @param $val - referenced, where to put value
	 * @param $format - unpack format
	 * @param int $nb
	 *
	 * @throws \RuntimeException
	 * @internal param $len - array length, 1 means single, no array
	 */
	private function _read(&$val, $format, $nb = null) {
		$len = $nb === null ? 1 : $nb;
		if (!in_array($format{0}, array('a', 'A', 'h', 'H'))) {
			$format = $format.$len;
		}
		$size = $this->_format_size($format);
		if ($this->pos > $this->size) {
			throw new \RuntimeException('Buffer overflow');
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
	 * @param $val - input or output for value
	 * @param $format - format
	 * @param null $nb
	 */
	private function _serial(&$val, $format, $nb = null) {
		$this->_read($val, $format, $nb);
	}

	function serial_byte(&$val, $nb = null) {
		$this->_serial($val, 'C', $nb);
	}

	// big-endian - network order
	function serial_short_n(&$val, $nb = null) {
		$this->_serial($val, 'n', $nb);
	}

	// little-endian - intel's order
	function serial_short(&$val, $nb = null) {
		$this->_serial($val, 'v', $nb);
	}

	// FIXME: test on 32bit
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
				$this->serial_uint32($low);
				$this->serial_uint32($high);

				if ($this->is64bit) {
					$val[] = ($high << 32) + $low;
				} else {
					$high = bcmul($high, '4294967296');
					$val = bcadd($high, $low);
				}

			}
		}
	}

	// big-endian - network order
	function serial_uint32_n(&$val, $nb = null) {
		$this->_serial($val, 'N', $nb);
	}

	// little-endian - intel's order
	function serial_uint32(&$val, $nb = null) {
		$this->_serial($val, 'V', $nb);
	}

	// machine order
	function serial_sint32(&$val, $nb = null) {
		$this->_serial($val, 'l', $nb);
	}

	// machine dependent size and representation
	function serial_float(&$val, $nb = null) {
		$this->_serial($val, 'f', $nb);
	}

	// simple - no unpack/pack
	function serial_buffer(&$val, $size) {
		$val = substr($this->buffer, $this->pos, $size);
		$this->pos += $size;
	}

	function serial_string(&$val, $nb = null) {
		$this->serial_int_string($val, $nb);
	}

	// <int> <string>
	function serial_int_string(&$val, $nb = null) {
		if ($nb === null) {
			$this->serial_uint32($size);
			$this->serial_buffer($val, $size);
		} else {
			$val = array();
			for ($i = 0; $i < $nb; $i++) {
				$this->serial_uint32($size);
				$this->serial_buffer($val[$i], $size);
			}
		}
	}

	// <short> <string>
	function serial_short_string(&$val, $nb = null) {
		if ($nb === null) {
			$this->serial_short($size);
			$this->serial_buffer($val, $size);
		} else {
			$val = array();
			for ($i = 0; $i < $nb; $i++) {
				$this->serial_short($size);
				$this->serial_buffer($val[$i], $size);
			}
		}
	}

	// <uint8> <string>
	function serial_byte_string(&$val, $nb = null) {
		if ($nb === null) {
			$this->serial_byte($size);
			$this->serial_buffer($val, $size);
		} else {
			$val = array();
			for ($i = 0; $i < $nb; $i++) {
				$this->serial_byte($size);
				$this->serial_buffer($val[$i], $size);
			}
		}
	}
}

