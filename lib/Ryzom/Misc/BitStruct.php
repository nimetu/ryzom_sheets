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

namespace Ryzom\Misc;

/**
 * Create C/C++ like bitfield structure
 */
class BitStruct {

	/** @var array */
	protected $structure;

	/** @var array */
	protected $values;

	/** @var int */
	protected $totalBits;

	/** @val bool */
	protected $useBitOps;

	/** @var mixed */
	protected $realValue;

	/**
	 * Create new bitfield structure
	 *
	 * Structure of array('a' => 1, 'b' => 5 'c' => 2)
	 * will create 8 bits with 'ccbbbbba' layout
	 *
	 * @param array $structure
	 */
	public function __construct(array $structure) {
		$this->structure = $structure;
		$this->realValue = 0;

		$this->totalBits = 0;
		foreach ($this->structure as $key => $bits) {
			$this->totalBits += $bits;
			$this->values[$key] = 0;
		}

		// PHP will overflow to negative numbers if we use bitwise ops
		// in full bit length and getValue() will give wrong (negative) result
		// Limit to 31 and 63 bits only
		if (PHP_INT_SIZE == 4 && $this->totalBits < 32) {
			$this->useBitOps = true;
		} elseif (PHP_INT_SIZE == 8 && $this->totalBits < 64) {
			$this->useBitOps = true;
		} else {
			$this->useBitOps = false;
		}
	}

	/**
	 * Set bitfield composite value.
	 * If value string starts with '0x', then decode it as hex
	 *
	 * @param string|int $value
	 */
	public function setValue($value) {
		if (substr($value, 0, 2) == '0x') {
			$value = $this->hex2dec($value);
		}

		// see if value is small enough to use bitwise ops
		if (is_int($value)) {
			$useBitOps = true;
		} else {
			$useBitOps = false;
		}
		$this->realValue = $value;

		foreach ($this->structure as $key => $bits) {
			if ($value == 0) {
				$val = 0;
			} elseif ($useBitOps) {
				// we need value for first N bits
				$mask = (1 << $bits) - 1;
				$val = $value & $mask;
				// remove processed bits from value
				// shifted highest bits are not cleared
				// and might remain as 1
				$value = $value >> $bits;
			} else {
				// we need value for first N bits
				$pow = bcpow(2, $bits);
				$val = bcmod($value, $pow);
				// remove processed bits from value
				$value = bcdiv($value, $pow);
			}

			$this->values[$key] = $val;
		}
	}

	/**
	 * Get bitfield composite value in hex
	 *
	 * @param bool $recalculate
	 *
	 * @return mixed
	 */
	public function getValueHex($recalculate = false) {
		$val = $this->getValue($recalculate);

		return $this->dec2hex($val);
	}

	/**
	 * Get bitfield composite value
	 *
	 * @param bool $recalculate
	 *
	 * @return mixed
	 */
	public function getValue($recalculate = false) {
		if (!$recalculate && $this->realValue !== false) {
			return $this->realValue;
		}

		$first = true;
		$this->realValue = 0;
		// process last fields first
		foreach (array_reverse($this->structure) as $key => $bits) {
			// if this is second value, then make some room
			if (!$first) {
				if ($this->useBitOps) {
					$this->realValue = $this->realValue << $bits;
				} else {
					$pow = bcpow(2, $bits);
					$this->realValue = bcmul($this->realValue, $pow);
				}
			}
			$first = false;

			$value = $this->values[$key];
			// make sure value takes N bits of space
			if ($this->useBitOps) {
				// val & mask
				$mask = (1 << $bits) - 1;
				$value = $value & $mask;
				// val << bits
				$this->realValue += $value;
			} else {
				// val & mask
				$pow = bcpow(2, $bits);
				$value = bcmod($value, $pow);
				$this->realValue = bcadd($this->realValue, $value);
			}
		}

		return $this->realValue;
	}

	/**
	 * Get individual field value
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get($key) {
		if (isset($this->values[$key])) {
			return $this->values[$key];
		}
		return null;
	}

	/**
	 * Modify individual field
	 *
	 * @param string $key
	 * @param int $val
	 */
	public function __set($key, $val) {
		if (isset($this->structure[$key])) {
			// invalidate cached value
			$this->realValue = false;
			$this->values[$key] = $val;
		}
	}

	/**
	 * Convert big number into hex.
	 *
	 * Resulting string will contain even number of chars.
	 *
	 * @param string $dec
	 *
	 * @return string
	 */
	protected function dec2hex($val) {
		$result = '';
		do {
			$mod = bcmod($val, 16);
			$result = dechex($mod).$result;
			$val = bcdiv(bcsub($val, $mod), 16);
		} while ($val !== '0');

		if (strlen($result) % 2 !== 0) {
			$result = '0'.$result;
		}

		return $result;
	}

	/**
	 * Convert big hex number into dec
	 *
	 * Strip optional '0x' prefix
	 *
	 * @param string $val
	 *
	 * @return string
	 */
	protected function hex2dec($val) {
		if (substr($val, 0, 2) == '0x') {
			$val = substr($val, 2);
		}
		$result = '0';
		$index = 0;
		do {
			$hex = substr($val, -2);
			$val = substr($val, 0, -2);

			$byte = hexdec($hex);

			$byte = bcmul($byte, bcpow(256, $index));
			$index++;

			$result = bcadd($result, $byte);
		} while (strlen($val) > 0);

		return $result;
	}
}
