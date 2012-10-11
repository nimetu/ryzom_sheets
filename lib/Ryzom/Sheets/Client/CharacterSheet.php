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

class CharacterSheet implements StreamInterface {

	public function serial(MemStream $s) {
		$s->serial_byte($this->Gender);
		$s->serial_uint32($this->Race);
		$s->serial_string($this->SkelFilename);
		$s->serial_string($this->AnimSetBaseName);
		$s->serial_string($this->Automaton);
		$s->serial_float($this->Scale);
		$s->serial_uint32($this->SoundFamily);
		$s->serial_uint32($this->SoundVariation);
		$s->serial_string($this->LodCharacterName);
		$s->serial_float($this->LodCharacterDistance);
		$s->serial_byte($this->Selectable);
		$s->serial_byte($this->Talkable);
		$s->serial_byte($this->Attackable);
		$s->serial_byte($this->Givable);
		$s->serial_byte($this->Mountable);
		$s->serial_byte($this->Turn);
		$s->serial_byte($this->SelectableBySpace);
		$s->serial_uint32($this->HLState);
		$s->serial_float($this->CharacterScalePos);
		$s->serial_float($this->NamePosZLow);
		$s->serial_float($this->NamePosZNormal);
		$s->serial_float($this->NamePosZHigh);
		$s->serial_string($this->Fame);

		// CEquipment::serial()
		$eq = function(&$row) use($s) {
			$row = new \stdClass();
			$s->serial_string($row->Item);
			$s->serial_byte($row->Texture);
			$s->serial_byte($row->Color);
			$s->serial_string($row->BindPoint);
		};
		$eq($this->Body);
		$eq($this->Legs);
		$eq($this->Arms);
		$eq($this->Hands);
		$eq($this->Feet);
		$eq($this->Head);
		$eq($this->Face);
		$eq($this->ObjectInRightHand);
		$eq($this->ObjectInLeftHand);

		$s->serial_byte($this->HairColor);
		$s->serial_byte($this->Skin);
		$s->serial_byte($this->EyesColor);

		$s->serial_float($this->DistToFront);
		$s->serial_float($this->DistToBack);
		$s->serial_float($this->DistToSide);

		$s->serial_float($this->ColRadius);
		$s->serial_float($this->ColHeight);
		$s->serial_float($this->ColLength);
		$s->serial_float($this->ColWidth);
		$s->serial_float($this->MaxSpeed);

		$s->serial_float($this->ClipRadius);
		$s->serial_float($this->ClipHeight);

		$s->serial_uint32($nbItems);
		$s->serial_string($this->AlternativeClothes, $nbItems);

		$this->HairItemList = array();
		$s->serial_uint32($nbItems);
		for ($nb = 0; $nb < $nbItems; $nb++) {
			$eq($this->HairItemList[$nb]);
		}

		$this->GroundFX = array();
		$s->serial_uint32($nbItems);
		for ($nb = 0; $nb < $nbItems; $nb++) {
			$row = new \stdClass();
			$s->serial_uint32($row->GroundID);
			$s->Serial_string($row->FXName);
			$this->GroundFX[] = $row;
		}

		$s->serial_byte($this->DisplayOSD);
		$s->serial_string($this->StaticFX);

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
		$s->serial_string($this->AttackLists, $nbItems);

		// bot object flags
		$s->serial_byte($this->DisplayInRadar);
		$s->serial_byte($this->DisplayOSDName);
		$s->serial_byte($this->DisplayOSDBars);
		$s->serial_byte($this->DisplayOSDForceOver);
		$s->serial_byte($this->Traversable);

		$s->serial_byte($this->RegionForce);
		$s->serial_byte($this->ForceLevel);
		$s->serial_short($this->Level);

		$this->ProjectileCastRay = array();
		$s->serial_uint32($nbItems);
		for ($nb = 0; $nb < $nbItems; $nb++) {
			$row = new \stdClass();
			$s->serial_float($row->Origin, 3);
			$s->serial_float($row->Pos, 3);
			$this->ProjectileCastRay[] = $row;
		}

		$s->serial_byte($this->R2Npc);
	}
}

