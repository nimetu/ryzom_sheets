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
 * SPropVisualA
 * <code/ryzom/common/src/game_share/player_visual_properties.h>
 *
 * @property int Sex
 * @property int JacketModel
 * @property int JacketColor
 * @property int TrouserModel
 * @property int TrouserColor
 * @property int WeaponRightHand
 * @property int WeaponLeftHand
 * @property int ArmModel
 * @property int ArmColor
 * @property int HatModel
 * @property int HatColor
 */
class SPropVisualA extends BitStruct implements StreamInterface {
	/**
	 * SPropVisualA
	 */
	public function __construct() {
		parent::__construct(array(
			'Sex' => 1,
			'JacketModel' => 8,
			'JacketColor' => 3,
			'TrouserModel' => 8,
			'TrouserColor' => 3,
			'WeaponRightHand' => 10,
			'WeaponLeftHand' => 8,
			'ArmModel' => 8,
			'ArmColor' => 3,
			'HatModel' => 9,
			'HatColor' => 3,
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
