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

namespace Ryzom\Sheets;

use Nel\Misc\MemStream;
use Nel\Misc\StreamInterface;
use Ryzom\Sheets\Client\CContinent;
use Ryzom\Sheets\Client\CContLandMark;

class ContinentLandmarks implements PackedSheetsCollection, StreamInterface {

	/**
	 * @var array {
	 * 	 continents: array<string,CContinent>,
	 *   worldmap: array<string,CContLandMark>,
	 *   aliasmap: array<int,string>
	 * }
	 */
	protected $entries = array(
		'continents' => array(),
		'worldmap' => array(),
		'aliasmap' => array()
	);

	public function serial(MemStream $s) {
		$s->serial_byte($ver);

		/** @var array<string,Client\CContinent> */
		$continents = array();
		$s->serial_uint32($nbItems);
		for ($i = 0; $i < $nbItems; $i++) {
			$cont = new Client\CContinent();
			$cont->serial($s);

			$name = $cont->Name;
			$continents[$name] = $cont;
		}
		$this->entries['continents'] = $continents;

		/** @var array<string,Client\CContLandMark> */
		$worldmap = array();
		$s->serial_uint32($nbItems);
		for ($i = 0; $i < $nbItems; $i++) {
			$map = new Client\CContLandMark();
			$map->serial($s);

			$name = $map->TitleText;
			$worldmap[$name] = $map;
		}
		$this->entries['worldmap'] = $worldmap;

		/** @var array<int,string> */
		$aliasmap = array();
		if ($ver >= 1) {
			$s->serial_uint32($nbItems);
			for ($i = 0; $i < $nbItems; $i++) {
				$s->serial_uint32($id);
				$s->serial_string($key);

				$aliasmap[$id] = $key;
			}
		}
		$this->entries['aliasmap'] = $aliasmap;
	}

	/**
	 * @return array
	 */
	public function getSheets() {
		return $this->entries;
	}

	/**
	 * @param int|string $id [continents, worldmap, aliasmap]
	 *
	 * @return array<string,CContinent>|array<string,CContLandMark>|array<int,string>|null
	 */
	public function get($id) {
		if (isset($this->entries[$id])) {
			return $this->entries[$id];
		}

		return null;
	}
}
