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

use Ryzom\Translation\Loader\LoaderInterface;

class StringsManager {

	/** @var array */
	private $words;

	/** @var LoaderInterface[] */
	private $loaders;

	function __construct() {
		$this->words = array();

		$this->loaders = array();
	}

	function register(LoaderInterface $loader) {
		$files = $loader->getSheets();
		foreach ($files as $file) {
			$this->loaders[$file] = $loader;
		}
	}

	function load($file, $data, $lang) {
		if (!isset($this->loaders[$file])) {
			throw new \RuntimeException("Loader for file [$file] is not registered");
		}

		// messages has one or more [$sheet => array(messages)] elements
		// outpost_words_<lang>.txt returns 3 sheets for example
		$messages = $this->loaders[$file]->load($file, $data);
		foreach($messages as $group => $array){
			$this->words[$group][$lang] = $array;
		}
	}

	/**
	 * Return strings for loaded language(s)
	 *
	 * @param string $sheet type (uxt, skill, title, outpost, etc)
	 * @param string $lang optional language code (en, fr, etc)
	 *
	 * @return array
	 */
	function getStrings($sheet, $lang = '') {
		if (isset($this->words[$sheet])) {
			if ($lang === '') {
				return $this->words[$sheet];
			}
			if (isset($this->words[$sheet][$lang])) {
				return $this->words[$sheet][$lang];
			}
		}
	}

}
