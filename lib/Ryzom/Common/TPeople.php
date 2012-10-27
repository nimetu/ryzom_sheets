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
 * <code/ryzom/common/src/game_share/people.h>
 */
class TPeople {
	const FYROS = 0;
	const MATIS = 1;
	const TRYKER = 2;
	const ZORAI = 3;

	/**
	 * Return race short name
	 *
	 * @param int $race
	 *
	 * @return string
	 */
	public static function toString($race) {
		switch ($race) {
		case self::FYROS:
			return 'FY';
		case self::MATIS:
			return 'MA';
		case self::TRYKER:
			return 'TR';
		case self::ZORAI:
			return 'ZO';
		default:
			return 'TPeople:UNKNOWN';
		}
	}

	/**
	 * Convert string to race constant
	 *
	 * @param string $str
	 *
	 * @return bool|int
	 */
	public static function fromString($str) {
		switch (strtolower($str)) {
		case 'f':
		case 'fy':
		case 'fyros':
			$race = self::FYROS;
			break;
		case 'm':
		case 'ma':
		case 'matis':
			$race = self::MATIS;
			break;
		case 't':
		case 'tr':
		case 'tryker':
			$race = self::TRYKER;
			break;
		case 'z':
		case 'zo':
		case 'zorai':
			$race = self::ZORAI;
			break;
		default:
			return false;
		}
		return $race;
	}
}
