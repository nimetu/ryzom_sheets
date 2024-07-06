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

namespace Ryzom\Client;

use Nel\Misc\MemStream;

/**
 * Class Icfg
 */
class Icfg {

	const ICFG_STREAM_VERSION = 1;
	const ICFG_HEADER = '_ICU';

	/** @var int */
	public $Version;

	/** @var array */
	public $FreeTeller;

	/** @var int */
	public $CurrentMode;

	/** @var int */
	public $LastInGameScreenW;

	/** @var int */
	public $LastInGameScreenH;

	/** @var IcfgLandmarks */
	public $Landmarks;

	/** @var array */
	public $Taskbar;

	/** @var array */
	public $Macros;

	/** @var array */
	public $SceneBubbleInfo;

	/** @var CInfoWindowSave */
	public $InfoWindowSave;

	/**
	 * Load .icfg file
	 *
	 * @param string $name
	 *
	 * @throws \RuntimeException
	 */
	public function load($name) {
		if (!file_exists($name)) {
			throw new \RuntimeException('file not found');
		}

		$buffer = file_get_contents($name);

		$s = new MemStream($buffer);
		$this->serial($s);
	}

	/**
	 * @param MemStream $s
	 */
	public function serial(MemStream $s) {
		if (!$s->isReading()) {
			throw new \RuntimeException('Writing is not supported');
		}

		$s->serialVersion($this->Version);
		if ($this->Version >= 1) {
			$s->serialCheck(strrev(self::ICFG_HEADER));

			$this->serialUserChatsInfos($s);
		}

		$s->serialCheck('ICFG');

		// number of desktops
		$s->serial_uint32($nbMode);
		// current desktop
		$s->serial_byte($this->CurrentMode);
		if ($this->Version >= 10) {
			$s->serial_sint32($this->LastInGameScreenW);
			$s->serial_sint32($this->LastInGameScreenH);
		}

		// desktop / window configurations
		for ($i = 0; $i < $nbMode; $i++) {
			$s->serial_uint32($len);
			if ($len > 0) {
				$s->serial_buffer($buf, $len);
				if ($this->Version <= 2) {
					$buf = chr(0).$buf;
				}
			}
			// c++: _Modes[i].serial(ms);
		}

		$this->serialDatabase($s);

		// compatibility: load taskbar
		if ($this->Version >= 2) {
			$this->serialTaskbar($s);
		}

		// user landmarks
		$this->Landmarks = new IcfgLandmarks();
		$this->Landmarks->serial($s);

		if ($this->Version >= 5) {
			$this->serialInfoWindowSave($s);
		}

		if ($this->Version >= 7) {
			// CSphraseManager::serialMacroMemory
			$this->serialMacroMemory($s);
		}

		if ($this->Version >= 8) {
			// FIXME: serial in scene bubble info
			// CGroupInSceneBubbleManager::serialInSceneBubbleInfo
			$this->serialInSceneBubbleInfo($s);
		}

		if ($this->Version >= 11) {
			// FIXME: load user dyn chats infos
			// PeopleInterraction.loadUserDynChatsInfos
			$this->serialUserDynChatsInfos($s);
		}
	}

	/**
	 * Read key:value settings like window colors and their state
	 * Data format depends on key
	 *
	 * @param MemStream $s
	 */
	protected function serialDatabase(MemStream $s) {
		if ($this->Version >= 9) {
			$s->serial_uint32($uiDbSaveVersion);
		} else {
			$uiDbSaveVersion = 0;
		}

		$s->serial_uint32($nb);
		for ($i = 0; $i < $nb; $i++) {
			// serial(leafTmp<SDBLeaf>)
			$s->serialVersion($v); // $v==1
			$s->serial_string($name);
			$s->serial_uint64($value);
			if ($v >= 1) {
				$s->serial_uint64($oldvalue);
			}
		}
	}

	/**
	 * deprecated, kept for compatibility
	 *
	 * @param MemStream $s
	 */
	protected function serialTaskbar(MemStream $s) {
		$s->serialVersion($ver); // $ver==0
		$TBM_NUM_BARS = 10;
		$TBM_NUM_SHORTCUT_PER_BAR = 10;

		$this->Taskbar = array();
		for ($row = 0; $row < $TBM_NUM_BARS; $row++) {
			for ($col = 0; $col < $TBM_NUM_SHORTCUT_PER_BAR; $col++) {
				// CTaskBarManager::CShortcutInfo
				$s->serialVersion($v);
				// CCtrlSheetInfo::TSheetType
				$s->serial_uint32($sheetType);
				$s->serial_string($dbsheet);
				$s->serial_uint32($macroId);
				$this->Taskbar[$row][$col] = array(
					'ver' => $v,
					'sheet_type' => $sheetType,
					'db_sheet' => $dbsheet,
					'macro_id' => $macroId,
				);
			}
		}
	}

