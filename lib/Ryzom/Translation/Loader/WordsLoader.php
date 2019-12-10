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

class WordsLoader extends UnicodeConverter implements LoaderInterface {

	// sheet extensions that gets added to key column
	private $keyExtension = array(
		'skill' => array('skill ID', ''),
		'faction' => array('faction', ''),
		'place' => array('placeId', ''),
		'item' => array('item ID', '.sitem'),
		'creature' => array('creature ID', '.creature'),
		'sbrick' => array('sbrick ID', '.sbrick'),
		'sphrase' => array('sphrase ID', '.sphrase'),
		'title' => array('title_id', ''),
		'outpost' => array('outpost ID', ''),
		//
		'race' => ['race ID', ''],
		'damagetype' => ['damageTypeId', ''],
		'ecosystem' => ['ecosysteme ID', ''],
		'score' => ['score_ID', ''],
		'characteristic' => ['characteristic_ID', ''],
	);

	function addCustomExt($ext, $array) {
		$this->keyExtension[$ext] = $array;
	}

	function getSheets() {
		return array_keys($this->keyExtension);
	}

	function load($sheet, $data) {
		if (!isset($this->keyExtension[$sheet])) {
			throw new \RuntimeException("Unknown translation sheet [$sheet]");
		}

		$data = $this->convert($data);

		$rows = preg_split("/(\r?\n)/", $data);

		// extract first line as header columns
		$header = explode("\t", $rows[0]);
		// remove last empty columns (if any)
		foreach(array_reverse(array_keys($header)) as $k) {
			if (!empty($header[$k])) {
				break;
			}
			unset($header[$k]);
		}

		$messages = array();
		for ($i = 1, $len = count($rows); $i < $len; $i++) {
			// split column, may create more columns than header has
			$cols = explode("\t", $rows[$i]);
			if (empty($cols[0])) {
				continue;
			}

			$keyIndex = array_search($this->keyExtension[$sheet][0], $header, true);

			// add missing sheet info to id
			if (!empty($this->keyExtension[$sheet][1])) {
				if ($keyIndex !== false) {
					$cols[$keyIndex] .= $this->keyExtension[$sheet][1];
				}
			}

			// keep keys lowercase
			$key = strtolower($cols[$keyIndex]);

			// use sheet from $key if it's available
			$pos = strrpos($key, '.');
			if ($pos !== false) {
				$sheetName = substr($key, $pos + 1);
				// and strip sheet from key as we group by sheet anyway
				$key = substr($key, 0, $pos);
			} else {
				$sheetName = $sheet;
			}

			// skip duplicate entries
			if (isset($messages[$sheetName][$key])) {
				continue;
			}

			// replace array index with header keys and convert line-breaks
			// : Ryzom Core converts line-breaks for 'name' fields too,
			// : but lets limit it for description in here
			$newArray = array();

			// discard these columns from output
			$skipColumns = array(
				'*HASH_VALUE',
				$this->keyExtension[$sheet][0],
				'* noms français',
				'* nom en français',
			);

			//
			$col = '_undef';
			foreach ($cols as $k => $v) {
				if (isset($header[$k]) && in_array($header[$k], $skipColumns)) {
					continue;
				}

				if ($k == 'description' || $k == 'description2') {
					$v = str_replace('\n', "\n", $v);
				}

				// only move to next column if header exists and has non-empty value
				// should only be last columns and this will join those with previous one
				if (isset($header[$k]) && !empty($k)) {
					$col = $header[$k];
					// bug in item_words_es.txt
					if ($col === 'descripción') {
						$col = 'description';
						$v = str_replace('\n', "\n", $v);
					}
					$newArray[$col] = trim($v);
				} else {
					// no trim
					$newArray[$col] .= $v;
				}
			}

			$messages[$sheetName][$key] = $newArray;
		}

		return $messages;
	}
}
