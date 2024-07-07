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

namespace Ryzom\Sheets\Client;

use Nel\Misc\MemStream;
use Nel\Misc\StreamInterface;

class SkilltreeSheet implements StreamInterface {
	/** @var array<string,CSkill> */
	private $skills = array();

	/** @var array<int,string> */
	private $indices = array();

	public function serial(MemStream $s) {

		$this->skills = array();
		$s->serial_uint32($nbSkills);
		for ($nb = 0; $nb < $nbSkills; $nb++) {
			$skill = new CSkill();
			$skill->serial($s);

			$code = strtolower($skill->SkillCode);
			$this->skills[$code] = $skill;
			$this->indices[] = $code;
		}
	}

	/**
	 * @return array
	 */
	public function getSkills() {
		return $this->skills;
	}

	/**
	 * @param int $index
	 *
	 * @return CSkill|null
	 *
	 * @deprecated use getSkills() to get full array or find() with skill code
	 */
	public function get($index) {
		if (!isset($this->indices[$index])) {
			return null;
		}

		$code = $this->indices[$index];
		return $this->skills[$code];
	}

	/**
	 * @param string $skill
	 *
	 * @return CSkill|null
	 */
	public function find($skill) {
		if (isset($this->skills[$skill])) {
			return $this->skills[$skill];
		}

		return null;
	}
}
