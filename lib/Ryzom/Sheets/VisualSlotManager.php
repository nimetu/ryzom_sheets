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

/**
 * visual_slot.tab
 */
class VisualSlotManager implements StreamInterface {

	/** @var array lookup for sheetid using index and slot */
	protected $indexMap = array();

	/** @var array lookup for index using slot and sheetid */
	protected $sheetMap = array();

	/**
	 * Return slot/index/sheetid map
	 *
	 * @return array
	 */
	public function getIndexMap() {
		return $this->indexMap;
	}

	/**
	 * Return Sheet ID that matches visual slot index
	 *
	 * @param int $index
	 * @param int $slot
	 *
	 * @return int|bool
	 */
	public function findByIndex($index, $slot) {
		if (!isset($this->indexMap[$slot][$index])) {
			return false;
		}
		return $this->indexMap[$slot][$index];
	}

	/**
	 * Find index for sheet id in visual slot
	 *
	 * @param int $sheet Sheet ID
	 * @param int $slot  visual slot
	 *
	 * @return int|bool
	 */
	public function findBySheetId($sheet, $slot) {
		if (!isset($this->sheetMap[$slot][$sheet])) {
			return false;
		}
		return $this->sheetMap[$slot][$sheet];
	}

	/**
	 * Parse 'visual_slot.tab' file and build map entries
	 *
	 * @param MemStream $s
	 *
	 * @return void
	 * @throws \RuntimeException
	 */
	public function serial(MemStream $s) {
		$s->serial_uint32($nbSlots);
		for ($slot = 0; $slot < $nbSlots; $slot++) {
			$s->serial_uint32($nbEntries);

			for ($i = 0; $i < $nbEntries; $i++) {
				$s->serial_uint32($index);
				$s->serial_uint32($sheet);

				// sanity check
				if (isset($this->indexMap[$slot][$index])) {
					throw new \RuntimeException("FATAL: duplicate visual slot ($slot) index ($index), sheetid ($sheet)");
				} elseif (isset($this->sheetMap[$slot][$sheet])) {
					throw new \RuntimeException("FATAL: duplicate sheet_id ($sheet), visual slot ($slot), index ($index)");
				}

				// remember both mappings
				$this->indexMap[$slot][$index] = $sheet;
				$this->sheetMap[$slot][$sheet] = $index;
			}
		}
	}

	/**
	 * Load 'visual_slot.tab' from string
	 *
	 * @param string $data
	 *
	 * @return void
	 */
	public function load($data) {
		$this->serial(new MemStream($data));
	}
}
