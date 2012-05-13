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

use Ryzom\ItemFamily;
use Nel\Misc\MemStream;
use Nel\Misc\StreamInterface;

/**
 * @property string MaleShape
 * @property string FemaleShape
 * @property int SlotBF
 * @property int MapVariant
 * @property int Family
 * @property int ItemType
 * @property string[] Icon
 * @property int[] IconColor
 * @property string IconText
 * @property string AnimSet
 * @property int Color
 * @property bool HasFx
 * @property bool DropOrSell
 * @property bool IsItemNoRent
 * @property bool NeverHideWhenEquiped
 * @property int Stackable
 * @property bool IsConsumable
 * @property float Bulk
 * @property int EquipTime
 * @property CFX FX
 * @property CStaticFX[] StaticFXs
 * @property string[] Effect
 * @property CMpItemPart[] MpItemParts
 * @property int CraftPlan
 * @property int RequiredCharac
 * @property int RequiredCharacLevel
 * @property int RequiredSkill
 * @property int RequiredSkillLevel
 * @property int ItemOrigin
 * @property CArmor Armor
 * @property CMeleeWeapon MeleeWeapon
 * @property CRangeWeapon RangeWeapon
 * @property CAmmo Ammo
 * @property CMp Mp
 * @property CShield Shield
 * @property mixed Tool
 * @property CTeleport Teleport
 * @property CPet Pet
 * @property CGuildOption GuildOption
 * @property CCosmetic Cosmetic
 * @property CConsumable Consumable
 * @property CScroll Scroll
 */
class ItemSheet implements StreamInterface {

	public function serial(MemStream $s) {
		$s->serial_string($this->MaleShape);
		$s->serial_string($this->FemaleShape);
		$s->serial_uint64($this->SlotBF);
		$s->serial_uint32($this->MapVariant);
		$s->serial_uint32($this->Family);
		$s->serial_uint32($this->ItemType);
		$s->serial_string($this->Icon, 4);
		$s->serial_sint32($this->IconColor, 4);
		$s->serial_string($this->IconText);
		$s->serial_string($this->AnimSet);
		$s->serial_byte($this->Color);
		$s->serial_byte($this->HasFx);
		$s->serial_byte($this->DropOrSell);
		$s->serial_byte($this->IsItemNoRent);
		$s->serial_byte($this->NeverHideWhenEquiped);
		$s->serial_uint32($this->Stackable);
		$s->serial_byte($this->IsConsumable);
		$s->serial_float($this->Bulk);
		$s->serial_uint32($this->EquipTime);

		$this->FX = new CFX();
		$this->FX->serial($s);

		$s->serial_uint32($nbItems);
		$this->StaticFXs = array();
		for ($fx = 0; $fx < $nbItems; $fx++) {
			$sfx = new CStaticFX();
			$sfx->serial($s);
			$this->StaticFXs[] = $sfx;
		}

		$s->serial_string($this->Effect, 4);

		$s->serial_uint32($nbItems);
		$this->MpItemParts = array();
		for ($mp = 0; $mp < $nbItems; $mp++) {
			$row = new CMpItemPart();
			$row->serial($s);
			$this->MpItemParts[] = $row;
		}

		$s->serial_uint32($this->CraftPlan);
		$s->serial_uint32($this->RequiredCharac);
		$s->serial_short($this->RequiredCharacLevel);
		$s->serial_uint32($this->RequiredSkill);
		$s->serial_short($this->RequiredSkillLevel);
		$s->serial_uint32($this->ItemOrigin);

		switch ($this->Family) {
		case ItemFamily::ARMOR:
			$this->Armor = new CArmor();
			$this->Armor->serial($s);
			break;
		case ItemFamily::MELEE_WEAPON:
			$this->MeleeWeapon = new CMeleeWeapon();
			$this->MeleeWeapon->serial($s);
			break;
		case ItemFamily::RANGE_WEAPON:
			$this->RangeWeapon = new CRangeWeapon();
			$this->RangeWeapon->serial($s);
			break;
		case ItemFamily::AMMO:
			$this->Ammo = new CAmmo();
			$this->Ammo->serial($s);
			break;
		case ItemFamily::RAW_MATERIAL:
			$this->Mp = new CMp();
			$this->Mp->serial($s);
			break;
		case ItemFamily::SHIELD:
			$this->Shield = new CShield();
			$this->Shield->serial($s);
			break;
		case ItemFamily::CRAFTING_TOOL:
			// fall thru
		case ItemFamily::HARVEST_TOOL:
			// fall thru
		case ItemFamily::TAMING_TOOL:
			$this->Tool = new CTool();
			$this->Tool->serial($s);
			break;
		case ItemFamily::TELEPORT:
			$this->Teleport = new CTeleport();
			$this->Teleport->serial($s);
			break;
		case ItemFamily::PET_ANIMAL_TICKET:
			$this->Pet = new CPet();
			$this->Pet->serial($s);
			break;
		case ItemFamily::GUILD_OPTION:
			$this->GuildOption = new CGuildOption();
			$this->GuildOption->serial($s);
			break;
		case ItemFamily::COSMETIC:
			$this->Cosmetic = new CCosmetic();
			$this->Cosmetic->serial($s);
			break;
		case ItemFamily::CONSUMABLE:
			$this->Consumable = new CConsumable();
			$this->Consumable->serial($s);
			break;
		case ItemFamily::SCROLL:
			$this->Scroll = new CScroll();
			$this->Scroll->serial($s);
			break;
		}
	}
}

