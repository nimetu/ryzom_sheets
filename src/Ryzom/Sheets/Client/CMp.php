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

namespace Ryzom\Sheets\Client;

use Nel\Misc\MemStream;
use Nel\Misc\StreamInterface;

/**
 * @property int Ecosystem
 * @property int MpCategory
 * @property int HarvestSkill
 * @property int Family
 * @property int ItemPartBF
 * @property bool UsedAsCraftRequirement
 * @property int MpColor
 * @property int StatEnergy
 */
class CMp implements StreamInterface {

	public function serial(MemStream $s) {
		$s->serial_uint32($this->Ecosystem);
		$s->serial_uint32($this->MpCategory);
		$s->serial_uint32($this->HarvestSkill);
		$s->serial_uint32($this->Family);
		$s->serial_uint64($this->ItemPartBF);
		$s->serial_byte($this->UsedAsCraftRequirement);
		$s->serial_byte($this->MpColor);
		$s->serial_short($this->StatEnergy);
	}

}
