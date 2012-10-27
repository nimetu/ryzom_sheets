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

use Ryzom\Misc\BitStruct;
use Nel\Misc\MemStream;
use Nel\Misc\StreamInterface;

/**
 * SPropVisualB
 * <code/ryzom/common/src/game_share/player_visual_properties.h>
 *
 * @property int Name
 * @property int HandsModel
 * @property int HandsColor
 * @property int FeetModel
 * @property int FeetColor
 * @property int RTrail
 * @property int LTrail
 */
class SPropVisualB extends BitStruct implements StreamInterface {
	/**
	 * SPropVisualB
	 */
	public function __construct() {
		parent::__construct(array(
			'Name' => 16,
			'HandsModel' => 9,
			'HandsColor' => 3,
			'FeetModel' => 9,
			'FeetColor' => 3,
			'RTrail' => 4,
			'LTrail' => 3,
		));
	}

	/**
	 * @param MemStream $s
	 */
	public function serial(MemStream $s) {
		if ($s->isReading()) {
			$s->serial_uint64($val);
			$this->setValue($val);
		} else {
			$val = $this->getValue();
			$s->serial_uint64($val);
		}
	}
}
