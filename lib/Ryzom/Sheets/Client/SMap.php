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

class SMap implements StreamInterface {
	/** @var string */
	public $Name;

	/** @var string */
	public $ContinentName;

	/** @var string */
	public $BitmapName;

	/** @var float */
	public $MinX;

	/** @var float */
	public $MinY;

	/** @var float */
	public $MaxX;

	/** @var float */
	public $MaxY;

	/** @var SMapChild[] */
	public $Children;

	public function serial(MemStream $s) {
		$s->serial_string($this->Name);
		$s->serial_string($this->ContinentName);
		$s->serial_string($this->BitmapName);
		$s->serial_float($this->MinX);
		$s->serial_float($this->MinY);
		$s->serial_float($this->MaxX);
		$s->serial_float($this->MaxY);

		$this->Children = array();
		$s->serial_uint32($nbChilds);
		for ($j = 0; $j < $nbChilds; $j++) {
			$child = new SMapChild();
			$child->serial($s);

			$this->Children[] = $child;
		}
	}
}
