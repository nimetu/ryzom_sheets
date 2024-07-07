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
use Ryzom\Sheets\EntityType;

/**
 * Loader of .packed_sheets files
 */
class PackedSheets implements PackedSheetsCollection, StreamInterface {
	const HEADER = 'HSKP';
	const HEADER_VERSION = 5;

	/** @var int */
	protected $version = 0;

	/** @var int */
	protected $compat = 0;

	/** @var array */
	protected $dictionary = array();

	/** @var int[] */
	protected $dependencies = array();

	/** @var bool */
	private $skipDependBlock;

	/** @var array<int, mixed> */
	private $entries = array();

	/** @var string key from $typeVersion */
	private $fileName;

	/** @var int */
	private $fileVersion;

	/** @var array<string,int> */
	private $typeVersion = array(
		'creature' => 17,
		//disabled:'player' => 0,
		//'fx' => 0,
		//'building' => 2,
		'sitem' => 44,
		'item' => 44,
		//'plant' => 5,
		//'death_impact' => 0,
		//disabled:'mission' => 0,
		'race_stats' => 3,
		//'light_cycle' => 0,
		//'weather_setup' => 1,
		//'continent' => 12,
		'world' => 1,
		//'weather_function_params' => 2,
		//'mission_icon' => 0,
		'sbrick' => 33,
		'sphrase' => 4,
		'skill_tree' => 5,
		//'titles' => 1,
		//'success_chances_table' => 1,
		//'automation_list' => 23,
		//'animset_list' => 25,
		//'animation_fx' => 4,
		//'id_to_string_array' => 1,
		//'emot' => 1,
		//'forage_source' => 2,
		//'flora' => 0,
		//'animation_fx_set' => 3,
		//'attacks_list' => 9,
		//'text_emotes' => 1,
		//'sky' => 5,
		'outpost' => 0,
		'outpost_building' => 1,
		'outpost_squad' => 1,
		'faction' => 0,
	);

	/**
	 * @param string $type
	 * @param bool $skipDependBlock
	 */
	public function __construct($type, $skipDependBlock = true) {
		if (!isset($this->typeVersion[$type])) {
			throw new \RuntimeException("Unsupported packed sheet file type ($type)");
		}

		$this->fileName = $type;
		$this->fileVersion = $this->typeVersion[$type];

		$this->skipDependBlock = $skipDependBlock;
	}

	/**
	 * @param MemStream $s
	 *
	 * @return void
	 */
	protected function readHeader(MemStream $s) {
		// check header
		$s->serial_buffer($hskp, 4);
		if ($hskp !== self::HEADER) {
			throw new \RuntimeException('Wrong packed sheets header, expected "'.self::HEADER.'"');
		}
		$s->serial_uint32($header_version);
		if ($header_version != self::HEADER_VERSION) {
			throw new \RuntimeException('Wrong packed sheets version, expected "'.self::HEADER_VERSION.'", got "'.$header_version.'"');
		}
		$s->serial_byte($this->compat);

		$s->serial_uint32($dependBlockSize);
		if ($this->skipDependBlock) {
			// skip dependency block
			$pos = $s->getPos();
			$s->seek($pos + $dependBlockSize);
		} else {
			// read dictionary
			$s->serial_uint32($nb);
			$s->serial_int_string($this->dictionary, $nb);

			// read dependency data
			$this->dependencies = array();
			$s->serial_uint32($depSize);
			for ($i = 0; $i < $depSize; $i++) {
				$s->serial_uint32($sheetId);
				$s->serial_uint32($nb);
				$s->serial_uint32($this->dependencies[$sheetId], $nb);
			}
		}
	}

	/**
	 * @param MemStream $s
	 *
	 * @return void
	 */
	public function serial(MemStream $s) {
		$this->readHeader($s);

		// records in this file (ignore this one)
		$s->serial_uint32($nbEntries);

		// this file version counter
		$s->serial_uint32($version);
		if ($version !== $this->fileVersion) {
			throw new \RuntimeException('Wrong packet sheet file version, expected "'.$this->fileVersion.'", got "'.$version.'"');
		}

		// records in this file
		$s->serial_uint32($nbEntries);
		for ($nbIndex = 0; $nbIndex < $nbEntries; $nbIndex++) {
			$s->serial_uint32($sheetId);
			$s->serial_uint32($entityType);

			$entity = EntityType::factory($entityType);
			$s->serial_uint32($entityId);

			// if $sheetId and entityId dont match, then we have file format error
			if ($sheetId !== $entityId) {
				throw new \RuntimeException("Failed loading {$this->fileName} packed sheets file. sheetId({$sheetId}) and entityId({$entityId}) are different");
			}

			// read rest of buffer
			$entity->serial($s);
			$this->entries[$sheetId] = $entity;

			//printf("%d of %d\r", $nbIndex, $this->nbEntries);
		}
	}

	/**
	 * @return array
	 */
	public function getSheets() {
		return $this->entries;
	}

	/**
	 * @param int $id
	 *
	 * @return mixed|null
	 */
	public function get($id) {
		if (isset($this->entries[$id])) {
			return $this->entries[$id];
		}

		return null;
	}
}
