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

/**
 * Factory class to load .packed_sheets and lmconts.packed files
 */
class PackedSheetsLoader {
	/** @var string */
	private $path;

	/**
	 * @param string $path
	 */
	public function __construct($path) {
		$this->path = $path;
	}

	/**
	 * @param string $type
	 *
	 * @throws \RuntimeException
	 *
	 * @return ContinentLandmarks|PackedSheets
	 */
	public function load($type) {
		if ($type == 'lmconts') {
			$fileName = sprintf('%s/lmconts.packed', $this->path);
		} else {
			$fileName = sprintf('%s/%s.packed_sheets', $this->path, $type);
		}
		if (!file_exists($fileName)) {
			throw new \RuntimeException("Requested packed sheet file ($fileName) not found");
		}
		$data = file_get_contents($fileName);

		$stream = new MemStream($data);

		if ($type == 'lmconts') {
			$ps = new ContinentLandmarks();
		} else {
			$ps = new PackedSheets($type);
		}
		$ps->serial($stream);

		return $ps;
	}
}
