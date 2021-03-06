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
 * Class WorldSheet
 * @package Ryzom\Sheets\Client
 */
class WorldSheet implements StreamInterface {
	/** @var string */
	public $Name;

	/** @var SContLoc[] */
	public $ContLocs;

	/** @var Smap[] */
	public $Maps;

	public function serial(MemStream $s) {
		$s->serial_string($this->Name);

		$this->ContLocs = array();
		$s->serial_uint32($nbItems);
		for ($i = 0; $i < $nbItems; $i++) {
			$cont = new SContLoc();
			$cont->serial($s);

			$this->ContLocs[] = $cont;
		}

		$this->Maps = array();
		$s->serial_uint32($nbItems);
		for ($i = 0; $i < $nbItems; $i++) {
			$map = new SMap();
			$map->serial($s);

			$this->Maps[] = $map;
		}
	}
}

