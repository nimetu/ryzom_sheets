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

	private $skills;

	public function serial(MemStream $s) {

		$this->skills = array();
		$s->serial_uint32($nbSkills);
		for ($nb = 0; $nb < $nbSkills; $nb++) {
			$skill = new CSkill();
			$skill->serial($s);

			$this->skills[$skill->Skill] = $skill;
		}
	}

	/**
	 * @return array
	 */
	public function getSkills() {
		return $this->skills;
	}

	/**
	 * @param $skill
	 *
	 * @return CSkill
	 */
	public function get($skill) {
		if (isset($this->skills[$skill])) {
			return $this->skills[$skill];
		}
	}
}

