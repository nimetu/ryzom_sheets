<?php

namespace Ryzom\Sheets;

use Nel\Misc\SheetId;

class SheetsManager {
	/** @var \Nel\Misc\SheetId */
	private $sheetId;

	/** @var PackedSheetsLoader */
	private $loader;

	/** @var PackedSheets[] */
	private $sheets;

	function __construct(SheetId $sheetId, PackedSheetsLoader $loader) {
		$this->sheetId = $sheetId;
		$this->loader = $loader;
		$this->sheets = array();
	}

	function getLoadedSheets() {
		return array_keys($this->sheets);
	}

	/**
	 * @param string $sheet 'name.sheet' or just 'sheet'
	 *
	 * @return PackedSheets
	 */
	function load($sheet) {
		$pos = strrpos($sheet, '.');
		if ($pos !== false) {
			$sheet = substr($sheet, $pos + 1);
		}

		if (!isset($this->sheets[$sheet])) {
			$this->sheets[$sheet] = $this->loader->load($sheet);
		}
		return $this->sheets[$sheet];
	}

	function findById($id) {
		// get sheet name where $id belongs to
		$key = $this->sheetId->getSheetIdName($id);

		// load PackedSheet file
		$ps = $this->load($key);
		if (!empty($ps)) {
			// return sheet record
			return $ps->get($id);
		}
	}
}