	/**
	 * @param MemStream $s
	 *
	 * @return bool
	 * @throws \UnexpectedValueException
	 */
	protected function serialUserChatsInfos(MemStream $s) {
		$s->serialVersion($ver);

		$s->serialCheck('CHAT');
		$s->serial_byte($present);
		if (!$present) {
			// invalid data
			return false;
		}

		$this->serialFilteredChatSummary($s);

		$MaxNumUserChats = 5;
		for ($i = 0; $i < $MaxNumUserChats; $i++) {
			$s->serial_byte($present);
			if ($present) {
				$this->serialFilteredChatSummary($s);
			}
		}

		$s->serialCheck('CHAT');
		if ($ver >= 1) {
			$s->serial_sint32($index);
			$s->serial_byte($present);
			if ($present) {
				$this->serialFilteredChatSummary($s);
			}
		}

		if ($ver >= 2) {
			$this->serialFreeTeller($s);
		}
	}

	/**
	 * Dyn chat channel states
	 *
	 * @param MemStream $s
	 */
	protected function serialUserDynChatsInfos(MemStream $s) {
		// FIXME: info is not kept
		$s->serialVersion($ver);
		$s->serialCheck('YGMO');
		if ($ver >= 1) {
			$s->serial_byte($present);

			// CFilteredDynChatSummary
			$s->serialVersion($fdcVersion);
			$s->serialCheck('CHSU');
			if ($fdcVersion >= 0) {
				$maxDynChanPerPlayer = 8;
				for ($i = 0; $i < $maxDynChanPerPlayer; $i++) {
					$s->serial_byte($state);
				}
			}
		}
	}

	/**
	 * Chat channel tab states
	 *
	 * @param MemStream $s
	 */
	protected function serialFilteredChatSummary(MemStream $s) {
		// FIXME: info is not kept
		$s->serialVersion($ver);
		$s->serialCheck('CHSU');

		$s->serial_byte($guild);
		$s->serial_byte($team);
		$s->serial_byte($aroundMe);
		$s->serial_byte($tell);
		$s->serial_byte($systemInfo);
		$s->serial_uint32($target);
		if ($ver >= 1) {
			$s->serial_byte($universe);
		}
		if ($ver >= 2) {
			$s->serial_byte($region);
		}
	}

	/**
	 * Load tell window names
	 *
	 * @param MemStream $s
	 */
	protected function serialFreeTeller(MemStream $s) {
		$this->FreeTeller = array();
		$s->serialVersion($ver);

		$s->serial_uint32($nb);
		for ($i = 0; $i < $nb; $i++) {
			if ($ver == 1) {
				$s->serial_string($old_id);
			}
			$s->serial_ucstring($title);
			$this->FreeTeller[] = $title;
		}
	}

	/**
	 * Read UI window locations
	 *
	 * @param MemStream $s
	 */
	protected function serialInfoWindowSave(MemStream $s) {
		$this->InfoWindowSave = array();

		$s->serialVersion($v);

		$s->serial_uint32($nb);
		for ($i = 0; $i < $nb; $i++) {
			$w = new CInfoWindowSave();
			$w->serial($s);

			$this->InfoWindowSave[] = $w;
		}
	}

	/**
	 * Read macro icons added to taskbar
	 *
	 * @param MemStream $s
	 */
	protected function serialMacroMemory(MemStream $s) {
		$this->Macros = array();
		$s->serial_uint32($nb);
		for ($i = 0; $i < $nb; $i++) {
			$s->serial_byte($line);
			$s->serial_byte($slot);
			$s->serial_uint32($id);

			$this->Macros[] = array(
				'row' => $line,
				'col' => $slot,
				'id' => $id,
			);
		}
	}

	/**
	 * @param MemStream $s
	 */
	protected function serialInSceneBubbleInfo(MemStream $s) {
		$this->SceneBubbleInfo = array();

		$s->serial_uint32($nb);
		for ($i = 0; $i < $nb; $i++) {
			$s->serial_string($string);
			$this->SceneBubbleInfo[] = $string;
		}
	}

}
