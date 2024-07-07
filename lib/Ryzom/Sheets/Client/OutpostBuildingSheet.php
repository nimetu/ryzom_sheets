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

class OutpostBuildingSheet implements StreamInterface {
	/** @var int */
	public $OBType = 0;

	/** @var int */
	public $CostDapper = 0;

	/** @var int */
	public $CostTime = 0;

	/** @var int */
	public $MPLevelOfHighestExtractRate = 0;

	/** @var int[] */
	public $Mps = array();

	/** @var string[] */
	public $Icon = array();

	public function serial(MemStream $s) {
		$s->serial_uint32($this->OBType);
		$s->serial_uint32($this->CostDapper);
		$s->serial_uint32($this->CostTime);
		$s->serial_uint32($this->MPLevelOfHighestExtractRate);

		$s->serial_uint32($nbItems);
		$s->serial_uint32($this->Mps, $nbItems);

		$s->serial_string($this->Icon, 4);
	}
}
