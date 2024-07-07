<?php

namespace Ryzom\Sheets;

use Nel\Misc\SheetId;

class SheetsManager {
	/** @var \Nel\Misc\SheetId */
	private $sheetId;

	/** @var PackedSheetsLoader */
	private $loader;

	/** @var PackedSheetsCollection[] */
	private $sheets;

	public function __construct(SheetId $sheetId, PackedSheetsLoader $loader) {
		$this->sheetId = $sheetId;
		$this->loader = $loader;
		$this->sheets = array();
	}

	/**
	 * @return string[]
	 */
	public function getLoadedSheets() {
		return array_keys($this->sheets);
	}

	/**
	 * @param string $sheet 'name.sheet' or just 'sheet'
	 *
	 * @return PackedSheetsCollection
	 */
	public function load($sheet) {
		$pos = strrpos($sheet, '.');
		if ($pos !== false) {
			$sheet = substr($sheet, $pos + 1);
		}

		if (!isset($this->sheets[$sheet])) {
			$this->sheets[$sheet] = $this->loader->load($sheet);
		}
		return $this->sheets[$sheet];
	}

	/**
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function findById($id) {
		// get sheet name where $id belongs to
		$key = $this->sheetId->getSheetIdName($id);

		// load PackedSheet file
		$ps = $this->load($key);

		// return sheet record
		return $ps->get($id);
	}
}
