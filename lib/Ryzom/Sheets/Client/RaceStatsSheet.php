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

class RaceStatsSheet implements StreamInterface {

	public function serial(MemStream $s) {
		// CGenderInfo
		$gi = function(&$var) use($s) {
			$var     = new \stdClass();
			$nbItems = 7;
			$s->serial_string($var->Items, $nbItems);
			$s->serial_string($var->Skelfilename);
			$s->serial_string($var->AnimSetBaseName);
			$s->serial_string($var->LodCharacterName);
			$s->serial_float($var->LodCharacterDistance);
			$s->serial_float($var->CharacterScalePos);
			$var->GroundFX = array();
			$s->serial_uint32($nbItems);
			for ($nb = 0; $nb < $nbItems; $nb++) {
				$row = new \stdClass();
				$s->serial_uint32($row->GroundID);
				$s->Serial_string($row->FXName);
				$var->GroundFX[] = $row;
			}
			for ($nb = 0; $nb < 8; $nb++) {
				$s->serial_float($var->BlendShapeMin[$nb]);
				$s->serial_float($var->BlendShapeMax[$nb]);
			}
			$s->serial_float($var->NamePosZLow);
			$s->serial_float($var->NamePosZNormal);
			$s->serial_float($var->NamePosZHigh);
		};

		$numCharacteristice = 8;
		$s->serial_byte($this->CharacStartValue, 8);
		$gi($this->GenderInfos[0]);
		$gi($this->GenderInfos[1]);
		$s->serial_byte($this->People);
		$s->serial_byte($this->Skin);
		$s->serial_string($this->Automaton);

		$this->BodyToBone = new \stdClass();
		$s->serial_string($this->BodyToBone->Head);
		$s->serial_string($this->BodyToBone->Chest);
		$s->serial_string($this->BodyToBone->LeftArm);
		$s->serial_string($this->BodyToBone->RightArm);
		$s->serial_string($this->BodyToBone->LeftHand);
		$s->serial_string($this->BodyToBone->RightHand);
		$s->serial_string($this->BodyToBone->LeftLeg);
		$s->serial_string($this->BodyToBone->RightLeg);
		$s->serial_string($this->BodyToBone->LeftFoot);
		$s->serial_string($this->BodyToBone->RightFoot);

		$s->serial_uint32($nbItems);
		$s->serial_int_string($this->AttackLists, $nbItems);
	}
}

