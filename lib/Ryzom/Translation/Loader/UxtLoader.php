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

namespace Ryzom\Translation\Loader;

use Ryzom\Translation\UnicodeConverter;

class UxtLoader extends UnicodeConverter implements LoaderInterface {

	function getSheets() {
		return array('uxt');
	}

	function load($sheet, $data) {
		if ($sheet !== 'uxt') {
			throw new \RuntimeException('This loader only supportx "uxt" file format');
		}

		// added line-break makes it easier regexp rule later
		$data = $this->convert($data)."\n";

		// replace \r\n with \n
		// remove lines starting with //
		// remove lines starting with /* and ending with */ spanning multiple lines
		$data = preg_replace(
			array("/\r\n/", "@^//.*$@m", "@^/\*(.*?)\*/$@ms"),
			array("\n", '', ''),
			$data
		);

		// split lines at ']\n' boundry, make sure closing tag is not escaped
		$lines = preg_split("/(?<=(?<!\\\)])\n\s*?/", $data);

		$messages = array();
		foreach ($lines as $line) {
			$line = trim($line);
			if (empty($line)) {
				continue;
			}
			if (!preg_match('/^([a-z0-9_@]+)\t\[(.*)\]$/is', $line, $match)) {
				continue;
			}
			$label = strtolower($match[1]);

			// skip duplicate entries
			if (isset($messages[$label])) {
				continue;
			}

			// merge multi line strings and replace string '\n' with real line-break
			$string = preg_replace(array("/\n\s+/", '/\\\n/'), array('', "\n"), $match[2]);
			$messages[$label]['name'] = $string;
		}

		return array('uxt' => $messages);
	}
}

