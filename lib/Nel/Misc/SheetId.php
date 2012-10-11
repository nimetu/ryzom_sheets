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

namespace Nel\Misc;

class SheetId implements StreamInterface {
	// Use 24 bits id and 8 bits file types
	const BITS_ID = 24;
	const BITS_TYPE = 8;
	//

	private $sheetIdToNameMap;

	function __construct() {
		$this->sheetIdToNameMap = array();
		$this->nameToSheetIdMap = array();
	}

	/**
	 * @param $data
	 */
	function load($data) {
		$this->serial(new MemStream($data));
	}

	/**
	 * @param MemStream $s
	 */
	public function serial(MemStream $s) {
		$s->serial_uint32($nbItems);

		for ($i = 0; $i < $nbItems; $i++) {
			$s->serial_uint32($sheetId);
			$s->serial_string($sheet);

			// sheet_id.bin has sheet names in mixed case
			$sheet = strtolower($sheet);

			// name -> id lookup
			$this->nameToSheetIdMap[$sheet] = $sheetId;

			// id -> name lookup
			$pos = strrpos($sheet, '.');
			if ($pos !== false) {
				$sheetName = substr($sheet, 0, $pos);
				$sheetFile = substr($sheet, $pos + 1);
			} else {
				// sane fallback
				$sheetName = $sheet;
				$sheetFile = '';
			}

			$this->sheetIdToNameMap[$sheetId] = array(
				'name' => $sheetName,
				'sheet' => $sheetFile,
			);
		}
	}

	/**
	 * @return array
	 */
	public function getSheets() {
		return $this->sheetIdToNameMap;
	}

	/**
	 * @param int $sheetId
	 * @param bool $withSheet
	 *
	 * @return string "name.sheet" or "#id" if $id was not found
	 */
	public function getSheetIdName($sheetId, $withSheet = true) {
		if (isset($this->sheetIdToNameMap[$sheetId])) {
			$result = $this->sheetIdToNameMap[$sheetId]['name'];
			if ($withSheet) {
				$result .= '.'.$this->sheetIdToNameMap[$sheetId]['sheet'];
			}
		} else {
			// if sheet definition was not found, then return numeric string
			$result = '#'.$sheetId;
		}
		return $result;
	}

	/**
	 * Return numeric sheet id from string name
	 *
	 * @param string $sheetName
	 *
	 * @return int
	 */
	public function getSheetId($sheetName) {
		if (isset($this->nameToSheetIdMap[$sheetName])) {
			return $this->nameToSheetIdMap[$sheetName];
		}
	}
}
