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

	/**
	 * sheet extensions that gets added to key column
	 *
	 * @var array<string,string[]>
	 */
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
		'race' => array('race ID', ''),
		'damagetype' => array('damageTypeId', ''),
		'ecosystem' => array('ecosysteme ID', ''),
		'score' => array('score_ID', ''),
		'characteristic' => array('characteristic_ID', ''),
	);

	/**
	 * @param string $ext
	 * @param string[] $array
	 *
	 * @return void
	 */
	function addCustomExt($ext, $array) {
		$this->keyExtension[$ext] = $array;
	}

	/**
	 * @return string[]
	 */
	function getSheets() {
		return array_keys($this->keyExtension);
	}

	/**
	 * @param string $sheet
	 * @param string $data
	 *
	 * @return array<string, array<string, string[]>>
	 */
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
			if ($header[$k] !== '') {
				break;
			}
			unset($header[$k]);
		}

		$messages = array();
		for ($i = 1, $len = count($rows); $i < $len; $i++) {
			// split column, may create more columns than header has
			$cols = explode("\t", $rows[$i]);
			if ($cols[0] === '') {
				continue;
			}

			$keyIndex = array_search($this->keyExtension[$sheet][0], $header, true);

			// add missing sheet info to id
			if (isset($this->keyExtension[$sheet][1]) && $this->keyExtension[$sheet][1] !== '') {
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

			// discard these columns from output
			$skipColumns = array(
				'*HASH_VALUE',
				$this->keyExtension[$sheet][0],
				'* noms français',
				'* nom en français',
			);

			// make sure row is not missing '\t' for last empty columns
			$newArray = array();
			foreach($header as $k => $v) {
				// bug in item_words_es.txt
				if ($v === 'descripción') {
					$v = 'description';
				}
				if (isset($header[$k]) && in_array($header[$k], $skipColumns)) {
					continue;
				}
				$newArray[$v] = '';
			}

			//
			$col = '_undef';
			foreach ($cols as $k => $v) {
				if (isset($header[$k]) && in_array($header[$k], $skipColumns)) {
					continue;
				}

				// note: c++ converts line-breaks for 'name' fields too
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
