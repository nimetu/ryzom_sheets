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

class CConsumable implements StreamInterface {
	/** @var int */
	public $OverdoseTimer = 0;

	/** @var int */
	public $ConsumptionTime = 0;

	/** @var string[] */
	public $Properties = array();

	public function serial(MemStream $s) {
		$s->serial_short($this->OverdoseTimer);
		$s->serial_short($this->ConsumptionTime);

		$s->serial_uint32($nbItems);
		$s->serial_int_string($this->Properties, $nbItems);
	}
}
