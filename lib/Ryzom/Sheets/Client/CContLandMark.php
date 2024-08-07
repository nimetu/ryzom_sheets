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

class CContLandMark implements StreamInterface {
	const CAPITAL = 0;
	const VILLAGE = 1;
	const OUTPOST = 2;
	const STABLE = 3;
	const REGION = 4;
	const PLACE = 5;
	const STREET = 6;

	/** @var int */
	public $Type = 0;

	/** @var CVector2f */
	public $Pos;

	/** @var CPrimZone */
	public $Zone;

	/** @var string */
	public $TitleText = '';

	public function __construct() {
		$this->Pos = new CVector2f();
		$this->Zone = new CPrimZone;
	}

	public function serial(MemStream $s) {
		$s->serial_byte($ver);
		$s->serial_uint32($this->Type);
		$this->Pos->serial($s);
		$this->Zone->serial($s);
		$s->serial_string($this->TitleText);
	}
}
