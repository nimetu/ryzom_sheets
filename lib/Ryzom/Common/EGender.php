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

namespace Ryzom\Common;

/**
 * <code/ryzom/common/src/game_share/gender.h>
 */
class EGender {
	const MALE = 0;
	const FEMALE = 1;

	/**
	 * @param int $gender
	 *
	 * @return string
	 */
	public static function toString($gender) {
		switch ($gender) {
		case self::MALE:
			return 'M';
		case self::FEMALE:
			return 'F';
		default:
			return 'EGender:UNKNOWN';
		}
	}

	/**
	 * @param string $str
	 *
	 * @return bool|int
	 */
	public static function fromValue($str) {
		switch (strtolower($str)) {
		case 'm':
		case 'hom':
		case 'male'  :
			$gender = self::MALE;
			break;
		case 'f':
		case 'hof':
		case 'female':
			$gender = self::FEMALE;
			break;
		default:
			return false;
		}
		return $gender;
	}
}
