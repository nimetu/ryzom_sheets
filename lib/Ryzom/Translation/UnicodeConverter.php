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

namespace Ryzom\Translation;

class UnicodeConverter {

	/**
	 * Convert input string from UTF8, UTF16 to UTF8
	 *
	 * @param string $data
	 *
	 * @return string
	 */
	function convert($data) {
		if (($data[0] == "\xFE" && $data[1] == "\xFF") || ($data[0] == "\xFF" && $data[1] == "\xFE")) {
			// UTF-16 (LE or BE)
			$data = iconv('UTF-16', 'UTF-8', $data);
		} else if ($data[0] == "\xEF" && $data[1] == "\xBB" && $data[2] == "\xBF") {
			// remove UTF-8 BOM
			$data = substr($data, 3);
		}
		return $data;
	}
}

